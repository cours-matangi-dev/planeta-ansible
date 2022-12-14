<?php
/* Copyright (C) 2007  STNA/7SQ (IVDS)
 *
 * This file is part of ASTRES.
 *
 * ASTRES is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * ASTRES is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ASTRES; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


/**
 * Common module : library of database functions used for the LogEvents and Stats tables
 *
 * @author STNA/7SQ, Christophe Javouhey
 * @version 3.7
 * @since 2010-02-11
 */


/**
 * Add a event in the LogEvents table if the last logged event isn't the same
 *
 * @author STNA/7SQ
 * @version 1.0
 * @since 2010-02-11
 *
 * @param $DbConnection          DB object    Object of the opened database connection
 * @param $Date                  Datetime     Date of the event to log (YYYY-mm-dd HH:mm:ss)
 * @param $ItemID                Integer      ID of the object concerned by the event (ask of work, document...) [0..n]
 * @param $ItemType              String       Type of the event to log
 * @param $Service               String       Name of the service of the event to log
 * @param $Action                String       Name of the action of the event to log
 * @param $Level                 Integer      Level of the event to log
 * @param $SupportMemberID       Integer      ID of the supporter who has done the action [0..n]
 * @param $Title                 String       Title of teh event (for RSS)
 * @param $Description           String       Description of the event (for RSS)
 * @param $LinkedObjectID        Integer      ID of the object linked to the item ID [1..n]
 *
 * @return Integer               The primary key of the logged event, 0 otherwise
 */
 function dbLogEvent($DbConnection, $Date, $ItemID, $ItemType, $Service, $Action, $Level, $SupportMemberID, $Title = '', $Description = '', $LinkedObjectID = NULL)
 {
     // ItemID = 0 : no concerned object
     // SupportMemberID = 0 : the application is the "author" of the action
     if (($ItemID >= 0) && (!empty($ItemType)) && (!empty($Service)) && (!empty($Action)) && ($Level > 0) && ($SupportMemberID >= 0))
     {
         // Check if the Date is valide
         if (preg_match("[\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d]", $Date) == 0)
         {
             // ERROR
             return 0;
         }

         // Check if the linked object ID is valide
         if (is_null($LinkedObjectID))
         {
             $LinkedObjectID = "LogEventLinkedObjectID = NULL";
         }
         elseif ($LinkedObjectID > 0)
         {
             $LinkedObjectID = "LogEventLinkedObjectID = $LinkedObjectID";
         }
         else
         {
             // ERROR
             return 0;
         }

         // The event is a new event? We get the last logged event
         $DbResult = $DbConnection->query("SELECT LogEventID, LogEventItemID, LogEventItemType, LogEventService, LogEventAction,
                                          SupportMemberID FROM LogEvents ORDER BY LogEventID DESC LIMIT 0, 1");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 0)
             {
                 // No previous logged event : we can log this new event
                 $bCanLog = TRUE;
             }
             else
             {
                 // We compare the previous logged event with the new event
                 $Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC);
                 if (
                     ($Record['LogEventItemID'] == $ItemID) && ($Record['LogEventItemType'] == $ItemType)
                     && ($Record['LogEventService'] == $Service) && ($Record['LogEventAction'] == $Action)
                     && ($Record['SupportMemberID'] == $SupportMemberID)
                    )
                 {
                     // The previous logged event is the same as the new event : we don't log it, just update the date
                     $bCanLog = FALSE;
                 }
                 else
                 {
                     // The previous logged event isn't the same as the new event : we can log it
                     $bCanLog = TRUE;
                 }
             }

             if ($bCanLog)
             {
                 // New event : it can be logged
                 // We check the limit of the number of events to log and delete the too old events
                 if ($GLOBALS['CONF_LOG_EVENTS_LIMIT'] > 0)
                 {
                     // log rotate : get the table size
                     $id = 0;
                     $DbResult = $DbConnection->getOne("SELECT COUNT(LogEventID) FROM LogEvents");
                     if (!DB::isError($DbResult))
                     {
                         $iNbLogEvents = $DbResult;
                         if ($iNbLogEvents < $GLOBALS['CONF_LOG_EVENTS_LIMIT'])
                         {
                             $id = getNewPrimaryKey($DbConnection, "LogEvents", "LogEventID");
                         }
                         else
                         {
                             // We must delete some events
                             $iNbEventsToDelete = 1 + $iNbLogEvents - $GLOBALS['CONF_LOG_EVENTS_LIMIT'];
                             $DbResult = $DbConnection->query("DELETE FROM LogEvents ORDER BY LogEventDate LIMIT $iNbEventsToDelete");
                             if (!DB::isError($DbResult))
                             {
                                 $DbResult = $DbConnection->getOne("SELECT LogEventID FROM LogEvents ORDER BY LogEventDate DESC LIMIT 1");
                                 if (!DB::isError($DbResult))
                                 {
                                     // Auto-incrementation
                                     $id = $DbResult + 1;
                                     if ($id > $GLOBALS['CONF_LOG_EVENTS_LIMIT'])
                                     {
                                         $id = 1;
                                     }
                                 }
                             }
                         }
                     }
                 }
                 else
                 {
                     // No log rotate
                     $id = getNewPrimaryKey($DbConnection, "LogEvents", "LogEventID");
                 }

                 if ($id != 0)
                 {
                     $DbResult = $DbConnection->query("INSERT INTO LogEvents SET LogEventID = $id, LogEventItemID = $ItemID,
                                                      LogEventItemType = \"$ItemType\", LogEventDate = \"$Date\",
                                                      LogEventService = \"$Service\", LogEventAction = \"$Action\",
                                                      LogEventLevel = $Level, SupportMemberID = $SupportMemberID,
                                                      LogEventTitle = \"$Title\", LogEventDescription = \"$Description\",
                                                      $LinkedObjectID");
                     if (!DB::isError($DbResult))
                     {
                         return $id;
                     }
                 }
             }
             else
             {
                 // Previous logged event : we update the date and return its ID
                 $DbResult = $DbConnection->query("UPDATE LogEvents SET LogEventDate = \"$Date\" WHERE LogEventID = ".$Record["LogEventID"]);
                 return $Record["LogEventID"];
             }
         }
     }

     // ERROR
     return 0;
 }


/**
 * Give the whole fields values of logged event, thanks to his ID
 *
 * @author STNA/7SQ
 * @version 1.0
 * @since 2010-02-11
 *
 * @param $DbConnection         DB object    Object of the opened database connection
 * @param $LogEventID           Integer      ID of the logged event searched [1..n]
 *
 * @return Mixed array          All fields values of a logged event if it exists,
 *                              an empty array otherwise
 */
 function getLogEventInfos($DbConnection, $LogEventID)
 {
     $DbResult = $DbConnection->query("SELECT LogEventID, LogEventItemID, LogEventItemType, LogEventDate, LogEventService,
                                      LogEventAction, LogEventLevel, LogEventTitle, LogEventDescription, LogEventLinkedObjectID,
                                      SupportMemberID FROM LogEvents WHERE LogEventID = $LogEventID");
     if (!DB::isError($DbResult))
     {
         if ($DbResult->numRows() != 0)
         {
             return $DbResult->fetchRow(DB_FETCHMODE_ASSOC);
         }
     }

     // ERROR
     return array();
 }


/**
 * Get logged events filtered by some criterion
 *
 * @author STNA/7SQ, Christophe Javouhey
 * @version 1.2
 *    - 2021-02-10 : v1.1. Replace DISTINCT by GROUP BY in the SQL query and taken into account
 *                   LogEventLevel and LogEventLinkedObjectID fields as filter criterion
 *    - 2021-12-27 : v1.2. Use DATE_FORMAT() on LogEventDate field
 *
 * @since 2010-02-17
 *
 * @param $DbConnection             DB object              Object of the opened database connection
 * @param $ArrayParams              Mixed array            Contains the criterion used to filter the logged events
 * @param $OrderBy                  String                 Criteria used to sort the logged events.
 * @param $Page                     Integer                Number of the page to return [1..n]
 * @param $LogEventsPerPage         Integer                Number of logged events per page to return [1..n]
 *
 * @return Array of String          List of logged events filtered, an empty array otherwise
 */
 function dbSearchLogEvent($DbConnection, $ArrayParams, $OrderBy = "", $Page = 1, $LogEventsPerPage = 10)
 {
     // SQL request to find logged events
     $Select = "SELECT le.LogEventID, le.LogEventDate, le.LogEventItemID, le.LogEventItemType, le.LogEventService, le.LogEventAction, le.LogEventLevel,
                le.LogEventTitle, le.LogEventDescription, le.LogEventLinkedObjectID, sm.SupportMemberID, sm.SupportMemberLastname, sm.SupportMemberFirstname";
     $From = "FROM LogEvents le LEFT JOIN SupportMembers sm ON (le.SupportMemberID = sm.SupportMemberID)";
     $Where = " WHERE 1=1";
     $Having = "";

     if (count($ArrayParams) >= 0)
     {
         // <<< Reference field >>>
         if ((array_key_exists("LogEventID", $ArrayParams)) && ($ArrayParams["LogEventID"] != ""))
         {
             $Where .= " AND le.LogEventID = ".$ArrayParams["LogEventID"];
         }

         // <<< LogEventItemID >>>
         if ((array_key_exists("LogEventItemID", $ArrayParams)) && (count($ArrayParams["LogEventItemID"]) > 0))
         {
             $Where .= " AND le.LogEventItemID IN ".constructSQLINString($ArrayParams["LogEventItemID"]);
         }

         // <<< LogEventLinkedObjectID >>>
         if ((array_key_exists("LogEventLinkedObjectID", $ArrayParams)) && (count($ArrayParams["LogEventLinkedObjectID"]) > 0))
         {
             $Where .= " AND le.LogEventLinkedObjectID IN ".constructSQLINString($ArrayParams["LogEventLinkedObjectID"]);
         }

         // <<< LogEventItemType field >>>
         if ((array_key_exists("LogEventItemType", $ArrayParams)) && (count($ArrayParams["LogEventItemType"]) > 0))
         {
             $Where .= " AND le.LogEventItemType IN ".constructSQLINString($ArrayParams["LogEventItemType"]);
         }

         // <<< LogEventService field >>>
         if ((array_key_exists("LogEventService", $ArrayParams)) && (count($ArrayParams["LogEventService"]) > 0))
         {
             $Where .= " AND le.LogEventService IN ".constructSQLINString($ArrayParams["LogEventService"]);
         }

         // <<< LogEventAction field >>>
         if ((array_key_exists("LogEventAction", $ArrayParams)) && (count($ArrayParams["LogEventAction"]) > 0))
         {
             $Where .= " AND le.LogEventAction IN ".constructSQLINString($ArrayParams["LogEventAction"]);
         }

         // <<< LogEventLevel >>>
         if ((array_key_exists("LogEventLevel", $ArrayParams)) && (count($ArrayParams["LogEventLevel"]) > 0))
         {
             $Where .= " AND le.LogEventLevel IN ".constructSQLINString($ArrayParams["LogEventLevel"]);
         }

         // <<< SupportMemberID >>>
         if ((array_key_exists("SupportMemberID", $ArrayParams)) && (count($ArrayParams["SupportMemberID"]) > 0))
         {
             $Where .= " AND sm.SupportMemberID IN ".constructSQLINString($ArrayParams["SupportMemberID"]);
         }

         // <<< SupportMemberName field >>>
         if ((array_key_exists("SupportMemberName", $ArrayParams)) && ($ArrayParams["SupportMemberName"] != ""))
         {
             $Where .= " AND (sm.SupportMemberLastname LIKE \"".$ArrayParams["SupportMemberName"]."\" OR sm.SupportMemberFirstname LIKE \"".$ArrayParams["SupportMemberName"]."\")";
         }

         // <<< Title fields >>>
         if ((array_key_exists("LogEventTitle", $ArrayParams)) && ($ArrayParams["LogEventTitle"] != ""))
         {
             $Where .= " AND le.LogEventTitle LIKE \"".$ArrayParams["LogEventTitle"]."\"";
         }

         // <<< Description field >>>
         if ((array_key_exists("LogEventDescription", $ArrayParams)) && ($ArrayParams["LogEventDescription"] != ""))
         {
             $Where .= " AND le.LogEventDescription LIKE \"".$ArrayParams["LogEventDescription"]."\"";
         }

         // <<< LogEventDate field >>> [0] -> operator [1] -> start date [2] -> operator [3] -> end date
         if ((array_key_exists("LogEventDate", $ArrayParams)) && (count($ArrayParams["LogEventDate"]) >= 2))
         {
             $Where .= " AND DATE_FORMAT(le.LogEventDate, '%Y-%m-%d') ".$ArrayParams["LogEventDate"][0]." \"".formatedDate2EngDate($ArrayParams["LogEventDate"][1])."\"";

             if (count($ArrayParams["LogEventDate"]) == 4)
             {
                 // There is an end date
                 $Where .= " AND DATE_FORMAT(le.LogEventDate, '%Y-%m-%d') ".$ArrayParams["LogEventDate"][2]." \"".formatedDate2EngDate($ArrayParams["LogEventDate"][3])."\"";
             }
         }
     }

     // We take into account the page and the number of logged events per page
     if ($Page < 1)
     {
         $Page = 1;
     }

     if ($LogEventsPerPage < 0)
     {
         $LogEventsPerPage = 10;
     }

     $Limit = '';
     if ($LogEventsPerPage > 0)
     {
         $StartIndex = ($Page - 1) * $LogEventsPerPage;
         $Limit = "LIMIT $StartIndex, $LogEventsPerPage";
     }

     // We take into account the order by
     if ($OrderBy == "")
     {
         $StrOrderBy = "";
     }
     else
     {
         $StrOrderBy = " ORDER BY $OrderBy";
     }

     // We can launch the SQL request
     $DbResult = $DbConnection->query("$Select $From $Where GROUP BY le.LogEventID $Having $StrOrderBy $Limit");
     if (!DB::isError($DbResult))
     {
         if ($DbResult->numRows() != 0)
         {
             // Creation of the result array
             $ArrayLogEvents = array(
                                    "LogEventID" => array(),
                                    "LogEventDate" => array(),
                                    "LogEventItemID" => array(),
                                    "LogEventItemType" => array(),
                                    "LogEventService" => array(),
                                    "LogEventAction" => array(),
                                    "LogEventLevel" => array(),
                                    "LogEventTitle" => array(),
                                    "LogEventDescription" => array(),
                                    "LogEventLinkedObjectID" => array(),
                                    "SupportMemberID" => array(),
                                    "Supporter" => array()
                                   );

             while($Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC))
             {
                 $ArrayLogEvents["LogEventID"][] = $Record["LogEventID"];
                 $ArrayLogEvents["LogEventDate"][] = $Record["LogEventDate"];
                 $ArrayLogEvents["LogEventItemID"][] = $Record["LogEventItemID"];
                 $ArrayLogEvents["LogEventItemType"][] = $Record["LogEventItemType"];
                 $ArrayLogEvents["LogEventService"][] = $Record["LogEventService"];
                 $ArrayLogEvents["LogEventAction"][] = $Record["LogEventAction"];
                 $ArrayLogEvents["LogEventLevel"][] = $Record["LogEventLevel"];
                 $ArrayLogEvents["LogEventTitle"][] = $Record["LogEventTitle"];
                 $ArrayLogEvents["LogEventDescription"][] = $Record["LogEventDescription"];
                 $ArrayLogEvents["LogEventLinkedObjectID"][] = $Record["LogEventLinkedObjectID"];
                 $ArrayLogEvents["SupportMemberID"][] = $Record["SupportMemberID"];
                 $ArrayLogEvents["Supporter"][] = $Record["SupportMemberLastname"]." ".$Record["SupportMemberFirstname"];
             }

             // Return result
             return $ArrayLogEvents;
         }
     }

     // ERROR
     return array();
 }


/**
 * Get the number of logged events filtered by some criterion
 *
 * @author STNA/7SQ, Christophe Javouhey
 * @version 1.1
 *    - 2021-02-10 : v1.1. Replace DISTINCT by GROUP BY in the SQL query and taken into account
 *                   LogEventLevel and LogEventLinkedObjectID fields as filter criterion
 *
 * @since 2010-02-17
 *
 * @param $DbConnection         DB object              Object of the opened database connection
 * @param $ArrayParams          Mixed array            Contains the criterion used to filter the logged events
 *
 * @return Integer              Number of the logged events found, 0 otherwise
 */
 function getNbdbSearchLogEvent($DbConnection, $ArrayParams)
 {
     // SQL request to find logged events
     $Select = "SELECT le.LogEventID";
     $From = "FROM LogEvents le LEFT JOIN SupportMembers sm ON le.SupportMemberID = sm.SupportMemberID";
     $Where = " WHERE 1=1";
     $Having = "";

     if (count($ArrayParams) >= 0)
     {
         // <<< Reference field >>>
         if ((array_key_exists("LogEventID", $ArrayParams)) && ($ArrayParams["LogEventID"] != ""))
         {
             $Where .= " AND le.LogEventID = ".$ArrayParams["LogEventID"];
         }

         // <<< LogEventItemID >>>
         if ((array_key_exists("LogEventItemID", $ArrayParams)) && (count($ArrayParams["LogEventItemID"]) > 0))
         {
             $Where .= " AND le.LogEventItemID IN ".constructSQLINString($ArrayParams["LogEventItemID"]);
         }

         // <<< LogEventLinkedObjectID >>>
         if ((array_key_exists("LogEventLinkedObjectID", $ArrayParams)) && (count($ArrayParams["LogEventLinkedObjectID"]) > 0))
         {
             $Where .= " AND le.LogEventLinkedObjectID IN ".constructSQLINString($ArrayParams["LogEventLinkedObjectID"]);
         }

         // <<< LogEventItemType field >>>
         if ((array_key_exists("LogEventItemType", $ArrayParams)) && (count($ArrayParams["LogEventItemType"]) > 0))
         {
             $Where .= " AND le.LogEventItemType IN ".constructSQLINString($ArrayParams["LogEventItemType"]);
         }

         // <<< LogEventService field >>>
         if ((array_key_exists("LogEventService", $ArrayParams)) && (count($ArrayParams["LogEventService"]) > 0))
         {
             $Where .= " AND le.LogEventService IN ".constructSQLINString($ArrayParams["LogEventService"]);
         }

         // <<< LogEventAction field >>>
         if ((array_key_exists("LogEventAction", $ArrayParams)) && (count($ArrayParams["LogEventAction"]) > 0))
         {
             $Where .= " AND le.LogEventAction IN ".constructSQLINString($ArrayParams["LogEventAction"]);
         }

         // <<< LogEventLevel >>>
         if ((array_key_exists("LogEventLevel", $ArrayParams)) && (count($ArrayParams["LogEventLevel"]) > 0))
         {
             $Where .= " AND le.LogEventLevel IN ".constructSQLINString($ArrayParams["LogEventLevel"]);
         }

         // <<< SupportMemberID >>>
         if ((array_key_exists("SupportMemberID", $ArrayParams)) && (count($ArrayParams["SupportMemberID"]) > 0))
         {
             $Where .= " AND sm.SupportMemberID IN ".constructSQLINString($ArrayParams["SupportMemberID"]);
         }

         // <<< SupportMemberName field >>>
         if ((array_key_exists("SupportMemberName", $ArrayParams)) && ($ArrayParams["SupportMemberName"] != ""))
         {
             $Where .= " AND (sm.SupportMemberLastname LIKE \"".$ArrayParams["SupportMemberName"]."\" OR sm.SupportMemberFirstname LIKE \"".$ArrayParams["SupportMemberName"]."\")";
         }

         // <<< Title fields >>>
         if ((array_key_exists("LogEventTitle", $ArrayParams)) && ($ArrayParams["LogEventTitle"] != ""))
         {
             $Where .= " AND le.LogEventTitle LIKE \"".$ArrayParams["LogEventTitle"]."\"";
         }

         // <<< Description field >>>
         if ((array_key_exists("LogEventDescription", $ArrayParams)) && ($ArrayParams["LogEventDescription"] != ""))
         {
             $Where .= " AND le.LogEventDescription LIKE \"".$ArrayParams["LogEventDescription"]."\"";
         }

         // <<< LogEventDate field >>> [0] -> operator [1] -> start date [2] -> operator [3] -> end date
         if ((array_key_exists("LogEventDate", $ArrayParams)) && (count($ArrayParams["LogEventDate"]) >= 2))
         {
             $Where .= " AND le.LogEventDate ".$ArrayParams["LogEventDate"][0]." \"".formatedDate2EngDate($ArrayParams["LogEventDate"][1])."\"";

             if (count($ArrayParams["LogEventDate"]) == 4)
             {
                 // There is an end date
                 $Where .= " AND le.LogEventDate ".$ArrayParams["LogEventDate"][2]." \"".formatedDate2EngDate($ArrayParams["LogEventDate"][3])."\"";
             }
         }
     }

     // We can launch the SQL request
     $DbResult = $DbConnection->query("$Select $From $Where GROUP BY le.LogEventID $Having");
     if (!DB::isError($DbResult))
     {
         return $DbResult->numRows();
     }

     // ERROR
     return 0;
 }


 /**
 * Add a stat in the Stats table
 *
 * @author Christophe Javouhey
 * @version 1.1
 *     - 2021-05-05 : taken into account stats about canteen registrations, nursery registrations,
 *                    more meals, event registrations and approvals of documents by families
 *
 * @since 2019-11-18
 *
 * @param $DbConnection          DB object    Object of the opened database connection
 * @param $Date                  Datetime     Date and hour of the concerned stat (YYYY-mm-dd HH:mm:ss)
 *                                            or date (YYYY-mm-dd), or year-month ((YYYY-mm)
 * @param $Type                  String       Type of the concerned stat
 * @param $Value                 Float        Value to add to the concerned stat
 * @param $SubType               String       Sub-type of the concerned stat
 *
 * @return Integer               The primary key of the stat, 0 otherwise
 */
 function dbUpdateStat($DbConnection, $Date, $Type, $Value, $SubType = NULL)
 {
     if (!empty($Type))
     {
         // Check if the Date is valide
         if ((preg_match("[\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d]", $Date) == 0)
             && (preg_match("[\d\d\d\d-\d\d-\d\d]", $Date) == 0)
             && (preg_match("[\d\d\d\d-\d\d]", $Date) == 0))
         {
             // ERROR
             return 0;
         }

         // Define the period
         switch(strToUpper($Type))
         {
             case STAT_TYPE_NB_CANTEEN_REGISTRATIONS:
             case STAT_TYPE_NB_DOC_APPROVALS:
             case STAT_TYPE_NB_MORE_MEALS:
             case STAT_TYPE_NB_NURSERY_REGISTRATIONS:
                 if (preg_match("[\d\d\d\d-\d\d-\d\d]", $Date) == 0)
                 {
                     // The date is already a year-month
                     $Period = $Date;
                 }
                 else
                 {
                     $Period = date('Y-m', strtotime($Date));
                 }
                 break;

             case STAT_TYPE_NB_EVENT_REGISTRATIONS:
                $Period = date('Y-m-d', strtotime($Date));
                break;

             case STAT_TYPE_NB_EMAILS_SENT:
             case STAT_TYPE_NB_EMAILS_ERRORS:
             default:
                 $Period = date('Y-m', strtotime($Date));
                 break;
         }

         $sSQLCondition = '';
         if (!empty($SubType))
         {
             $sSQLCondition = " AND StatSubType = \"$SubType\"";
         }

         // Check if an entry already exists for the concerned Type and Subtype
         $DbResult = $DbConnection->query("SELECT StatID, StatValue
                                           FROM Stats
                                           WHERE StatPeriod = \"$Period\" AND StatType = \"$Type\" $sSQLCondition
                                           ORDER BY StatID DESC LIMIT 0, 1");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 0)
             {
                 $id = getNewPrimaryKey($DbConnection, "Stats", "StatID");

                 if (empty($SubType))
                 {
                     $SubType = "$SubType = NULL";
                 }
                 else
                 {
                     $SubType = "StatSubType = \"$SubType\"";
                 }

                 $DbResult = $DbConnection->query("INSERT INTO Stats SET StatID = $id, StatPeriod = \"$Period\", StatType = \"$Type\", $SubType, StatValue = $Value");
             }
             else
             {
                 $Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC);
                 $id = $Record['StatID'];
                 $Value += $Record['StatValue'];

                 $DbResult = $DbConnection->query("UPDATE Stats SET StatValue = $Value WHERE StatID = $id");
             }

             if ($id != 0)
             {
                 if (!DB::isError($DbResult))
                 {
                     return $id;
                 }
             }
         }
     }

     // ERROR
     return 0;
 }


/**
 * Get stats filtered by some criterion
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-05-07
 *
 * @param $DbConnection             DB object              Object of the opened database connection
 * @param $ArrayParams              Mixed array            Contains the criterion used to filter the stats
 * @param $OrderBy                  String                 Criteria used to sort the stats
 * @param $Page                     Integer                Number of the page to return [1..n]
 * @param $StatsPerPage             Integer                Number of stats per page to return [1..n]
 *
 * @return Array of String          List of stats filtered, an empty array otherwise
 */
 function dbSearchStat($DbConnection, $ArrayParams, $OrderBy = "", $Page = 1, $StatsPerPage = 10)
 {
     // SQL request to find stats
     $Select = "SELECT StatID, StatPeriod, StatType, StatSubType, StatValue";
     $From = "FROM Stats";
     $Where = " WHERE 1=1";
     $Having = "";

     if (count($ArrayParams) >= 0)
     {
         // <<< StatID field >>>
         if ((array_key_exists("StatID", $ArrayParams)) && (!empty($ArrayParams["StatID"])))
         {
             if (is_array($ArrayParams["StatID"]))
             {
                 $Where .= " AND StatID IN ".constructSQLINString($ArrayParams["SupportMemberID"]);
             }
             else
             {
                 $Where .= " AND StatID = ".$ArrayParams["StatID"];
             }
         }

         // <<< StatPeriod >>>
         if ((array_key_exists("StatPeriod", $ArrayParams)) && ($ArrayParams["StatPeriod"] != ""))
         {
             $Where .= " AND StatPeriod LIKE \"".$ArrayParams["StatPeriod"]."\"";
         }

         // <<< StatType fields >>>
         if ((array_key_exists("StatType", $ArrayParams)) && ($ArrayParams["StatType"] != ""))
         {
             $Where .= " AND StatType LIKE \"".$ArrayParams["StatType"]."\"";
         }

         // <<< StatSubType field >>>
         if ((array_key_exists("StatSubType", $ArrayParams)) && ($ArrayParams["StatSubType"] != ""))
         {
             $Where .= " AND StatSubType LIKE \"".$ArrayParams["StatSubType"]."\"";
         }

         // <<< StatValue field >>> [0] -> operator [1] -> value
         if ((array_key_exists("StatValue", $ArrayParams)) && (count($ArrayParams["StatValue"]) == 2))
         {
             $Where .= " AND StatValue ".$ArrayParams["StatValue"][0]." ".$ArrayParams["StatValue"][1];
         }
     }

     // We take into account the page and the number of lstats per page
     if ($Page < 1)
     {
         $Page = 1;
     }

     if ($StatsPerPage < 0)
     {
         $StatsPerPage = 10;
     }

     $Limit = '';
     if ($StatsPerPage > 0)
     {
         $StartIndex = ($Page - 1) * $StatsPerPage;
         $Limit = "LIMIT $StartIndex, $StatsPerPage";
     }

     // We take into account the order by
     if ($OrderBy == "")
     {
         $StrOrderBy = "";
     }
     else
     {
         $StrOrderBy = " ORDER BY $OrderBy";
     }

     // We can launch the SQL request
     $DbResult = $DbConnection->query("$Select $From $Where GROUP BY StatID $Having $StrOrderBy $Limit");
     if (!DB::isError($DbResult))
     {
         if ($DbResult->numRows() != 0)
         {
             // Creation of the result array
             $ArrayStats = array(
                                 "StatID" => array(),
                                 "StatPeriod" => array(),
                                 "StatType" => array(),
                                 "StatSubType" => array(),
                                 "StatValue" => array()
                                );

             while($Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC))
             {
                 $ArrayStats["StatID"][] = $Record["StatID"];
                 $ArrayStats["StatPeriod"][] = $Record["StatPeriod"];
                 $ArrayStats["StatType"][] = $Record["StatType"];
                 $ArrayStats["StatSubType"][] = $Record["StatSubType"];
                 $ArrayStats["StatValue"][] = $Record["StatValue"];
             }

             // Return result
             return $ArrayStats;
         }
     }

     // ERROR
     return array();
 }


/**
 * Get the number of stats filtered by some criterion
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-05-07
 *
 * @param $DbConnection         DB object              Object of the opened database connection
 * @param $ArrayParams          Mixed array            Contains the criterion used to filter the stats
 *
 * @return Integer              Number of the stats found, 0 otherwise
 */
 function getNbdbSearchStat($DbConnection, $ArrayParams)
 {
     // SQL request to find stats
     $Select = "SELECT StatID";
     $From = "FROM Stats";
     $Where = " WHERE 1=1";
     $Having = "";

     if (count($ArrayParams) >= 0)
     {
         // <<< StatID field >>>
         if ((array_key_exists("StatID", $ArrayParams)) && (!empty($ArrayParams["StatID"])))
         {
             if (is_array($ArrayParams["StatID"]))
             {
                 $Where .= " AND StatID IN ".constructSQLINString($ArrayParams["SupportMemberID"]);
             }
             else
             {
                 $Where .= " AND StatID = ".$ArrayParams["StatID"];
             }
         }

         // <<< StatPeriod >>>
         if ((array_key_exists("StatPeriod", $ArrayParams)) && ($ArrayParams["StatPeriod"] != ""))
         {
             $Where .= " AND StatPeriod LIKE \"".$ArrayParams["StatPeriod"]."\"";
         }

         // <<< StatType fields >>>
         if ((array_key_exists("StatType", $ArrayParams)) && ($ArrayParams["StatType"] != ""))
         {
             $Where .= " AND StatType LIKE \"".$ArrayParams["StatType"]."\"";
         }

         // <<< StatSubType field >>>
         if ((array_key_exists("StatSubType", $ArrayParams)) && ($ArrayParams["StatSubType"] != ""))
         {
             $Where .= " AND StatSubType LIKE \"".$ArrayParams["StatSubType"]."\"";
         }

         // <<< StatValue field >>> [0] -> operator [1] -> value
         if ((array_key_exists("StatValue", $ArrayParams)) && (count($ArrayParams["StatValue"]) == 2))
         {
             $Where .= " AND StatValue ".$ArrayParams["StatValue"][0]." ".$ArrayParams["StatValue"][1];
         }
     }

     // We can launch the SQL request
     $DbResult = $DbConnection->query("$Select $From $Where GROUP BY StatID $Having");
     if (!DB::isError($DbResult))
     {
         return $DbResult->numRows();
     }

     // ERROR
     return 0;
 }


/**
 * Treat log events data for GDPR
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-05-10
 *
 * @param $DbConnection                 DB object      Object of the opened database connection
 * @param $AnonymizedSupportMemberID    Integer        ID of the support member used to anonymize data [1..n]
 * @param $ArrayParams                  Mixed array    Contains other parameters to use to apply
 *                                                     GDPR treatment
 *
 * @return Boolean                      TRUE if GDPR treatment is done, FALSE otherwise
 */
 function dbLogEventsGDPRTreatment($DbConnection, $AnonymizedSupportMemberID, $ArrayParams = array())
 {
     if ($AnonymizedSupportMemberID > 0)
     {
         $ArrayConcernedID = array();
         $bLogsDeleted = FALSE;

         // First, we deleted log events linked to deleted support members
         $sSupporterCondition = '';
         $sSupporterConditionSM = '';
         if ((array_key_exists("SupportMemberID", $ArrayParams)) && (!empty($ArrayParams["SupportMemberID"])))
         {
             // To limit to some given supporters
             if (is_array($ArrayParams["SupportMemberID"]))
             {
                 $sSupporterConditionSM = " AND l.SupportMemberID IN ".constructSQLINString($ArrayParams["SupportMemberID"]);
                 $sSupporterCondition = " AND SupportMemberID IN ".constructSQLINString($ArrayParams["SupportMemberID"]);
             }
             else
             {
                 $sSupporterConditionSM = " AND l.SupportMemberID = ".$ArrayParams["SupportMemberID"];
                 $sSupporterCondition = " AND SupportMemberID = ".$ArrayParams["SupportMemberID"];
             }
         }

         $DbResult = $DbConnection->query("SELECT l.LogEventID, l.SupportMemberID, sm.SupportMemberID
                                           FROM LogEvents l LEFT JOIN SupportMembers sm ON (l.SupportMemberID = sm.SupportMemberID)
                                           WHERE l.SupportMemberID > 0 $sSupporterConditionSM
                                           HAVING sm.SupportMemberID IS NULL ");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() >= 0)
             {
                 while($Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC))
                 {
                     $ArrayConcernedID[] = $Record['LogEventID'];
                 }
             }
         }

         if (!empty($ArrayConcernedID))
         {
             $DbResult = $DbConnection->query("DELETE FROM LogEvents
                                               WHERE LogEventID IN ".constructSQLINString($ArrayConcernedID));
         }

         // Then, we delete too old log events
         $sLogDateCondition = " LogEventDate < ".date('Y-m-d', strtotime("5 years ago"));
         if ((array_key_exists("LogEventDate", $ArrayParams)) && (count($ArrayParams["LogEventDate"]) == 2))
         {
             $sLogDateCondition = " LogEventDate ".$ArrayParams["LogEventDate"][0]." \"".$ArrayParams["LogEventDate"][1]."\"";
         }

         $DbResult = $DbConnection->query("DELETE FROM LogEvents WHERE $sLogDateCondition $sSupporterCondition");
         if (!DB::isError($DbResult))
         {
             // Log events deleted
             $bLogsDeleted = TRUE;
         }

         return $bLogsDeleted;
     }

     // ERROR
     return FALSE;
 }
?>