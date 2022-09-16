<?php
/* Copyright (C) 2012 Calandreta Del Païs Murethin
 *
 * This file is part of CanteenCalandreta.
 *
 * CanteenCalandreta is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * CanteenCalandreta is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with CanteenCalandreta; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


/**
 * Common module : library of all database functions
 *
 * @author Christophe Javouhey
 * @version 3.6
 * @since 2012-01-12
 */

 // To use the PEAR library
 require_once("DB.php");

 include_once("DbLogEventsLibrary.php");           // Database primitives library used for the LogEvents table
 include_once("DbSupportMembersLibrary.php");      // Database primitives library used for the SupportMembers table
 include_once("DbSupportMembersStatesLibrary.php");// Database primitives library used for the SupportMembersStates table
 include_once("DbFamiliesLibrary.php");            // Database primitives library used for the Families and HistoFamilies tables
 include_once("DbChildrenLibrary.php");            // Database primitives library used for the Children, HistoLevelsChildren and Suspensions tables
 include_once("DbPaymentsLibrary.php");            // Database primitives library used for the Payments table
 include_once("DbBillsLibrary.php");               // Database primitives library used for the Bills table
 include_once("DbBanksLibrary.php");               // Database primitives library used for the Banks table
 include_once("DbTownsLibrary.php");               // Database primitives library used for the Towns table
 include_once("DbHolidaysLibrary.php");            // Database primitives library used for the Holidays table
 include_once("DbCanteenRegistrations.php");       // Database primitives library used for the CanteenRegistrations and MoreMeals tables
 include_once("DbNurseryRegistrations.php");       // Database primitives library used for the NurseryRegistrations table
 include_once("DbEventsLibrary.php");              // Database primitives library used for the EventTypes and Events tables
 include_once("DbOpenedSpecialDaysLibrary.php");   // Database primitives library used for the OpenedSpecialDays table
 include_once("DbSnackRegistrations.php");         // Database primitives library used for the SnackRegistrations table
 include_once("DbLaundryRegistrations.php");       // Database primitives library used for the LaundryRegistrations table
 include_once("DbExitPermissions.php");            // Database primitives library used for the ExitPermissions table
 include_once("DbWorkGroupsLibrary.php");          // Database primitives library used for the WorkGroups and WorkGroupRegistrations tables
 include_once("DbAliasLibrary.php");               // Database primitives library used for the Alias table
 include_once("DbDonationsLibrary.php");           // Database primitives library used for the Donations table
 include_once("DbJobsLibrary.php");                // Database primitives library used for the Jobs and JobParameters tables
 include_once("DbConfigParametersLibrary.php");    // Database primitives library used for the ConfigParameters table
 include_once("DbDiscountsFamiliesLibrary.php");   // Database primitives library used for the DiscountsFamilies table
 include_once("DbDocumentsApprovalsLibrary.php");  // Database primitives library used for the DocumentsApprovals and DocumentsFamiliesApprovals tables
 include_once("DbUploadedFilesLibrary.php");       // Database primitives library used for the UploadedFiles table
 include_once("DbMeetingRoomsLibrary.php");        // Database primitives library used for the MeetingRooms and MeetingRoomsRegistrations tables
 include_once("DbForumLibrary.php");               // Database primitives library used for the ForumCategories, ForumCategoriesAccess,
                                                   //                                          ForumTopics, ForumMessages, ForumTopicsSubscriptions and
                                                   //                                          ForumTopicsLastReads tables


/**
 * Open a generic connection to the database
 *
 * @author STNA/7SQ
 * @version 2.0
 *     - 2004-04-14 : try to create a localhost connexion if the first connexion fails
 *
 * @since 2004-01-01
 *
 * @return DB object
 */
 function dbConnection()
 {
     switch($GLOBALS["CONF_DB_SGBD_TYPE"])
     {
         case "IBMDB2" :
         case "MSAccess" :
             $PearServerType = "odbc";
             break;
         default:
             $PearServerType = $GLOBALS["CONF_DB_SGBD_TYPE"];
             break;
     }

     $Dsn = "$PearServerType://";

     if (!empty($GLOBALS["CONF_DB_USER"]))
     {
         $Dsn .= $GLOBALS["CONF_DB_USER"];
     }

     if (!empty($GLOBALS["CONF_DB_PASSWORD"]))
     {
         $Dsn .= ":".$GLOBALS["CONF_DB_PASSWORD"];
     }

     $Dsn .= "@".$GLOBALS["CONF_DB_SERVER"];

     if (!empty($GLOBALS["CONF_DB_DATABASE"]))
     {
         $Dsn .= "/".$GLOBALS["CONF_DB_DATABASE"];
     }

     // Open the connection
     $DbCon = DB::connect($Dsn, FALSE);

     // If the connection fails
     if (DB::isError($DbCon))
     {
         // Try to create a localhost connection
         $Dsn = "$PearServerType://";

         if (!empty($GLOBALS["CONF_DB_USER"]))
         {
             $Dsn .= $GLOBALS["CONF_DB_USER"];
         }

         if (!empty($GLOBALS["CONF_DB_PASSWORD"]))
         {
             $Dsn .= ":".$GLOBALS["CONF_DB_PASSWORD"];
         }

         $Dsn .= "@localhost";

         if (!empty($GLOBALS["CONF_DB_DATABASE"]))
         {
             $Dsn .= "/".$GLOBALS["CONF_DB_DATABASE"];
         }

         // Open the localhost connection
         $DbCon = DB::connect($Dsn, FALSE);

         // If the localhost connection fails
         if (DB::isError($DbCon))
         {
             die("$DbCon->message<br /><b>".$GLOBALS["LANG_ERROR_DB_CONNECTION"]."</b>");
         }
     }

     $DbCon->query("SET CHARACTER SET latin1");

     // Return the DB object created
     return $DbCon;
 }


/**
 * Open a generic connection to a given database, with parameters
 *
 * @author STNA/7SQ
 * @version 1.0
 * @since 2009-03-09
 *
 * @param $ServerName               String     Name of the server where the database is hosted
 * @param $Port                     String     Port to connect to the SGBD
 * @param $User                     String     Username to connect to the database
 * @param $Password                 String     Password to connect to the database
 * @param $DatabaseName             String     Name of the database to connect
 * @param $SGBDType                 String     Name of the SGBD (ex : mysql)
 * @param $SGBDVersion              String     Version of the SGBD (ex : 5 for MySQL 5.x)
 * @param $PersistanceConnection    Boolean    Allow persistant connection
 *
 * @return DB object
 */
 function dbConnectionByParams($ServerName, $Port, $User, $Password, $DatabaseName, $SGBDType, $SGBDVersion, $PersistanceConnection = FALSE)
 {
     switch($SGBDType)
     {
         case "IBMDB2" :
         case "MSAccess" :
             $PearServerType = "odbc";
             break;
         default:
             $PearServerType = $SGBDType;
             break;
     }

     $Dsn = "$PearServerType://";

     if (!empty($User))
     {
         $Dsn .= $User;
     }

     if (!empty($Password))
     {
         $Dsn .= ":$Password";
     }

     $Dsn .= "@$ServerName";

     if (!empty($DatabaseName))
     {
         $Dsn .= "/$DatabaseName";
     }

     // Open the connection
     $DbCon = DB::connect($Dsn, FALSE);

     // If the connection fails
     if (DB::isError($DbCon))
     {
         // Try to create a localhost connection
         $Dsn = "$PearServerType://";

         if (!empty($User))
         {
             $Dsn .= $User;
         }

         if (!empty($Password))
         {
             $Dsn .= ":$Password";
         }

         $Dsn .= "@localhost";

         if (!empty($DatabaseName))
         {
             $Dsn .= "/$DatabaseName";
         }

         // Open the localhost connection
         $DbCon = DB::connect($Dsn, FALSE);

         // If the localhost connection fails
         if (DB::isError($DbCon))
         {
             die("$DbCon->message<br /><b>".$GLOBALS["LANG_ERROR_DB_CONNECTION"]."</b>");
         }
     }

     $DbCon->query("SET CHARACTER SET latin1");

     // Return the DB object created
     return $DbCon;
 }


/**
 * Generate a new primary key to simulate the auto-incrementation
 *
 * @author STNA/7SQ
 * @version 1.0
 * @since 2004-01-17
 *
 * @param $DbConnection               DB object    Object of the opened database connection
 * @param $TableName                  String       The primary key is generated for this table
 * @param $PrimaryKeyFieldName        String       Name of the primary key field
 *
 * @return Integer                                 ID generated, 0 otherwise
 */
 function getNewPrimaryKey($DbConnection, $TableName, $PrimaryKeyFieldName)
 {
     $DbResult = $DbConnection->getOne("SELECT MAX($PrimaryKeyFieldName) FROM $TableName");
     if (!DB::isError($DbResult))
     {
         // Auto-incrementation
         return $DbResult + 1;
     }

     // ERROR
     return 0;
 }


/**
 * Get fieldnames of a table
 *
 * @author STNA/7SQ
 * @version 1.0
 * @since 2009-03-02
 *
 * @param $DbConnection               DB object    Object of the opened database connection
 * @param $TableName                  String       Table for which we want fieldnames
 * @param $DatabaseName               String       DatabaseName to use if different from the
 *                                                 database declared in CONF_DB_DATABASE
 *
 * @return Array of Strings           Fieldnames of the table, empty array otherwise
 */
 function getTableFieldnames($DbConnection, $TableName, $DatabaseName = '')
 {
     if (empty($DatabaseName))
     {
         // Default database to use
         $DatabaseName = $GLOBALS["CONF_DB_DATABASE"];
     }

     if (!empty($TableName))
     {
         switch($GLOBALS['CONF_DB_SGBD_TYPE'])
         {
             case 'mysql':
             default:
                 switch($GLOBALS['CONF_DB_SGBD_VERSION'])
                 {
                     case 5:
                         // MySql 5
                         $DbResult = $DbConnection->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = \"$DatabaseName\" AND TABLE_NAME = \"$TableName\" ORDER BY ORDINAL_POSITION");
                         if (!DB::isError($DbResult))
                         {
                             // There are fields for this table
                             if ($DbResult->numRows() > 0)
                             {
                                 $ArrayFieldnames = array();
                                 while ($Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC))
                                 {
                                     $ArrayFieldnames[] = $Record["COLUMN_NAME"];
                                 }

                                 return $ArrayFieldnames;
                             }
                         }
                         break;

                     default:
                         // MySql < 5 (3, 4...)
                         $DbResult = $DbConnection->getAll("SELECT * FROM $TableName LIMIT 0,1", array(), DB_FETCHMODE_ASSOC);
                         if (!DB::isError($DbResult))
                         {
                             return array_keys($DbResult[0]);
                         }
                         break;
                 }
                 break;
         }
     }

     // Error
     return array();
 }


/**
 * Get content of a table, for all fields or just some given fields
 *
 * @author STNA/7SQ
 * @version 1.0
 * @since 2009-03-02
 *
 * @param $DbConnection               DB object           Object of the opened database connection
 * @param $TableName                  String              Table for which we want its content
 * @param $OrderBy                    String              List of fields to order the records found
 * @param $ArrayFieldsToReturn        Array of Strings    List of fields to get
 * @param $DatabaseName               String              DatabaseName to use if different from the
 *                                                        database declared in CONF_DB_DATABASE
 *
 * @return Mixed array                Content of the table with the given fields,
 *                                    empty array otherwise
 */
 function getTableContent($DbConnection, $TableName, $OrderBy, $ArrayFieldsToReturn = array(), $DatabaseName = '')
 {
     if (empty($DatabaseName))
     {
         // Default database to use
         $DatabaseName = $GLOBALS["CONF_DB_DATABASE"];
     }

     if (!empty($TableName))
     {
         $ArrayOfTable = getTableFieldnames($DbConnection, $TableName, $DatabaseName);
         if (empty($ArrayFieldsToReturn))
         {
             // The fields to return are all fields of the table
             $ArrayFieldsToReturn = $ArrayOfTable;
         }
         else
         {
             // We return only some fields
             $ArrayFieldsToReturn = array_values(array_intersect($ArrayFieldsToReturn, $ArrayOfTable));
         }

         if (empty($OrderBy))
         {
             // By default, we order by the first field (primary key)
             $OrderBy = $ArrayFieldsToReturn[0];
         }

         $DbResult = $DbConnection->query("SELECT ".implode(', ', $ArrayFieldsToReturn)." FROM $TableName ORDER BY $OrderBy");
         if (!DB::isError($DbResult))
         {
             // There are fields for this table
             if ($DbResult->numRows() > 0)
             {
                 $ArrayResult = array();

                 while ($Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC))
                 {
                     // For this record, we get the value of each field n the table
                     foreach($ArrayFieldsToReturn as $f => $CurrentFieldName)
                     {
                         $ArrayResult[$CurrentFieldName][] = $Record[$CurrentFieldName];
                     }
                 }

                 return $ArrayResult;
             }
         }
     }

     // Error
     return array();
 }


/**
 * Get a record of a table, for all fields or just some given fields
 *
 * @author STNA/7SQ
 * @version 1.1
 *     - 2011-09-30 : patch a bug when $ObjectID is empty
 *
 * @since 2010-04-07
 *
 * @param $DbConnection               DB object           Object of the opened database connection
 * @param $TableName                  String              Table for which we want a record
 * @param $ObjectID                   Integer             The primary key of the record to get
 * @param $ArrayFieldsToReturn        Array of Strings    List of fields to get
 * @param $DatabaseName               String              DatabaseName to use if different from the
 *                                                        database declared in CONF_DB_DATABASE
 *
 * @return Mixed array                One record of the table with the given fields,
 *                                    empty array otherwise
 */
 function getTableRecordInfos($DbConnection, $TableName, $ObjectID, $ArrayFieldsToReturn = array(), $DatabaseName = '')
 {
     if (empty($DatabaseName))
     {
         // Default database to use
         $DatabaseName = $GLOBALS["CONF_DB_DATABASE"];
     }

     if ((!empty($TableName)) && ($ObjectID > 0))
     {
         $ArrayOfTable = getTableFieldnames($DbConnection, $TableName, $DatabaseName);
         if (empty($ArrayFieldsToReturn))
         {
             // The fields to return are all fields of the table
             $ArrayFieldsToReturn = $ArrayOfTable;
         }
         else
         {
             // We return only some fields
             $ArrayFieldsToReturn = array_values(array_intersect($ArrayFieldsToReturn, $ArrayOfTable));
         }

         // $ArrayOfTable[0] contains the primary key, so the ID
         $DbResult = $DbConnection->query("SELECT ".implode(', ', $ArrayFieldsToReturn)." FROM $TableName WHERE ".$ArrayOfTable[0]." = $ObjectID");
         if (!DB::isError($DbResult))
         {
             // There are fields for this table
             if ($DbResult->numRows() > 0)
             {
                 return $DbResult->fetchRow(DB_FETCHMODE_ASSOC);
             }
         }
     }

     // Error
     return array();
 }


/**
 * Give the value of a field of a given custom field in the CustomFields table
 *
 * @author STNA/7SQ
 * @version 1.0
 * @since 2009-03-04
 *
 * @param $DbConnection         DB object    Object of the opened database connection
 * @param $TableName            String       Table for which we want the value of one of its records
 * @param $ObjectID             Integer      ID of the object to get its value [1..n]
 * @param $Fieldname            String       Name of the field of which we want to get the value
 *
 * @return mixed                Value of the fieldname of the given object ID, -1 otherwise
 */
 function getTableFieldValue($DbConnection, $TableName, $ObjectID, $Fieldname)
 {
     // Check if the fieldname given is a field of the CustomFields table
     if ((!empty($TableName)) && ($ObjectID > 0) && (!empty($Fieldname)))
     {
         $ArrayTableFields = getTableFieldnames($DbConnection, $TableName);
         if (in_array($Fieldname, $ArrayTableFields))
         {
             // $ArrayTableFields[0] contains the primary key, so the ID
             $DbResult = $DbConnection->getOne("SELECT $Fieldname FROM $TableName WHERE ".$ArrayTableFields[0]." = $ObjectID");
             if (!DB::isError($DbResult))
             {
                 return $DbResult;
             }
         }
     }

     // ERROR
     return -1;
 }


/**
 * Give the value of a field for a given field value in the given table
 *
 * @author DTI/DSO/SLI
 * @version 1.0
 * @since 2012-04-23
 *
 * @param $DbConnection         DB object    Object of the opened database connection
 * @param $TableName            String       Table for which we want the value of one or several records
 * @param $FieldNameToGet       String       Name of the field of which we want to get the value
 * @param $UsedFieldName        String       Name of the field used to get the value
 * @param $UsedOp               String       Operator used to get the value of the field (=, <, >...)
 * @param $UsedValue            Mixed        Value used to filter and get the field
 * @param $OrderBy              String       To order the found values (if several)
 * @param $DatabaseName         String       DatabaseName to use if different from the
 *                                           database declared in CONF_DB_DATABASE
 *
 * @return Mixed                Value of the FieldNameToGet (one value or an array if several found values),
 *                              FALSE otherwise
 */
 function getTableFieldValueByFieldName($DbConnection, $TableName, $FieldNameToGet, $UsedFieldName, $UsedOp = '=', $UsedValue = '', $OrderBy = '', $DatabaseName = '', $Limit = 0)
 {
     if (empty($DatabaseName))
     {
         // Default database to use
         $DatabaseName = $GLOBALS['CONF_DB_DATABASE'];
     }

     // Check if FieldNameToGet and FieldNameToGet given are fields of the table
     if ((!empty($TableName)) && (!empty($FieldNameToGet)) && (!empty($UsedFieldName)) && ($Limit >= 0))
     {
         $ArrayTableFields = getTableFieldnames($DbConnection, $TableName, $DatabaseName);
         if (
             ($ArrayTableFields !== FALSE)
             && (in_array($FieldNameToGet, $ArrayTableFields))
             && (in_array($UsedFieldName, $ArrayTableFields))
            )
         {
             $OrderByCondition = '';
             if (!empty($OrderBy))
             {
                 $OrderByCondition = "ORDER BY $OrderBy";
             }

             $LimitCondition = '';
             if ($Limit > 0)
             {
                 // To limit the number of found records
                 $LimitCondition = "LIMIT 0, $Limit";
             }

             // Check the type of the value
             if (is_bool($UsedValue))
             {
                 // Boolean type
                 if ($UsedValue)
                 {
                     $UsedValue = 1;
                 }
                 else
                 {
                     $UsedValue = 0;
                 }
             }
             elseif (is_array($UsedValue))
             {
                 // Array type : $UsedOp must be IN or NOT IN
                 $UsedValue = constructSQLINString($UsedValue);
             }
             elseif (is_string($UsedValue))
             {
                 // String type
                 $UsedValue = "\"$UsedValue\"";
             }
             elseif (is_null($UsedValue))
             {
                 // $UsedOp must be IS or IS NOT
                 $UsedValue = 'NULL';
             }

             $DbResult = $DbConnection->query("SELECT $FieldNameToGet FROM $DatabaseName.$TableName
                                               WHERE $UsedFieldName $UsedOp $UsedValue $OrderByCondition $LimitCondition");


             if (!DB::isError($DbResult))
             {
                 $NbRecords = $DbResult->numRows();
                 switch($NbRecords)
                 {
                     case 0:
                         // No record found
                         return NULL;

                     case 1:
                         // One record found
                         $Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC);
                         return $Record[$FieldNameToGet];
                         break;

                     default:
                         // > 1 record found
                         $ArrayResult = array();
                         while ($Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC))
                         {
                             $ArrayResult[$FieldNameToGet][] = $Record[$FieldNameToGet];
                         }

                         return $ArrayResult;
                         break;
                 }
             }
         }
     }

     // ERROR
     return FALSE;
 }


/**
 * Close a generic connection to the database
 *
 * @author STNA/7SQ
 * @version 1.0
 * @since 2004-01-01
 *
 * @param $DbConnection         DB object       Object of the opened database connection
 *
 * @return TRUE
 */
 function dbDisconnection($DbConnection)
 {
     // The connection is a persistance connection?
     if ($GLOBALS["CONF_DB_PERSISTANCE_CONNECTION"])
     {
         return TRUE;
     }
     else
     {
         $DbConnection->disconnect();
         return TRUE;
     }
 }


/**
 * Close a generic connection to a database opened by dbConnectionByParams()
 *
 * @author STNA/7SQ
 * @version 1.0
 * @since 2009-03-09
 *
 * @param $DbConnection             DB object       Object of the opened database connection
 * @param $PersistanceConnection    Boolean         Allow persistant connection
 *
 * @return TRUE
 */
 function dbDisconnectionByParams($DbConnection, $PersistanceConnection = FALSE)
 {
     // The connection is a persistance connection?
     if ($PersistanceConnection)
     {
         return TRUE;
     }
     else
     {
         $DbConnection->disconnect();
         return TRUE;
     }
 }


/**
 * Get data bout GDPR filtered by some criterion
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-05-05
 *
 * @param $DbConnection             DB object              Object of the opened database connection
 * @param $ArrayParams              Mixed array            Contains the criterion used to filter the  data about GDPR
 * @param $OrderBy                  String                 Criteria used to sort the data about GDPR. If < 0, DESC is used,
 *                                                         otherwise ASC is used
 * @param $Page                     Integer                Number of the page to return [1..n]
 * @param $RecordsPerPage           Integer                Number of data bout GDPR per page to return [1..n]
 *
 * @return Array of String          List of data about GDPR filtered, an empty array otherwise
 */
 function dbSearchGDPRData($DbConnection, $ArrayParams, $OrderBy = "", $Page = 1, $RecordsPerPage = 10)
 {
     // SQL request to find data about GDPR
     // Generate SQL query for each table
     $ArrayConcernedTables = dbSearchGDPRDataGenerateQueries($DbConnection, $ArrayParams);

     // We take into account the page and the number of data about RGPD per page
     if ($Page < 1)
     {
         $Page = 1;
     }

     if ($RecordsPerPage < 0)
     {
         $RecordsPerPage = 10;
     }

     $Limit = '';
     if ($RecordsPerPage > 0)
     {
         $StartIndex = ($Page - 1) * $RecordsPerPage;
         $Limit = "LIMIT $StartIndex, $RecordsPerPage";
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
     $sSQL = implode(" UNION ", $ArrayConcernedTables);
     $DbResult = $DbConnection->query("$sSQL $StrOrderBy $Limit");

     if (!DB::isError($DbResult))
     {
         if ($DbResult->numRows() != 0)
         {
             // Creation of the result array
             $ArrayRecords = array(
                                   "TableName" => array(),
                                   "NbRecords" => array()
                                  );

             while($Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC))
             {
                 $ArrayRecords['TableName'][] = $Record['TableName'];
                 $ArrayRecords['NbRecords'][] = $Record['NB'];
             }

             // Return result
             return $ArrayRecords;
         }
     }

     // ERROR
     return array();
 }


/**
 * Get the number of data about GDPR filtered by some criterion
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-05-05
 *
 * @param $DbConnection         DB object              Object of the opened database connection
 * @param $ArrayParams          Mixed array            Contains the criterion used to filter the data about GDPR
 *
 * @return Integer              Number of the data about GDPR found, 0 otherwise
 */
 function getNbdbSearchGDPRData($DbConnection, $ArrayParams)
 {
     // Generate SQL query for each table
     $ArrayConcernedTables = dbSearchGDPRDataGenerateQueries($DbConnection, $ArrayParams);

     // We can launch the SQL request
     $sSQL = implode(" UNION ", $ArrayConcernedTables);
     $DbResult = $DbConnection->query($sSQL);
     if (!DB::isError($DbResult))
     {
         return $DbResult->numRows();
     }

     // ERROR
     return 0;
 }


/**
 * Generate SQL queries about data concerned by the GDPR filtered by some criterion
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-05-06
 *
 * @param $DbConnection         DB object              Object of the opened database connection
 * @param $ArrayParams          Mixed array            Contains the criterion used to filter the data about GDPR
 *
 * @return Array of strings     SQL queries to find data concerned by GDPR, empty arry otherwise
 */
 function dbSearchGDPRDataGenerateQueries($DbConnection, $ArrayParams)
 {
     // SQL request to find data about GDPR
     $ArrayConcernedTables = array();
     $Having = " HAVING NB > 0";

     if (count($ArrayParams) >= 0)
     {
         // <<< CanteenRegistrations table >>>
         if ((array_key_exists("CanteenRegistrations", $ArrayParams)) && (count($ArrayParams["CanteenRegistrations"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $ArrayConcernedTables["CanteenRegistrations"] = "SELECT 'CanteenRegistrations' AS TableName, COUNT(CanteenRegistrationID) AS NB
                                                              FROM CanteenRegistrations WHERE CanteenRegistrationForDate "
                                                             .$ArrayParams["CanteenRegistrations"][0]." \"".$ArrayParams["CanteenRegistrations"][1]."\"
                                                              $Having";
         }

         // <<< Children table >>>
         if ((array_key_exists("Children", $ArrayParams)) && (count($ArrayParams["Children"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             // c.ChildFirstname REGEXP 'Ch[0-9]+' : to exclude already anonymized children
             $ArrayConcernedTables["Children"] = "SELECT 'Children' AS TableName, COUNT(c.ChildID) AS NB
                                                  FROM Children c, Families f WHERE c.FamilyID = f.FamilyID
                                                  AND (c.ChildEmail IS NULL OR c.ChildEmail = '')
                                                  AND c.ChildFirstname REGEXP 'Ch[0-9]+' = 0
                                                  AND f.FamilyDesactivationDate IS NOT NULL AND f.FamilyDesactivationDate "
                                                 .$ArrayParams["Children"][0]." \"".$ArrayParams["Children"][1]."\" $Having";
         }

         // <<< DiscountsFamilies table >>>
         if ((array_key_exists("DiscountsFamilies", $ArrayParams)) && (count($ArrayParams["DiscountsFamilies"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $ArrayConcernedTables["DiscountsFamilies"] = "SELECT 'DiscountsFamilies' AS TableName, COUNT(df.DiscountFamilyID) AS NB
                                                           FROM DiscountsFamilies df, Families f
                                                           WHERE df.FamilyID = f.FamilyID
                                                           AND f.FamilyDesactivationDate IS NOT NULL AND f.FamilyDesactivationDate "
                                                          .$ArrayParams["DiscountsFamilies"][0]." \"".$ArrayParams["DiscountsFamilies"][1]."\" $Having";
         }

         // <<< DocumentsApprovals table >>>
         if ((array_key_exists("DocumentsApprovals", $ArrayParams)) && (count($ArrayParams["DocumentsApprovals"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $ArrayConcernedTables["DocumentsApprovals"] = "SELECT 'DocumentsApprovals' AS TableName, COUNT(DocumentApprovalID) AS NB
                                                         FROM DocumentsApprovals WHERE DocumentApprovalDate "
                                                        .$ArrayParams["DocumentsApprovals"][0]." \"".$ArrayParams["DocumentsApprovals"][1]."\"
                                                         $Having";
         }

         // <<< DocumentsFamiliesApprovals table >>>
         if ((array_key_exists("DocumentsFamiliesApprovals", $ArrayParams)) && (count($ArrayParams["DocumentsFamiliesApprovals"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $ArrayConcernedTables["DocumentsFamiliesApprovals"] = "SELECT 'DocumentsFamiliesApprovals' AS TableName, COUNT(dfa.DocumentFamilyApprovalID) AS NB
                                                                    FROM Families f, SupportMembers sm, DocumentsFamiliesApprovals dfa
                                                                    WHERE f.FamilyID = sm.FamilyID AND sm.SupportMemberID = dfa.SupportMemberID
                                                                    AND f.FamilyDesactivationDate IS NOT NULL AND f.FamilyDesactivationDate "
                                                                   .$ArrayParams["DocumentsFamiliesApprovals"][0]." \"".$ArrayParams["DocumentsFamiliesApprovals"][1]."\"
                                                                    $Having";
         }

         // <<< Events table >>>
         if ((array_key_exists("Events", $ArrayParams)) && (count($ArrayParams["Events"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $sAnonymeSupporterCondition = '';
             if ((array_key_exists("AnonymizedSupportMemberID", $ArrayParams)) && ($ArrayParams["AnonymizedSupportMemberID"] > 0))
             {
                 $sAnonymeSupporterCondition = " AND sm.SupportMemberID <> ".$ArrayParams["AnonymizedSupportMemberID"];
             }

             $ArrayConcernedTables["Events"] = "SELECT 'Events' AS TableName, COUNT(e.EventID) AS NB
                                                FROM SupportMembers sm, Events e
                                                WHERE sm.SupportMemberID = e.SupportMemberID AND sm.SupportMemberActivated = 0
                                                AND e.EventStartDate ".$ArrayParams["Events"][0]." \"".$ArrayParams["Events"][1]."\"
                                                $sAnonymeSupporterCondition $Having";
         }

         // <<< EventRegistrations table >>>
         if ((array_key_exists("EventRegistrations", $ArrayParams)) && (count($ArrayParams["EventRegistrations"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $ArrayConcernedTables["EventRegistrations"] = "SELECT 'EventRegistrations' AS TableName, COUNT(er.EventRegistrationID) AS NB
                                                            FROM Events e, EventRegistrations er
                                                            WHERE e.EventID = er.EventID
                                                            AND e.EventStartDate ".$ArrayParams["EventRegistrations"][0]
                                                            ." \"".$ArrayParams["EventRegistrations"][1]."\" $Having";
         }

         // <<< EventSwappedRegistrations table >>>
         if ((array_key_exists("EventSwappedRegistrations", $ArrayParams)) && (count($ArrayParams["EventSwappedRegistrations"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $ArrayConcernedTables["EventSwappedRegistrations"] = "SELECT 'EventSwappedRegistrations' AS TableName, COUNT(esr.EventSwappedRegistrationID) AS NB
                                                                   FROM Events e, EventSwappedRegistrations esr
                                                                   WHERE (e.EventID = esr.RequestorEventID OR e.EventID = esr.AcceptorEventID)
                                                                   AND e.EventStartDate ".$ArrayParams["EventSwappedRegistrations"][0]
                                                                  ." \"".$ArrayParams["EventSwappedRegistrations"][1]."\" $Having";
         }

         // <<< ExitPermissions table >>>
         if ((array_key_exists("ExitPermissions", $ArrayParams)) && (count($ArrayParams["ExitPermissions"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $ArrayConcernedTables["ExitPermissions"] = "SELECT 'ExitPermissions' AS TableName, COUNT(ExitPermissionID) AS NB
                                                         FROM ExitPermissions WHERE ExitPermissionDate "
                                                        .$ArrayParams["ExitPermissions"][0]." \"".$ArrayParams["ExitPermissions"][1]."\"
                                                         $Having";
         }

         // <<< Families table >>>
         if ((array_key_exists("Families", $ArrayParams)) && (count($ArrayParams["Families"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             // f.FamilyLastname REGEXP 'F[0-9]+' : to exclude already anonymized families
             $ArrayConcernedTables["Families"] = "SELECT 'Families' AS TableName, COUNT(f.FamilyID) AS NB
                                                  FROM Families f
                                                  WHERE f.FamilyMainEmailContactAllowed = 0 AND f.FamilySecondEmailContactAllowed = 0
                                                  AND f.FamilyLastname REGEXP 'F[0-9]+' = 0
                                                  AND f.FamilyDesactivationDate IS NOT NULL AND f.FamilyDesactivationDate "
                                                 .$ArrayParams["Families"][0]." \"".$ArrayParams["Families"][1]."\" $Having";
         }

         // <<< HistoFamilies table >>>
         if ((array_key_exists("HistoFamilies", $ArrayParams)) && (count($ArrayParams["HistoFamilies"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $ArrayConcernedTables["HistoFamilies"] = "SELECT 'HistoFamilies' AS TableName, COUNT(hf.HistoFamilyID) AS NB
                                                       FROM HistoFamilies hf, Families f
                                                       WHERE hf.FamilyID = f.FamilyID
                                                       AND f.FamilyDesactivationDate IS NOT NULL AND f.FamilyDesactivationDate "
                                                      .$ArrayParams["HistoFamilies"][0]." \"".$ArrayParams["HistoFamilies"][1]."\" $Having";
         }

         // <<< HistoLevelsChildren table >>>
         if ((array_key_exists("HistoLevelsChildren", $ArrayParams)) && (count($ArrayParams["HistoLevelsChildren"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $ArrayConcernedTables["HistoLevelsChildren"] = "SELECT 'HistoLevelsChildren' AS TableName, COUNT(h.HistoLevelChildID) AS NB
                                                             FROM HistoLevelsChildren h, Children c, Families f
                                                             WHERE h.ChildID = c.ChildID AND c.FamilyID = f.FamilyID
                                                             AND (c.ChildEmail IS NULL OR c.ChildEmail = '')
                                                             AND f.FamilyDesactivationDate IS NOT NULL AND f.FamilyDesactivationDate "
                                                            .$ArrayParams["HistoLevelsChildren"][0]." \"".$ArrayParams["HistoLevelsChildren"][1]."\"
                                                             $Having";
         }

         // <<< LaundryRegistrations table >>>
         if ((array_key_exists("LaundryRegistrations", $ArrayParams)) && (count($ArrayParams["LaundryRegistrations"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $ArrayConcernedTables["LaundryRegistrations"] = "SELECT 'LaundryRegistrations' AS TableName, COUNT(LaundryRegistrationID) AS NB
                                                              FROM LaundryRegistrations WHERE LaundryRegistrationDate "
                                                             .$ArrayParams["LaundryRegistrations"][0]." \"".$ArrayParams["LaundryRegistrations"][1]."\"
                                                              $Having";
         }

         // <<< LogEvents table >>>
         if ((array_key_exists("LogEvents", $ArrayParams)) && (count($ArrayParams["LogEvents"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $ArrayConcernedTables["LogEvents"] = "SELECT 'LogEvents' AS TableName, COUNT(LogEventID) AS NB
                                                   FROM LogEvents WHERE LogEventDate "
                                                  .$ArrayParams["LogEvents"][0]." \"".$ArrayParams["LogEvents"][1]."\"
                                                   $Having";
         }

         // <<< MeetingRoomsRegistrations table >>>
         if ((array_key_exists("MeetingRoomsRegistrations", $ArrayParams)) && (count($ArrayParams["MeetingRoomsRegistrations"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $ArrayConcernedTables["MeetingRoomsRegistrations"] = "SELECT 'MeetingRoomsRegistrations' AS TableName, COUNT(MeetingRoomRegistrationID) AS NB
                                                                   FROM MeetingRoomsRegistrations WHERE MeetingRoomRegistrationStartDate "
                                                                  .$ArrayParams["MeetingRoomsRegistrations"][0]." \"".$ArrayParams["MeetingRoomsRegistrations"][1]."\"
                                                                   $Having";
         }

         // <<< MoreMeals table >>>
         if ((array_key_exists("MoreMeals", $ArrayParams)) && (count($ArrayParams["MoreMeals"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $ArrayConcernedTables["MoreMeals"] = "SELECT 'MoreMeals' AS TableName, COUNT(MoreMealID) AS NB
                                                   FROM MoreMeals WHERE MoreMealDate "
                                                  .$ArrayParams["MoreMeals"][0]." \"".$ArrayParams["MoreMeals"][1]."\" $Having";
         }

         // <<< NurseryRegistrations table >>>
         if ((array_key_exists("NurseryRegistrations", $ArrayParams)) && (count($ArrayParams["NurseryRegistrations"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $ArrayConcernedTables["NurseryRegistrations"] = "SELECT 'NurseryRegistrations' AS TableName, COUNT(NurseryRegistrationID) AS NB
                                                              FROM NurseryRegistrations WHERE NurseryRegistrationForDate "
                                                             .$ArrayParams["NurseryRegistrations"][0]." \"".$ArrayParams["NurseryRegistrations"][1]."\"
                                                             $Having";
         }

         // <<< SnackRegistrations table >>>
         if ((array_key_exists("SnackRegistrations", $ArrayParams)) && (count($ArrayParams["SnackRegistrations"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $ArrayConcernedTables["SnackRegistrations"] = "SELECT 'SnackRegistrations' AS TableName, COUNT(SnackRegistrationID) AS NB
                                                            FROM SnackRegistrations WHERE SnackRegistrationDate "
                                                           .$ArrayParams["SnackRegistrations"][0]." \"".$ArrayParams["SnackRegistrations"][1]."\"
                                                            $Having";
         }

         // <<< Suspensions table >>>
         if ((array_key_exists("Suspensions", $ArrayParams)) && (count($ArrayParams["Suspensions"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $ArrayConcernedTables["Suspensions"] = "SELECT 'Suspensions' AS TableName, COUNT(s.SuspensionID) AS NB
                                                     FROM Suspensions s, Children c, Families f
                                                     WHERE s.ChildID = c.ChildID AND c.FamilyID = f.FamilyID
                                                     AND (c.ChildEmail IS NULL OR c.ChildEmail = '')
                                                     AND f.FamilyDesactivationDate IS NOT NULL AND f.FamilyDesactivationDate "
                                                    .$ArrayParams["Suspensions"][0]." \"".$ArrayParams["Suspensions"][1]."\" $Having";
         }

         // <<< WorkGroupRegistrations table >>>
         if ((array_key_exists("WorkGroupRegistrations", $ArrayParams)) && (count($ArrayParams["WorkGroupRegistrations"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $ArrayConcernedTables["WorkGroupRegistrations"] = "SELECT 'WorkGroupRegistrations' AS TableName, COUNT(LogEventID) AS NB
                                                                FROM LogEvents WHERE LogEventItemType = \"".EVT_WORKGROUP."\"
                                                                AND LogEventService = \"".EVT_SERV_WORKGROUP_REGISTRATION."\"
                                                                AND LogEventDate ".$ArrayParams["WorkGroupRegistrations"][0]." \""
                                                               .$ArrayParams["WorkGroupRegistrations"][1]."\" $Having";
         }
     }

     return $ArrayConcernedTables;
 }
?>