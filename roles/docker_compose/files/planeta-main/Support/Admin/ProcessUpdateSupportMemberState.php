<?php
/* Copyright (C) 2012 Calandreta Del Pa?s Murethin
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
 * Support module : process the update of a support member state. The supporter must be logged.
 *
 * @author Christophe Javouhey
 * @version 3.7
 *     - 2022-01-31 : v3.7. Taken into account SupportMemberStateOptions field
 *
 * @since 2016-10-28
 */

 // Include the graphic primitives library
  require '../../GUI/GraphicInterface.php';

 // To measure the execution script time
 initStartTime();

 // Create "supporter" session or use the opened "supporter" session
 session_start();

 // Redirect the user to the login page index.php if he isn't loggued
 setRedirectionToLoginPage();

 // To take into account the crypted and no-crypted support member state ID
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
         loadDbConfigParameters($DbCon, array('CONF_SCHOOL_YEAR_START_DATES',
                                              'CONF_CLASSROOMS'));

         $ContinueProcess = TRUE; // used to check that the parameters are correct

         // We identify the support member state
         $iStateOptions = 0;
         if (isExistingSupportMemberState($DbCon, $Id))
         {
             // The support member state exists
             $SupportMemberStateID = $Id;
             $SupportMemberStateRecord = getTableRecordInfos($DbCon, "SupportMembersStates", $SupportMemberStateID);
             $iStateOptions = $SupportMemberStateRecord['SupportMemberStateOptions'];
         }
         else
         {
             // ERROR : the support member state doesn't exist
             $ContinueProcess = FALSE;
         }

         // We get the values entered by the user
         $StateName = trim(strip_tags($_POST["sStateName"]));
         if (empty($StateName))
         {
             $ContinueProcess = FALSE;
         }

         $Description = trim(strip_tags($_POST["sDescription"]));

         // We get checked options
         if ($CONF_SUPPORT_NB_SUPPORT_MEMBER_STATE_OPTIONS > 0)
         {
             for($opt = 0; $opt < $CONF_SUPPORT_NB_SUPPORT_MEMBER_STATE_OPTIONS; $opt++)
             {
                 $OptChecked = pow(2, $opt);
                 if (isset($_POST['chkSupportMemberStateOption_'.$opt]))
                 {
                     // Option checked
                     if (($iStateOptions & $OptChecked) == 0)
                     {
                         // This option isn't already recorded in DB : we save it
                         $iStateOptions += $OptChecked;
                     }
                 }
                 else
                 {
                     // Option not checked
                     if (($iStateOptions & $OptChecked) > 0)
                     {
                         // This option is recorded in DB : we remove it
                         $iStateOptions -= $OptChecked;
                     }
                 }
             }
         }

         // Verification that the parameters are correct
         if ($ContinueProcess)
         {
             $SupportMemberStateID = dbUpdateSupportMemberState($DbCon, $SupportMemberStateID, $StateName, $Description, $iStateOptions);
             if ($SupportMemberStateID != 0)
             {
                 // The support member state is updated
                 $ConfirmationCaption = $LANG_CONFIRMATION;
                 $ConfirmationSentence = $LANG_CONFIRM_RECORD_UPDATED;
                 $ConfirmationStyle = "ConfirmationMsg";
                 $UrlParameters = "Cr=".md5($SupportMemberStateID)."&Id=$SupportMemberStateID"; // For the redirection
             }
             else
             {
                 // The support member state can't be updated
                 $ConfirmationCaption = $LANG_ERROR;
                 $ConfirmationSentence = $LANG_ERROR_UPDATE_RECORD;
                 $ConfirmationStyle = "ErrorMsg";
                 $UrlParameters = $QUERY_STRING; // For the redirection
             }
         }
         else
         {
             // Errors
             $ConfirmationCaption = $LANG_ERROR;

             if (empty($StateName))
             {
                 // No state name
                 $ConfirmationSentence = $LANG_ERROR_MANDORY_FIELDS;
             }
             else
             {
                 // ERROR : some parameters are empty strings
                 $ConfirmationSentence = $LANG_ERROR_WRONG_FIELDS;
             }

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
     // The supporter doesn't come from the UpdateSupportMemberState.php page
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
                      "Redirection('".$CONF_ROOT_DIRECTORY."Support/Admin/UpdateSupportMemberState.php?$UrlParameters', $CONF_TIME_LAG)"
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