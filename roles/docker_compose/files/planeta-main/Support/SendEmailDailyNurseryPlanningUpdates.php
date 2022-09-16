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
 * Support module : Send an e-mail containg the planning of the canteen for the next day
 * For some cases, the next can be another day (because of week-end or vacations)
 *
 * @author Christophe Javouhey
 * @version 3.7
 * @since 2021-12-27
 */

 if (!function_exists('getIntranetRootDirectoryHDD'))
 {
    /**
     * Give the path of the Intranet root directory on the HDD
     *
     * @author Christophe Javouhey
     * @version 1.1
     *     - 2021-12-27 : v1.1. Replace $sLocalDir{0} by substr($sLocalDir, 0, 1) for PHP8
     *
     * @since 2012-03-20
     *
     * @return String             Intranet root directory on the HDD
     */
     function getIntranetRootDirectoryHDD()
     {
         $sLocalDir = str_replace(array("\\"), array("/"), dirname(__FILE__)).'/';
         $bUnixOS = FALSE;
         if (substr($sLocalDir, 0, 1) == '/')
         {
             $bUnixOS = TRUE;
         }

         $ArrayTmp = explode('/', $sLocalDir);

         $iPos = array_search("CanteenCalandreta", $ArrayTmp);
         if ($iPos !== FALSE)
         {
             $sLocalDir = '';
             if ($bUnixOS)
             {
                 $sLocalDir = '/';
             }

             for($i = 0; $i <= $iPos; $i++)
             {
                 $sLocalDir .= $ArrayTmp[$i].'/';
             }
         }

         return $sLocalDir;
     }
 }

 $DOCUMENT_ROOT = getIntranetRootDirectoryHDD();

 include_once($DOCUMENT_ROOT.'GUI/GraphicInterface.php');

 $CONF_EMAIL_TEMPLATES_DIRECTORY_HDD = $DOCUMENT_ROOT."Templates/";

 $NotificationType = 'TodayNurseryPlanningUpdates';

 $DbCon = dbConnection();

 // Load all configuration variables from database
 loadDbConfigParameters($DbCon, array('CONF_SCHOOL_YEAR_START_DATES',
                                      'CONF_CLASSROOMS',
                                      'CONF_NURSERY_OTHER_TIMESLOTS',
                                      'CONF_NURSERY_PRICES'));

 $ArrayDatesToTreat = array();  // In this array, we set date for which we must send an e-mail

 // Get the next day
 $CurrentDate = date('Y-m-d');
 $CurrentDateStamp = strtotime($CurrentDate);
 $ArrayDatesToTreat[] = $CurrentDate;

 // We check if we must send a notification
 if ((isset($CONF_NURSERY_NOTIFICATIONS[$NotificationType][Template]))
     && (!empty($CONF_NURSERY_NOTIFICATIONS[$NotificationType][Template]))
     && (!empty($CONF_NURSERY_NOTIFICATIONS[$NotificationType][To]))
    )
 {
     foreach($ArrayDatesToTreat as $dtt => $DateToTreat)
     {
         // We get nursery planning updates done during the "DateToTreat"
         $ArrayNurseryUpdates = dbSearchLogEvent($DbCon, array(
                                                               'LogEventItemType' => array(EVT_NURSERY),
                                                               'LogEventService' => array(EVT_SERV_PLANNING),
                                                               'LogEventDate' => array('=', date($CONF_DATE_DISPLAY_FORMAT, strtotime($DateToTreat)))
                                                              ), "LogEventDate", 1, 0);

         if ((isset($ArrayNurseryUpdates['LogEventID'])) && (!empty($ArrayNurseryUpdates['LogEventID'])))
         {
             $ArrayUpdatesDone = array();
             foreach($ArrayNurseryUpdates['LogEventID'] as $u => $CurrentLogEventID)
             {
                 // Remove prefix to have only infos about child and nursery date
                 $sCurrentTitle = str_replace(
                                              array(
                                                    $LANG_NURSERY.' : ',
                                                    $CONF_LOG_EVENTS_TITLE_PREFIX[EVT_ACT_ADD],
                                                    $CONF_LOG_EVENTS_TITLE_PREFIX[EVT_ACT_UPDATE],
                                                    $CONF_LOG_EVENTS_TITLE_PREFIX[EVT_ACT_DELETE]
                                                   ),
                                              array('', '', '', ''), $ArrayNurseryUpdates['LogEventTitle'][$u]);

                 // Remove slots names
                 if (preg_match("/(.)*(\(\d\d\/\d\d\/\d\d\d\d)/", $sCurrentTitle, $ArrayExtractData) != 0)
                 {
                     // We check if the concerned day is >= current date
                     $bKeepUpdate = TRUE;
                     $NurseryRegistrationForDate = formatedDate2EngDate(substr($ArrayExtractData[2], 1));
                     if (strtotime($NurseryRegistrationForDate) <= $CurrentDateStamp)
                     {
                         // Concerned day by the update too old
                         $bKeepUpdate = FALSE;
                     }

                     if ($bKeepUpdate)
                     {
                         // We keep this update entry
                         $sCurrentTitle = $ArrayExtractData[0].')';
                         if (!isset($ArrayUpdatesDone[$sCurrentTitle]))
                         {
                             $ArrayUpdatesDone[$sCurrentTitle] = array();
                         }

                         // We try to detect the ChildID
                         $ChildID = NULL;
                         switch($ArrayNurseryUpdates['LogEventAction'][$u])
                         {
                             case EVT_ACT_ADD:
                             case EVT_ACT_UPDATE:
                                 // The LogEventLinkedObjectID is a nursery registration : we check if it still exists
                                 if (isExistingNurseryRegistration($DbCon, $ArrayNurseryUpdates['LogEventLinkedObjectID'][$u]))
                                 {
                                     $FieldValue = getTableFieldValue($DbCon, 'NurseryRegistrations',
                                                                      $ArrayNurseryUpdates['LogEventLinkedObjectID'][$u], 'ChildID');
                                     if ($FieldValue != -1)
                                     {
                                         $ChildID = $FieldValue;
                                     }
                                 }
                                 break;

                             case EVT_ACT_UPDATE:
                                 // The LogEventLinkedObjectID is a ChildID
                                 $ChildID = $ArrayNurseryUpdates['LogEventLinkedObjectID'][$u];
                                 break;
                         }

                         $ArrayUpdatesDone[$sCurrentTitle][] = array('action' => $ArrayNurseryUpdates['LogEventAction'][$u],
                                                                     'pos' => $u, 'ChildID' => $ChildID,
                                                                     'NurseryRegistrationForDate' => $NurseryRegistrationForDate);
                     }
                 }
             }

             $EmailTemplate = $CONF_NURSERY_NOTIFICATIONS[$NotificationType][Template];

             // Generate the content of the mail
             // Now, we explain in the mail the changes for each child
             $ArrayFinalResult = array();

             foreach($ArrayUpdatesDone as $CurrChildDate => $ArrayInfos)
             {
                 // What is the last update for this child ?
                 $iNbUpdates = count($ArrayInfos);
                 $ChildID = NULL;
                 $ArrayNurseryRegistrations = array();
                 $sAmPmOtherTimeslot = '';
                 $NurseryRegistrationForDate = $ArrayInfos[0]['NurseryRegistrationForDate'];

                 // We search the concerned child
                 foreach($ArrayInfos as $i => $CurrentData)
                 {
                     if (!empty($CurrentData['ChildID']))
                     {
                         // We stop the search
                         $ChildID = $CurrentData['ChildID'];

                         // We search nursery registrations for the concerned date and the child
                         $ArrayNurseryRegistrations = getNurseryRegistrations($DbCon, $NurseryRegistrationForDate,
                                                                              $NurseryRegistrationForDate,
                                                                              'NurseryRegistrationForDate', $ChildID,
                                                                              PLANNING_BETWEEN_DATES);
                         break;
                     }
                 }

                 if ((isset($ArrayNurseryRegistrations['NurseryRegistrationID']))
                     && (!empty($ArrayNurseryRegistrations['NurseryRegistrationID'])))
                 {
                     // Only one nursery registration for a same day and same child : we format the timeslots
                     // AM and/or PM and/or other timeslots?
                     if (!empty($ArrayNurseryRegistrations['NurseryRegistrationForAM'][0]))
                     {
                         $sAmPmOtherTimeslot = $LANG_AM;
                     }

                     if (!empty($ArrayNurseryRegistrations['NurseryRegistrationForPM'][0]))
                     {
                         if (!empty($sAmPmOtherTimeslot))
                         {
                             $sAmPmOtherTimeslot .= " / ";
                         }

                         $sAmPmOtherTimeslot .= $LANG_PM;
                     }

                     if (!empty($ArrayNurseryRegistrations['NurseryRegistrationOtherTimeslots'][0]))
                     {
                         // Get other timeslots for the concerned school year
                         $ConcernedSchoolYear = getSchoolYear($ArrayNurseryRegistrations['NurseryRegistrationForDate'][0]);
                         if ((isset($CONF_NURSERY_OTHER_TIMESLOTS[$ConcernedSchoolYear]))
                             && (!empty($CONF_NURSERY_OTHER_TIMESLOTS[$ConcernedSchoolYear])))
                         {
                             // There are some other timeslots for the concerned school year
                             // We get which other timeslots are checked
                             $ArrayOTSPos = array_keys($CONF_NURSERY_OTHER_TIMESLOTS[$ConcernedSchoolYear]);
                             foreach($ArrayOTSPos as $ots => $CurrentOtherTimeslotID)
                             {
                                 if ($ArrayNurseryRegistrations['NurseryRegistrationOtherTimeslots'][0] & pow(2, $ots))
                                 {
                                     // We get the label of this other timeslot
                                     if (!empty($sAmPmOtherTimeslot))
                                     {
                                         $sAmPmOtherTimeslot .= " / ";
                                     }

                                     $sAmPmOtherTimeslot .= $CONF_NURSERY_OTHER_TIMESLOTS[$ConcernedSchoolYear][$CurrentOtherTimeslotID]['Label'];
                                 }
                             }
                         }
                     }
                 }

                 if (!isset($ArrayFinalResult[$NurseryRegistrationForDate]))
                 {
                     $ArrayFinalResult[$NurseryRegistrationForDate] = array(
                                                                            EVT_ACT_ADD => array(),
                                                                            EVT_ACT_UPDATE => array(),
                                                                            EVT_ACT_DELETE => array()
                                                                           );
                 }

                 switch($ArrayInfos[$iNbUpdates - 1]['action'])
                 {
                     case EVT_ACT_ADD:
                         // The child is registered to the nursery planning for this day
                         if (!empty($ChildID))
                         {
                             $iPos = strpos($ArrayNurseryUpdates['LogEventDescription'][$ArrayInfos[$iNbUpdates - 1]['pos']], ', ');
                             if ($iPos !== FALSE)
                             {
                                 // We remove the timeslot name of the description (date, timeslot) and we add
                                 // registered timeslots of the child for this day
                                 $Change = substr($ArrayNurseryUpdates['LogEventDescription'][$ArrayInfos[$iNbUpdates - 1]['pos']], 0, $iPos)
                                           ." : $sAmPmOtherTimeslot";
                                 $ArrayFinalResult[$NurseryRegistrationForDate][EVT_ACT_ADD][] = "$Change.";
                             }
                         }
                         break;

                     case EVT_ACT_UPDATE:
                         // There are updates for this nursery registration : we check the first action
                         switch($ArrayInfos[0]['action'])
                         {
                             case EVT_ACT_ADD:
                                 // The child is registered to the nursery planning for this day : we get registered
                                 // timeslots for this child
                                 if (!empty($ChildID))
                                 {
                                     $iPos = strpos($ArrayNurseryUpdates['LogEventDescription'][$ArrayInfos[0]['pos']], ', ');
                                     if ($iPos !== FALSE)
                                     {
                                         // We remove the timeslot name of the description (date, timeslot) and we add
                                         // registered timeslots of the child for this day
                                         $Change = substr($ArrayNurseryUpdates['LogEventDescription'][$ArrayInfos[0]['pos']], 0, $iPos)
                                                   ." : $sAmPmOtherTimeslot";
                                         $ArrayFinalResult[$NurseryRegistrationForDate][EVT_ACT_ADD][] = "$Change.";
                                     }
                                 }
                                 break;

                             default:
                                 // Other update action
                                 if (!empty($ChildID))
                                 {
                                     $iPos = strpos($ArrayNurseryUpdates['LogEventDescription'][$ArrayInfos[0]['pos']], ', ');
                                     if ($iPos !== FALSE)
                                     {
                                         // We remove the timeslot name of the description (date, timeslot) and we add
                                         // registered timeslots of the child for this day
                                         $Change = substr($ArrayNurseryUpdates['LogEventDescription'][$ArrayInfos[0]['pos']], 0, $iPos)
                                                   ." : $sAmPmOtherTimeslot";
                                         $ArrayFinalResult[$NurseryRegistrationForDate][EVT_ACT_UPDATE][] = "$Change.";
                                     }
                                 }
                                 break;
                         }
                         break;

                     case EVT_ACT_DELETE:
                         // The last action is to delete the last timeslot of the day for this child
                         $iPos = strpos($ArrayNurseryUpdates['LogEventDescription'][$ArrayInfos[$iNbUpdates - 1]['pos']], ', ');
                         if ($iPos !== FALSE)
                         {
                             // We remove the timeslot name of the description (date, timeslot)
                             $Change = substr($ArrayNurseryUpdates['LogEventDescription'][$ArrayInfos[$iNbUpdates - 1]['pos']], 0, $iPos);
                             $ArrayFinalResult[$NurseryRegistrationForDate][EVT_ACT_DELETE][] = "$Change.";
                         }
                         break;
                 }
             }

             // To have concerned days ordered by date
             ksort($ArrayFinalResult);

             foreach($ArrayFinalResult as $dDate => $ArrayDateUpdates)
             {
                 // Concerned day date bu updates of nursery registrations
                 $BodyContent .= "<h3 style=\"margin-top: 2em;\">**** ".date($CONF_DATE_DISPLAY_FORMAT, strtotime($dDate))." ****</h3>\n";

                 foreach($ArrayDateUpdates as $ActType => $ArrayActTypeUpdates)
                 {
                     if (!empty($ArrayFinalResult[$dDate][$ActType]))
                     {
                         // There are some actions done for this type of nursery registration action (add, update, delete)
                         $BodyContent .= "<dl>\n<dt style=\"font-style: italic; text-decoration: underline;\">";
                         switch($ActType)
                         {
                             case EVT_ACT_ADD:
                                 $BodyContent .= $LANG_NURSERY_PLANNING_UPDATES_EMAIL_ADD;
                                 break;

                             case EVT_ACT_UPDATE:
                                 $BodyContent .= $LANG_NURSERY_PLANNING_UPDATES_EMAIL_UPDATE;
                                 break;

                             case EVT_ACT_DELETE:
                                 $BodyContent .= $LANG_NURSERY_PLANNING_UPDATES_EMAIL_DELETE;
                                 break;
                         }

                         $BodyContent .= " : </dt>\n";

                         foreach($ArrayActTypeUpdates as $u => $CurrentUpdate)
                         {
                             $BodyContent .= "<dd>$CurrentUpdate</dd>\n";
                         }

                         $BodyContent .= "</dl>\n";
                     }
                 }
             }

             echo "<br />\n$BodyContent<br />\n";


             // We send an e-mail
             $EmailSubject = $CONF_EMAIL_OBJECTS_SUBJECT_PREFIX[FCT_NURSERY_PLANNING]."$LANG_NURSERY_PLANNING_UPDATES_EMAIL_SUBJECT "
                             .date($CONF_DATE_DISPLAY_FORMAT, strtotime($DateToTreat));

             $MailingList["to"] = $CONF_NURSERY_NOTIFICATIONS[$NotificationType][To];

             if ($CONF_MODE_DEBUG)
             {
                 $MailingList["to"] = array_merge(array($CONF_EMAIL_INTRANET_EMAIL_ADDRESS), $MailingList["to"]);
             }

             if ((isset($CONF_NURSERY_NOTIFICATIONS[$NotificationType][Cc])) && (!empty($CONF_NURSERY_NOTIFICATIONS[$NotificationType][Cc])))
             {
                 $MailingList["cc"] = $CONF_NURSERY_NOTIFICATIONS[$NotificationType][Cc];
             }

             if ((isset($CONF_NURSERY_NOTIFICATIONS[$NotificationType][Bcc])) && (!empty($CONF_NURSERY_NOTIFICATIONS[$NotificationType][Bcc])))
             {
                 $MailingList["bcc"] = $CONF_NURSERY_NOTIFICATIONS[$NotificationType][Bcc];
             }

             // We send the e-mail
             sendEmail(NULL, $MailingList, $EmailSubject, $EmailTemplate,
                       array(
                             array("{UpdateDate}", "{BodyContent}"),
                             array(date($CONF_DATE_DISPLAY_FORMAT, strtotime($DateToTreat)), $BodyContent)
                            ));
         }
     }
 }

 // We close the database connection
 dbDisconnection($DbCon);
?>