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
 * Support module : delete too old data in relation with GDPR. The supporter must be logged to delete
 * the data. For some tables, data are computed and stored in the Stats table.
 *
 * @author Christophe Javouhey
 * @version 3.7
 *     - 2021-12-29 : correct WorkgroupRegistrations to WorkGroupRegistrations in the switch case
 *
 * @since 2021-05-05
 */

 // Include the graphic primitives library
 require '../../GUI/GraphicInterface.php';

 // To measure the execution script time
 initStartTime();

 // Create "supporter" session or use the opened "supporter" session
 session_start();

 // Redirect the user to the login page index.php if he isn't loggued
 setRedirectionToLoginPage();

 // To take into account the crypted and no-crypted concerned table name
 // Crypted ID
 if (!empty($_GET["Cr"]))
 {
     $CryptedID = (string)strip_tags($_GET["Cr"]);
 }
 else
 {
     $CryptedID = "";
 }

 // No-crypted ID
 if (!empty($_GET["Id"]))
 {
     $Id = (string)strip_tags($_GET["Id"]);
 }
 else
 {
     $Id = "";
 }

 //################################ FORM PROCESSING ##########################
 if ((isSet($_SESSION["SupportMemberID"])) && (isAdmin()))
 {
     // the ID and the md5 crypted ID must be equal
     if (($Id != '') && (md5($Id) == $CryptedID))
     {
         // Connection to the database
         $DbCon = dbConnection();

         // Load all configuration variables from database
         loadDbConfigParameters($DbCon, array('CONF_SCHOOL_YEAR_START_DATES'));

         $bTreatmentDone = FALSE;
         $TableName = $Id;
         $ArrayConcernedTables = array();
         if ((isset($CONF_GDPR_NB_YEARS_DATA_DELAY)) && (!empty($CONF_GDPR_NB_YEARS_DATA_DELAY)))
         {
             $ArrayConcernedTables = array_keys($CONF_GDPR_NB_YEARS_DATA_DELAY);
         }

         if (in_array($TableName, $ArrayConcernedTables))
         {
             $CurrentSchoolYear = getSchoolYear(date('Y-m-d'));

             if ((isset($CONF_GDPR_NB_YEARS_DATA_DELAY[$TableName]))
                 && ($CONF_GDPR_NB_YEARS_DATA_DELAY[$TableName] > 0))
             {
                 // We compute the date for which data must be deleted
                 $TooOldDate = getSchoolYearStartDate($CurrentSchoolYear - $CONF_GDPR_NB_YEARS_DATA_DELAY[$TableName]);
                 switch($TableName)
                 {
                     case 'CanteenRegistrations':
                         // Manage too old data about canteen registrations
                         // We do the GDPR treatment of the canteen registrations
                         $bTreatmentDone = dbCanteenRegistrationsGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                                               array(
                                                                                     'CanteenRegistrationForDate' => array('<', $TooOldDate)
                                                                                    ));
                         break;

                     case 'Children':
                         // Manage too old data about children of families
                         // We do the GDPR treatment of the children
                         $bTreatmentDone = dbChildrenGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                                   array(
                                                                         'FamilyDesactivationDate' => array('<', $TooOldDate)
                                                                        ));
                         break;

                     case 'DiscountsFamilies':
                         // Manage too old data about discounts of families
                         // We do the GDPR treatment of the discounts
                         $bTreatmentDone = dbDiscountsFamiliesGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                                            array(
                                                                                  'FamilyDesactivationDate' => array('<', $TooOldDate)
                                                                                 ));
                         break;

                     case 'DocumentsApprovals':
                         // Manage too old data about documents to approve
                         // We do the GDPR treatment of the documents to approve
                         $ArrayDocumentsToDelete = array();
                         $bTreatmentDone = dbDocumentsApprovalsGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                                             array(
                                                                                   'DocumentApprovalDate' => array('<', $TooOldDate)
                                                                                  ), $ArrayDocumentsToDelete);

                         if (($bTreatmentDone) && (!empty($ArrayDocumentsToDelete)))
                         {
                             // We must delete files onf the /Upload/ directory
                             foreach($ArrayDocumentsToDelete as $d => $CurrentDocFile)
                             {
                                 if (file_exists($CONF_UPLOAD_DOCUMENTS_FILES_DIRECTORY_HDD.$CurrentDocFile))
                                 {
                                     unlink($CONF_UPLOAD_DOCUMENTS_FILES_DIRECTORY_HDD.$CurrentDocFile);
                                 }
                             }
                         }

                         unset($ArrayDocumentsToDelete);
                         break;

                     case 'DocumentsFamiliesApprovals':
                         // Manage too old data about approvals of documents by families
                         // We do the GDPR treatment of the approvals od documents
                         $bTreatmentDone = dbDocumentsFamiliesApprovalsGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                                                     array(
                                                                                           'FamilyDesactivationDate' => array('<', $TooOldDate)
                                                                                          ));
                         break;

                     case 'Events':
                         // Manage too old data about events
                         // We do the GDPR treatment of the events
                         $bTreatmentDone = dbEventsGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                                 array(
                                                                       'EventStartDate' => array('<', $TooOldDate)
                                                                      ));
                         break;

                     case 'EventRegistrations':
                         // Manage too old data about event registrations
                         // We do the GDPR treatment of the event registrations
                         $bTreatmentDone = dbEventRegistrationsGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                                             array(
                                                                                   'EventStartDate' => array('<', $TooOldDate)
                                                                                  ));
                         break;

                     case 'EventSwappedRegistrations':
                         // Manage too old data about event swapped registrations
                         // We do the GDPR treatment of the event swapped registrations
                         $bTreatmentDone = dbEventSwappedRegistrationsGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                                                    array(
                                                                                          'EventStartDate' => array('<', $TooOldDate)
                                                                                         ));
                         break;

                     case 'ExitPermissions':
                         // Manage too old data about exit permissions of children
                         // We do the GDPR treatment of the exit permissions
                         $bTreatmentDone = dbExitPermissionsGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                                          array(
                                                                                'ExitPermissionDate' => array('<', $TooOldDate)
                                                                               ));
                         break;

                     case 'Families':
                         // Manage too old data about families
                         // We do the GDPR treatment of the families
                         $bTreatmentDone = dbFamiliesGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                                   array(
                                                                         'FamilyDesactivationDate' => array('<', $TooOldDate)
                                                                        ));
                         break;

                     case 'HistoFamilies':
                         // Manage too old data about history entries of the families
                         // We do the GDPR treatment of the history entries
                         $bTreatmentDone = dbHistoFamiliesGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                                        array(
                                                                              'FamilyDesactivationDate' => array('<', $TooOldDate)
                                                                             ));
                         break;

                     case 'HistoLevelsChildren':
                         // Manage too old data about children levels history
                         // We do the GDPR treatment of the children history
                         $bTreatmentDone = dbHistoLevelsChildrenGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                                              array(
                                                                                    'FamilyDesactivationDate' => array('<', $TooOldDate)
                                                                                   ));
                         break;

                     case 'LaundryRegistrations':
                         // Manage too old data about laundry registrations of families
                         // We do the GDPR treatment of the laundry registrations
                         $bTreatmentDone = dbLaundryRegistrationsGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                                               array(
                                                                                     'LaundryRegistrationDate' => array('<', $TooOldDate)
                                                                                    ));
                         break;

                     case 'LogEvents':
                         // Manage too old data about log events
                         // We do the GDPR treatment of the log events
                         $bTreatmentDone = dbLogEventsGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                                    array(
                                                                          'LogEventDate' => array('<', $TooOldDate)
                                                                         ));
                         break;

                     case 'MeetingRoomsRegistrations':
                         // Manage too old data about meeting rooms registrations
                         // We do the GDPR treatment of the meeting rooms registrations
                         $bTreatmentDone = dbMeetingRoomsRegistrationsGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                                                    array(
                                                                                          'MeetingRoomRegistrationStartDate' => array('<', $TooOldDate)
                                                                                         ));
                         break;

                     case 'MoreMeals':
                         // Manage too old data about more meals registrations
                         // We do the GDPR treatment of the canteen registrations
                         $bTreatmentDone = dbMoreMealsGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                                    array(
                                                                          'MoreMealForDate' => array('<', $TooOldDate)
                                                                         ));
                         break;

                     case 'NurseryRegistrations':
                         // Manage too old data about nursery registrations
                         // We do the GDPR treatment of the nursery registrations
                         $bTreatmentDone = dbNurseryRegistrationsGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                                               array(
                                                                                     'NurseryRegistrationForDate' => array('<', $TooOldDate)
                                                                                    ));
                         break;

                     case 'SnackRegistrations':
                         // Manage too old data about snack registrations of families
                         // We do the GDPR treatment of the snack registrations
                         $bTreatmentDone = dbSnackRegistrationsGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                                             array(
                                                                                   'SnackRegistrationDate' => array('<', $TooOldDate)
                                                                                  ));
                         break;

                     case 'Suspensions':
                         // Manage too old data about suspensions of children
                         // We do the GDPR treatment of the suspensions
                         $bTreatmentDone = dbSuspensionsGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                                      array(
                                                                            'FamilyDesactivationDate' => array('<', $TooOldDate)
                                                                           ));
                         break;

                     case 'WorkGroupRegistrations':
                         // Manage too old data about workgroup registrations
                         // We do the GDPR treatment of the workgroup registrations
                         $bTreatmentDone = dbWorkGroupRegistrationsGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                                                 array(
                                                                                       'WorkGroupRegistrationDate' => array('<', $TooOldDate)
                                                                                      ));
                         break;
                 }
             }

             if ($bTreatmentDone)
             {
                 // The too old data are deleted and stored in the Stats table
                 $ConfirmationCaption = $LANG_CONFIRMATION;
                 $ConfirmationSentence = $LANG_CONFIRM_RECORD_DELETED;
                 $ConfirmationStyle = "ConfirmationMsg";
             }
             else
             {
                 // ERROR : the too old data aren't deleted
                 $ConfirmationCaption = $LANG_ERROR;
                 $ConfirmationSentence = $LANG_ERROR_DELETE_RECORD;
                 $ConfirmationStyle = "ErrorMsg";
             }

             $UrlParameters = "GDPRManagement.php"; // For the redirection
         }
         else
         {
             // ERROR : the table name is wrong
             $ConfirmationCaption = $LANG_ERROR;
             $ConfirmationSentence = $LANG_ERROR_WRONG_RECORD_ID;
             $ConfirmationStyle = "ErrorMsg";
             $UrlParameters = "GDPRManagement.php"; // For the redirection
         }

         // Release the connection to the database
         dbDisconnection($DbCon);
     }
     else
     {
         // ERROR : the table name ID is wrong
         $ConfirmationCaption = $LANG_ERROR;
         $ConfirmationSentence = $LANG_ERROR_WRONG_RECORD_ID;
         $ConfirmationStyle = "ErrorMsg";
         $UrlParameters = "GDPRManagement.php"; // For the redirection
     }
 }
 else
 {
     // ERROR : the supporter isn't logged
     $ConfirmationCaption = $LANG_ERROR;
     $ConfirmationSentence = $LANG_ERROR_NOT_LOGGED;
     $ConfirmationStyle = "ErrorMsg";
     $UrlParameters = ""; // For the redirection
 }
 //################################ END FORM PROCESSING ##########################

 if ($UrlParameters == '')
 {
     // No redirection
     initGraphicInterface(
                          $LANG_INTRANET_NAME,
                          array(
                                '../../GUI/Styles/styles.css' => 'screen',
                                '../Styles_Support.css' => 'screen'
                               ),
                          array('../Verifications.js'),
                          'WhitePage'
                         );
 }
 else
 {
     // Redirection to the GDPR management âge
     initGraphicInterface(
                          $LANG_INTRANET_NAME,
                          array(
                                '../../GUI/Styles/styles.css' => 'screen',
                                '../Styles_Support.css' => 'screen'
                               ),
                          array($CONF_ROOT_DIRECTORY."Common/JSRedirection/Redirection.js"),
                          'WhitePage',
                          "Redirection('".$CONF_ROOT_DIRECTORY."Support/Admin/$UrlParameters', $CONF_TIME_LAG)"
                         );
 }

 // Content of the web page
 openArea('id="content"');

 // the ID and the md5 crypted ID must be equal
 if (($Id != '') && (md5($Id) == $CryptedID))
 {
     openFrame($ConfirmationCaption);
     displayStyledText($ConfirmationSentence, $ConfirmationStyle);
     closeFrame();
 }
 else
 {
     // Error because the ID of the table name and the crypted ID don't match
     openFrame($LANG_ERROR);
     displayStyledText($LANG_ERROR_WRONG_RECORD_ID, 'ErrorMsg');
     closeFrame();
 }

 // To measure the execution script time
 if ($CONF_DISPLAY_EXECUTION_TIME_SCRIPT)
 {
     openParagraph('InfoMsg');
     initEndTime();
     displayExecutionScriptTime('ExecutionTime');
     closeParagraph();
 }

 // Close the <div> "content"
 closeArea();

 closeGraphicInterface();
?>