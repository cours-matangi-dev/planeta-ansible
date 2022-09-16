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
 * Support module : process the upload of file for a forum message.
 * The supporter must be logged.
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

 // Connection to the database
 $DbCon = dbConnection();
 $ObjectID = trim(strip_tags($_POST["hidObjectID"]));

 // Get the default language of the forum topic
 if (isExistingForumMessage($DbCon, $ObjectID))
 {
     $ForumTopicID = getForumMessageTopicID($DbCon, $ObjectID);
     $ForumCategoryDefaultLang = getForumTopicDefaultLang($DbCon, $ForumTopicID);
     if ($CONF_LANG != $ForumCategoryDefaultLang)
     {
         $CONF_LANG = $ForumCategoryDefaultLang;
         include '../../Languages/SetLanguage.php';
     }
 }

 //################################ FORM PROCESSING ##########################
 $UrlParameters = '';

 if (!empty($_POST["bSubmit"]))
 {
     if (isSet($_SESSION["SupportMemberID"]))
     {
         // Load all configuration variables from database
         loadDbConfigParameters($DbCon, array());

         $ContinueProcess = TRUE; // used to check that the parameters are correct

         $CurrentDateTime = date('Y-m-d H:i:s');
         $ObjectType = trim(strip_tags($_POST["hidObjectType"]));

         // Check if the object exists
         switch($ObjectType)
         {
             case OBJ_FORUM_MESSAGE:
                 $ForumTopicID = getForumMessageTopicID($DbCon, $ObjectID);
                 $HDDDirectory = $CONF_UPLOAD_FORUM_MESSAGE_FILES_DIRECTORY_HDD;
                 $UrlParameters = "DisplayForumTopicMessages.php?Cr=".md5($ForumTopicID)."&Id=$ForumTopicID";

                 if (!isExistingForumMessage($DbCon, $ObjectID))
                 {
                     // Error : the forum message dosne't exist
                     $ContinueProcess = FALSE;
                 }
                 break;

             default:
                 // Error : the object type isn't taken into account
                 $HDDDirectory = '';
                 $UrlParameters = '';
                 $ContinueProcess = FALSE;
                 break;
         }

         // We upload the file
         $UploadedFile = "";
         if ($_FILES["fFilename"]["name"] != "")
         {
             // We give a valide name to the uploaded file
             $_FILES["fFilename"]["name"] = formatFilename($_FILES["fFilename"]["name"]);

             // Check if the file owns an allowed extension
             if (isFileOwnsAllowedExtension($_FILES["fFilename"]["name"], $CONF_UPLOAD_ALLOWED_EXTENSIONS))
             {
                 if (is_uploaded_file($_FILES["fFilename"]["tmp_name"]))
                 {
                     $UploadedFile = $_FILES["fFilename"]["name"];

                     if ($_FILES["fFilename"]["size"] > $CONF_UPLOAD_FORUM_MESSAGE_FILES_MAXSIZE)
                     {
                         // Error : file to big
                         $ContinueProcess = FALSE;
                     }
                 }
             }
             else
             {
                 // Error : file with a not allowed extension
                 $ContinueProcess = FALSE;
             }
         }

         $FileDescription = trim(strip_tags($_POST["sFileDescription"]));

         // Verification that the parameters are correct
         if ($ContinueProcess)
         {
             if (!empty($UploadedFile))
             {
                 // We move the uploaded file in the right directory
                 // We have one directory for each year : we check if this directory exists
                 $Year = date('Y', strtotime($CurrentDateTime));
                 $HDDDirectory .= "$Year/";
                 if (!file_exists($HDDDirectory))
                 {
                     // We create the directory
                     mkdir($HDDDirectory);
                 }

                 // Add the time in the filename
                 $iPosExt = strrpos($UploadedFile, '.');
                 if ($iPosExt === FALSE)
                 {
                     $UploadedFile .= '_'.time();
                 }
                 else
                 {
                     $UploadedFile = substr($UploadedFile, 0, $iPosExt).'_'.time().substr($UploadedFile, $iPosExt);
                 }

                 @move_uploaded_file($_FILES["fFilename"]["tmp_name"], $HDDDirectory.$UploadedFile);
             }

             // We can create the new uploaded file
             $UploadedFileID = dbAddUploadedFile($DbCon, $UploadedFile, date('Y-m-d H:i:s'), $ObjectType, $ObjectID, $FileDescription);

             if ($UploadedFileID != 0)
             {
                 // Log event
                 logEvent($DbCon, EVT_UPLOADED_FILE, EVT_SERV_UPLOADED_FILE, EVT_ACT_ADD, $_SESSION['SupportMemberID'], $UploadedFileID);

                 // The uploaded file is added
                 $ConfirmationCaption = $LANG_CONFIRMATION;
                 $ConfirmationSentence = $LANG_CONFIRM_FILE_ADDED;
                 $ConfirmationStyle = "ConfirmationMsg";
             }
             else
             {
                 // The uploaded file can't be added
                 $ConfirmationCaption = $LANG_ERROR;
                 $ConfirmationSentence = $LANG_ERROR_ADD_FILE;
                 $ConfirmationStyle = "ErrorMsg";
             }
         }
         else
         {
             // Errors
             $ConfirmationCaption = $LANG_ERROR;

             if (empty($DocumentApprovalFile))
             {
                 // The filename is empty
                 $ConfirmationSentence = $LANG_ERROR_FILENAME;
             }
             else
             {
                 // ERROR : some parameters are empty strings
                 $ConfirmationSentence = $LANG_ERROR_WRONG_FIELDS;
             }

             $ConfirmationStyle = "ErrorMsg";
         }
     }
     else
     {
         // ERROR : the supporter isn't logged
         $ConfirmationCaption = $LANG_ERROR;
         $ConfirmationSentence = $LANG_ERROR_NOT_LOGGED;
         $ConfirmationStyle = "ErrorMsg";
         $UrlParameters = 'UploadForumTopicMessageFile.php?'.$QUERY_STRING; // For the redirection
     }
 }
 else
 {
     // The supporter doesn't come from the UploadForumTopicMessageFile.php page
     $ConfirmationCaption = $LANG_ERROR;
     $ConfirmationSentence = $LANG_ERROR_COME_FORM_PAGE;
     $ConfirmationStyle = "ErrorMsg";
     $UrlParameters = 'UploadForumTopicMessageFile.php?'.$QUERY_STRING; // For the redirection
 }
 //################################ END FORM PROCESSING ##########################

 // Release the connection to the database
 dbDisconnection($DbCon);

 initGraphicInterface(
                      $LANG_INTRANET_NAME,
                      array(
                            '../../GUI/Styles/styles.css' => 'screen',
                            '../Styles_Support.css' => 'screen'
                           ),
                      array($CONF_ROOT_DIRECTORY."Common/JSRedirection/Redirection.js"),
                      '',
                      "Redirection('".$CONF_ROOT_DIRECTORY."Support/Forum/$UrlParameters', $CONF_TIME_LAG)"
                     );
 openWebPage();

 // Display the header of the application
 displayHeader($LANG_INTRANET_HEADER);

 // Display the main menu at the top of the web page
 displaySupportMainMenu(1);

 // Content of the web page
 openArea('id="content"');

 // Display the "Forum" and the "parameters" contextual menus if the supporter isn't logged, an empty contextual menu otherwise
 if (isSet($_SESSION["SupportMemberID"]))
 {
     // Open the contextual menu area
     openArea('id="contextualmenu"');

     displaySupportMemberContextualMenu("forum", 1, Forum_ForumsList);
     displaySupportMemberContextualMenu("parameters", 1, 0);

     // Display information about the logged user
     displayLoggedUser($_SESSION);

     // Close the <div> "contextualmenu"
     closeArea();

     openArea('id="page"');
 }

 // Display the informations, forms, etc. on the right of the web page
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

 if (isSet($_SESSION["SupportMemberID"]))
 {
     // Close the <div> "Page"
     closeArea();
 }

 // Close the <div> "content"
 closeArea();

 // Footer of the application
 displayFooter($LANG_INTRANET_FOOTER);

 // Close the web page
 closeWebPage();

 closeGraphicInterface();
?>