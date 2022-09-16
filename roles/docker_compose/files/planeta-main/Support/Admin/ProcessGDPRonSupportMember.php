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
 * Support module : process GDPR treatment on support member data. The supporter must be logged.
 *
 * @author Christophe Javouhey
 * @version 3.6
 * @since 2021-05-01
 */

 // Include the graphic primitives library
  require '../../GUI/GraphicInterface.php';

 // To measure the execution script time
 initStartTime();

 // Create "supporter" session or use the opened "supporter" session
 session_start();

 // Redirect the user to the login page index.php if he isn't loggued
 setRedirectionToLoginPage();

 // To take into account the crypted and no-crypted support member ID
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
 if (!empty($_POST["bSubmit"]))
 {
     if ((isSet($_SESSION["SupportMemberID"])) && (isAdmin()))
     {
         // Connection to the database
         $DbCon = dbConnection();

         // Load all configuration variables from database
         loadDbConfigParameters($DbCon, array('CONF_SCHOOL_YEAR_START_DATES'));

         $ContinueProcess = TRUE; // used to check that the parameters are correct

         // We identify the support member
         $SupportMemberID = trim(strip_tags($_POST["hidSupportMemberID"]));
         if (!isExistingSupportMember($DbCon, $SupportMemberID))
         {
             // ERROR : the support member doesn't exist
             $ContinueProcess = FALSE;
             $SupportMemberID = 0;
         }

         $bTreatProfileData = 0;
         $bTreatEventsData = 0;
         $bTreatForum = 0;

         if (isset($_POST["lUserProfileGDPR"]))
         {
             $bTreatProfileData = trim(strip_tags($_POST["lUserProfileGDPR"]));
         }

         if (isset($_POST["lEventsGDPR"]))
         {
             $bTreatEventsData = trim(strip_tags($_POST["lEventsGDPR"]));
         }

         if (isset($_POST["lForumGDPR"]))
         {
             $bTreatForum = trim(strip_tags($_POST["lForumGDPR"]));
         }

         // Verification that the parameters are correct
         if ($ContinueProcess)
         {
             $bResult = FALSE;
             $CurrentSchoolYear = getSchoolYear(date('Y-m-d'));

             // We must treat user profile data
             if ($bTreatProfileData == 1)
             {
                 if ((isset($CONF_GDPR_NB_YEARS_DATA_DELAY['LogEvents'])) && ($CONF_GDPR_NB_YEARS_DATA_DELAY['LogEvents'] > 0))
                 {
                     $TooOldDate = getSchoolYearStartDate($CurrentSchoolYear - $CONF_GDPR_NB_YEARS_DATA_DELAY['LogEvents']);

                     if (dbLogEventsGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                  array(
                                                        'SupportMemberID' => $SupportMemberID,
                                                        'LogEventDate' => array('<', $TooOldDate))))
                     {
                         $bResult = TRUE;
                     }
                 }
             }

             // We must treat events data
             if ($bTreatEventsData == 1)
             {
                 if ((isset($CONF_GDPR_NB_YEARS_DATA_DELAY['EventSwappedRegistrations'])) && ($CONF_GDPR_NB_YEARS_DATA_DELAY['EventSwappedRegistrations'] > 0))
                 {
                     // We compute the date for which data must be deleted
                     $EventSwappedRegistrationsTooOldDate = getSchoolYearStartDate($CurrentSchoolYear - $CONF_GDPR_NB_YEARS_DATA_DELAY['EventSwappedRegistrations']);
                     dbEventSwappedRegistrationsGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                              array(
                                                                    'SupportMemberID' => $SupportMemberID,
                                                                    'EventStartDate' => array('<', $EventSwappedRegistrationsTooOldDate)
                                                                   ));
                 }

                 if ((isset($CONF_GDPR_NB_YEARS_DATA_DELAY['EventRegistrations'])) && ($CONF_GDPR_NB_YEARS_DATA_DELAY['EventRegistrations'] > 0))
                 {
                     $EventRegistrationsTooOldDate = getSchoolYearStartDate($CurrentSchoolYear - $CONF_GDPR_NB_YEARS_DATA_DELAY['EventRegistrations']);
                     dbEventRegistrationsGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                                       array(
                                                             'SupportMemberID' => $SupportMemberID,
                                                             'EventStartDate' => array('<', $EventRegistrationsTooOldDate)
                                                            ));
                 }

                 if ((isset($CONF_GDPR_NB_YEARS_DATA_DELAY['Events'])) && ($CONF_GDPR_NB_YEARS_DATA_DELAY['Events'] > 0))
                 {
                     $EventsTooOldDate = getSchoolYearStartDate($CurrentSchoolYear - $CONF_GDPR_NB_YEARS_DATA_DELAY['Events']);

                     if (dbEventsGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                               array(
                                                     'SupportMemberID' => $SupportMemberID,
                                                     'EventStartDate' => array('<', $EventsTooOldDate))))
                     {
                         $bResult = TRUE;
                     }
                 }
             }

             // We must treat forum data
             if ($bTreatForum == 1)
             {
                 if (dbForumGDPRTreatment($DbCon, $CONF_GDPR_ANONYMIZED_SUPPORT_MEMBER_ID,
                                          array('SupportMemberID' => $SupportMemberID,
                                                'ForumMessageContent' => $LANG_FORUM_MESSAGE_ERASED_MESSAGE)))
                 {
                     $bResult = TRUE;
                 }
             }

             if ($bResult)
             {
                 // The data are updated
                 $ConfirmationCaption = $LANG_CONFIRMATION;
                 $ConfirmationSentence = $LANG_CONFIRM_RECORD_UPDATED;
                 $ConfirmationStyle = "ConfirmationMsg";

             }
             else
             {
                 // The data can't be updated
                 $ConfirmationCaption = $LANG_ERROR;
                 $ConfirmationSentence = $LANG_ERROR_UPDATE_RECORD;
                 $ConfirmationStyle = "ErrorMsg";
             }

             $UrlParameters = "Cr=".md5($SupportMemberID)."&Id=$SupportMemberID"; // For the redirection
         }
         else
         {
             // ERROR : some parameters are empty strings
             $ConfirmationCaption = $LANG_ERROR;
             $ConfirmationStyle = "ErrorMsg";
             $UrlParameters = $QUERY_STRING; // For the redirection
         }

         // Release the connection to the database
         dbDisconnection($DbCon);
     }
     else
     {
         // ERROR : the supporter isn't logged
         $ConfirmationCaption = $LANG_ERROR;
         $ConfirmationSentence = $LANG_ERROR_NOT_LOGGED;
         $ConfirmationStyle = "ErrorMsg";
         $UrlParameters = $QUERY_STRING; // For the redirection
     }
 }
 else
 {
     // The supporter doesn't come from the UpdateSupportMember.php page
     $ConfirmationCaption = $LANG_ERROR;
     $ConfirmationSentence = $LANG_ERROR_COME_FORM_PAGE;
     $ConfirmationStyle = "ErrorMsg";
     $UrlParameters = $QUERY_STRING; // For the redirection
 }
 //################################ END FORM PROCESSING ##########################

 initGraphicInterface(
                      $LANG_INTRANET_NAME,
                      array(
                            '../../GUI/Styles/styles.css' => 'screen',
                            '../Styles_Support.css' => 'screen'
                           ),
                      array($CONF_ROOT_DIRECTORY."Common/JSRedirection/Redirection.js"),
                      'WhitePage',
                      "Redirection('".$CONF_ROOT_DIRECTORY."Support/Admin/UpdateSupportMember.php?$UrlParameters', $CONF_TIME_LAG)"
                     );

 // Content of the web page
 openArea('id="content"');

 openFrame($ConfirmationCaption);
 displayStyledText($ConfirmationSentence, $ConfirmationStyle);
 closeFrame();

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