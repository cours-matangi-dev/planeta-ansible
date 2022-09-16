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
 * Support module : delete an uploaded file linked to an object. The supporter must be logged to
 * delete the uploaded file.
 *
 * @author Christophe Javouhey
 * @version 3.6
 * @since 2021-04-20
 */

 // Include the graphic primitives library
 require '../../GUI/GraphicInterface.php';

 // To measure the execution script time
 initStartTime();

 // Create "supporter" session or use the opened "supporter" session
 session_start();

 // Redirect the user to the login page index.php if he isn't loggued
 setRedirectionToLoginPage();

 // We check if the default language of the loggued supporter is different form the default application language
 if ((isset($CONF_LANG_SUPPORT_MEMBER_STATES[$_SESSION['SupportMemberStateID']]))
     && ($CONF_LANG != $CONF_LANG_SUPPORT_MEMBER_STATES[$_SESSION['SupportMemberStateID']]))
 {
     $CONF_LANG = $CONF_LANG_SUPPORT_MEMBER_STATES[$_SESSION['SupportMemberStateID']];
     include '../../Languages/SetLanguage.php';
 }

 // To take into account the crypted and no-crypted uploaded file ID
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
 if (isSet($_SESSION["SupportMemberID"]))
 {
     // the ID and the md5 crypted ID must be equal
     if (($Id != '') && (md5($Id) == $CryptedID))
     {
         // Connection to the database
         $DbCon = dbConnection();

         // Load all configuration variables from database
         loadDbConfigParameters($DbCon, array());

         // We get details about the uploaded file
         $bCanDelete = FALSE;
         $UrlParameters = "";

         $RecordUploadedFile = getTableRecordInfos($DbCon, "UploadedFiles", $Id);

         $ForumMessageID = $RecordUploadedFile['ObjectID'];

         // Get the default language of the forum topic
         if (isExistingForumMessage($DbCon, $ForumMessageID))
         {
             $ForumTopicID = getForumMessageTopicID($DbCon, $ForumMessageID);
             $ForumCategoryDefaultLang = getForumTopicDefaultLang($DbCon, $ForumTopicID);
             if ($CONF_LANG != $ForumCategoryDefaultLang)
             {
                 $CONF_LANG = $ForumCategoryDefaultLang;
                 include '../../Languages/SetLanguage.php';
             }
         }

         if (isset($RecordUploadedFile['UploadedFileID']))
         {
             switch($RecordUploadedFile['UploadedFileObjectType'])
             {
                 case OBJ_FORUM_MESSAGE:
                     $bCanDelete = TRUE;
                     $HDDDirectory = $CONF_UPLOAD_FORUM_MESSAGE_FILES_DIRECTORY_HDD;
                     $UrlParameters = $CONF_ROOT_DIRECTORY."Support/Forum/DisplayForumTopicMessages.php?Cr=".md5($ForumTopicID)
                                      ."&Id=$ForumTopicID";
                     break;

                 default:
                     $UrlParameters = "";
                     break;
             }
         }

         // We delete the selected uploaded file
         if ($bCanDelete)
         {
             // Yes, we can delete this uploaded file
             if (dbDeleteUploadedFile($DbCon, $Id))
             {
                 // One directory for each year !
                 $Year = date('Y', strtotime($RecordUploadedFile['UploadedFileDate']));
                 $HDDDirectory .= "$Year/";

                 unlink($HDDDirectory.$RecordUploadedFile['UploadedFileName']);

                 // Log event
                 logEvent($DbCon, EVT_UPLOADED_FILE, EVT_SERV_UPLOADED_FILE, EVT_ACT_DELETE, $_SESSION['SupportMemberID'], $Id,
                          array('UploadedFileDetails' => $RecordUploadedFile));

                 // The uploaded file is deleted
                 $ConfirmationCaption = $LANG_CONFIRMATION;
                 $ConfirmationSentence = $LANG_CONFIRM_FILE_DELETED;
                 $ConfirmationStyle = "ConfirmationMsg";
             }
             else
             {
                 // ERROR : the uploaded file isn't deleted
                 $ConfirmationCaption = $LANG_ERROR;
                 $ConfirmationSentence = $LANG_ERROR_DELETE_FILE;
                 $ConfirmationStyle = "ErrorMsg";
             }
         }
         else
         {
             // Error : the user isn't allowed to delete the uploaded file
             $ConfirmationCaption = $LANG_ERROR;
             $ConfirmationSentence = $LANG_ERROR_NOT_ALLOWED_TO_DELETE_FILE;
             $ConfirmationStyle = "ErrorMsg";
         }

         // Release the connection to the database
         dbDisconnection($DbCon);
     }
     else
     {
         // ERROR : the uploaded file ID is wrong
         $ConfirmationCaption = $LANG_ERROR;
         $ConfirmationSentence = $LANG_ERROR_WRONG_FILE_ID;
         $ConfirmationStyle = "ErrorMsg";
     }
 }
 else
 {
     // ERROR : the supporter isn't logged
     $ConfirmationCaption = $LANG_ERROR;
     $ConfirmationSentence = $LANG_ERROR_NOT_LOGGED;
     $ConfirmationStyle = "ErrorMsg";
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
     // Redirection to the details of the event
     initGraphicInterface(
                          $LANG_INTRANET_NAME,
                          array(
                                '../../GUI/Styles/styles.css' => 'screen',
                                '../Styles_Support.css' => 'screen'
                               ),
                          array($CONF_ROOT_DIRECTORY."Common/JSRedirection/Redirection.js"),
                          'WhitePage',
                          "Redirection('$UrlParameters', $CONF_TIME_LAG)"
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
     // Error because the ID of the uploaded file and the crypted ID don't match
     openFrame($LANG_ERROR);
     displayStyledText($LANG_ERROR_WRONG_FILE_ID, 'ErrorMsg');
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