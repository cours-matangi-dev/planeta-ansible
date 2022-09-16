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
 * Support module : process the update of an existing forum topic with the first message.
 * The supporter must be logged.
 *
 * @author Christophe Javouhey
 * @version 3.6
 * @since 2021-04-19
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

 //################################ FORM PROCESSING ##########################
 $UrlParameters = '';

 if (!empty($_POST["bSubmit"]))
 {
     if (isSet($_SESSION["SupportMemberID"]))
     {
         // Connection to the database
         $DbCon = dbConnection();

         // Load all configuration variables from database
         loadDbConfigParameters($DbCon, array());

         $ContinueProcess = TRUE; // used to check that the parameters are correct

         $CurrentDateTime = date('Y-m-d H:i:s');

         // Check if the forum topic exists
         $ForumTopicID = trim(strip_tags($_POST["hidForumTopicID"]));
         $bForumTopicExists = isExistingForumTopic($DbCon, $ForumTopicID);
         if ($bForumTopicExists)
         {
             // Get the default language of the forum topic
             $ForumCategoryDefaultLang = getForumTopicDefaultLang($DbCon, $ForumTopicID);
             if ($CONF_LANG != $ForumCategoryDefaultLang)
             {
                 $CONF_LANG = $ForumCategoryDefaultLang;
                 include '../../Languages/SetLanguage.php';
             }
         }
         else
         {
             // The forum topic doesn't exist
             $ContinueProcess = FALSE;
         }

         // We get the values entered by the user
         $TopicTitle = trim(strip_tags($_POST["sTopicTitle"]));
         if (empty($TopicTitle))
         {
             $ContinueProcess = FALSE;
         }

         // Get the author
         $SupportMemberID = trim(strip_tags($_POST["hidSupportMemberID"]));

         // Check if the forum category exists
         $ForumCategoryID = trim(strip_tags($_POST["hidForumCategoryID"]));
         $bForumCategoryExists = isExistingForumCategory($DbCon, $ForumCategoryID);
         if (!$bForumCategoryExists)
         {
             // The forum category doesn't exist
             $ContinueProcess = FALSE;
         }

         // We have to convert the expiration date in english format (format used in the database)
         $ExpirationDate = formatedDate2EngDate($_POST["expirationDate"]);
         if (!empty($ExpirationDate))
         {
             // The expiration date must be >= today
             if (strtotime($ExpirationDate) < strtotime($CurrentDateTime))
             {
                 // Error : wrong expiration date
                 $ContinueProcess = FALSE;
             }
         }

         // Get the selected icon of the topic
         $TopicIcon = strip_tags($_POST["radTopicIcon"]);

         // Get the rank of the topic if the field exists and is set and the supporter is allowed
         $TopicRank = NULL;
         if ((isset($_POST["sTopicRank"])) && (in_array($_SESSION['SupportMemberStateID'], $CONF_FORUM_ALLOWED_TO_USE_TOPIC_RANK)))
         {
             $TopicRank = nullFormatText(strip_tags($_POST["sTopicRank"]), "NULL");
             if (!is_Null($TopicRank))
             {
                 // The topic rank must be an integer > 0
                 if ((integer)$TopicRank <= 0)
                 {
                     $ContinueProcess = FALSE;
                 }
             }
         }

         // Get the selected topic status if the field exists and is set and the supporter is allowed
         $TopicStatus = NULL;
         if ((isset($_POST["lTopicStatus"])) && (in_array($_SESSION['SupportMemberStateID'], $CONF_FORUM_ALLOWED_TO_USE_TOPIC_STATUS)))
         {
             $TopicStatus = trim(strip_tags($_POST["lTopicStatus"]));
         }

         // We check if a picture is selected
         $MessagePicture = '';
         if (isset($_POST['radMessagePicture']))
         {
             $MessagePicture = trim(strip_tags($_POST['radMessagePicture']));
         }
         else
         {
             // No selected picture : we use the topic icon
             if (isset($CONF_FORUM_ICONS['TopicIcons'][$TopicIcon]))
             {
                 $MessagePicture = basename($CONF_FORUM_ICONS['TopicIcons'][$TopicIcon]);
             }
         }

         // We get the message content in HTML format
         $MessageContent = $_POST["hidTopicContent"];
         if (trim($MessageContent) == '')
         {
             // Error : no message
             $ContinueProcess = FALSE;
         }

         // Verification that the parameters are correct
         if ($ContinueProcess)
         {
             $ForumTopicID = dbUpdateForumTopic($DbCon, $ForumTopicID, addslashes($TopicTitle), NULL, $ForumCategoryID, NULL,$TopicStatus,
                                                $TopicIcon, $ExpirationDate, $TopicRank);

             if ($ForumTopicID != 0)
             {
                 // ge the ID of the first message of the topic
                 $ForumMessageID = getForumTopicMessageIDwithPosInTopic($DbCon, $ForumTopicID, 1);
                 $ForumMessageID = dbUpdateForumMessage($DbCon, $ForumMessageID, $ForumTopicID, NULL, addslashes($MessageContent),
                                                        $CurrentDateTime, $MessagePicture);

                 if ($ForumMessageID != 0)
                 {
                     // Log event
                     logEvent($DbCon, EVT_FORUM, EVT_SERV_FORUM_TOPIC, EVT_ACT_UPDATE, $_SESSION['SupportMemberID'], $ForumTopicID);
                     logEvent($DbCon, EVT_FORUM, EVT_SERV_FORUM_MESSAGE, EVT_ACT_UPDATE, $_SESSION['SupportMemberID'], $ForumMessageID);

                     // The forum topic is updated
                     $ConfirmationCaption = $LANG_CONFIRMATION;
                     $ConfirmationSentence = $LANG_CONFIRM_FORUM_TOPIC_UPDATED;
                     $ConfirmationStyle = "ConfirmationMsg";
                     $UrlParameters = "DisplayForumTopicMessages.php?Cr=".md5($ForumTopicID)."&Id=$ForumTopicID"; // For the redirection
                 }
                 else
                 {
                     // The forum message can't be updated
                     $ConfirmationCaption = $LANG_ERROR;
                     $ConfirmationSentence = $LANG_ERROR_UPDATE_FORUM_MESSAGE;
                     $ConfirmationStyle = "ErrorMsg";
                     $UrlParameters = 'UpdateForumTopic.php?'.$QUERY_STRING; // For the redirection
                 }
             }
             else
             {
                 // The forum topic can't be updated
                 $ConfirmationCaption = $LANG_ERROR;
                 $ConfirmationSentence = $LANG_ERROR_UPDATE_FORUM_TOPIC;
                 $ConfirmationStyle = "ErrorMsg";
                 $UrlParameters = 'UpdateForumTopic.php?'.$QUERY_STRING; // For the redirection
             }
         }
         else
         {
             // Errors
             $ConfirmationCaption = $LANG_ERROR;

             if (!$bForumTopicExists)
             {
                 // No forum topic or wrong form topic
                 $ConfirmationSentence = $LANG_ERROR_WRONG_FORUM_TOPIC_ID;
             }
             elseif (empty($TopicTitle))
             {
                 // The topic title is empty
                 $ConfirmationSentence = $LANG_ERROR_FORUM_TOPIC_TITLE;
             }
             elseif (!$bForumCategoryExists)
             {
                 // No forum category or wrong form category
                 $ConfirmationSentence = $LANG_ERROR_FORUM_CATEGORY;
             }

             elseif ((!empty($TopicRank)) && ((integer)$TopicRank < 1))
             {
                 // Wrong topic rank
                 $ConfirmationSentence = $LANG_ERROR_WRONG_FORUM_TOPIC_RANK;
             }
             elseif ((!empty($ExpirationDate)) && (strtotime($ExpirationDate) < strtotime($CurrentDateTime)))
             {
                 // Wrong topic expiration date
                 $ConfirmationSentence = $LANG_ERROR_WRONG_FORUM_TOPIC_EXPIRATION_DATE;
             }
             elseif (trim($MessageContent) == '')
             {
                 // The topic message content is empty
                 $ConfirmationSentence = $LANG_ERROR_FORUM_MESSAGE_CONTENT;
             }
             else
             {
                 // ERROR : some parameters are empty strings
                 $ConfirmationSentence = $LANG_ERROR_WRONG_FIELDS;
             }

             $ConfirmationStyle = "ErrorMsg";
             $UrlParameters = 'UpdateForumTopic.php?'.$QUERY_STRING; // For the redirection
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
         $UrlParameters = 'UpdateForumTopic.php?'.$QUERY_STRING; // For the redirection
     }
 }
 else
 {
     // The supporter doesn't come from the UpdateForumTopic.php page
     $ConfirmationCaption = $LANG_ERROR;
     $ConfirmationSentence = $LANG_ERROR_COME_FORM_PAGE;
     $ConfirmationStyle = "ErrorMsg";
     $UrlParameters = 'CreateForumTopic.php?'.$QUERY_STRING; // For the redirection
 }
 //################################ END FORM PROCESSING ##########################

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