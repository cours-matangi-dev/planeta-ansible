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
 * Support module : process the creation of a subscription to a given forum topic.
 * The supporter must be logged.
 *
 * @author Christophe Javouhey
 * @version 3.6
 * @since 2021-04-22
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

         // Get the author
         $SupportMemberID = trim(strip_tags($_POST["hidSupportMemberID"]));

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

         $Email = trim(strip_tags($_POST["sEmail"]));
         if ((empty($Email)) || (!isValideEmailAddress($Email)))
         {
             $ContinueProcess = FALSE;
         }

         // Verification that the parameters are correct
         if ($ContinueProcess)
         {
             $ForumTopicSubscribtionID = dbAddForumTopicSubscribtion($DbCon, $ForumTopicID, $SupportMemberID, $Email);

             if ($ForumTopicSubscribtionID != 0)
             {
                 // Log event
                 logEvent($DbCon, EVT_FORUM, EVT_SERV_FORUM_TOPIC_SUBSCRIBTION, EVT_ACT_ADD, $_SESSION['SupportMemberID'],
                          $ForumTopicSubscribtionID);

                 // The forum topic subscribtion is created
                 $ConfirmationCaption = $LANG_CONFIRMATION;
                 $ConfirmationSentence = $LANG_CONFIRM_FORUM_TOPIC_SUBSCRIBTION_ADDED;
                 $ConfirmationStyle = "ConfirmationMsg";
                 $UrlParameters = "DisplayForumTopicMessages.php?Cr=".md5($ForumTopicID)."&Id=$ForumTopicID"; // For the redirection
             }
             else
             {
                 // The forum topic subscribtion can't be added
                 $ConfirmationCaption = $LANG_ERROR;
                 $ConfirmationSentence = $LANG_ERROR_ADD_FORUM_SUBSCRIBTION;
                 $ConfirmationStyle = "ErrorMsg";
                 $UrlParameters = 'SubscribeForumTopic.php?'.$QUERY_STRING; // For the redirection
             }
         }
         else
         {
             // Errors
             $ConfirmationCaption = $LANG_ERROR;

             if (!$bForumTopicExists)
             {
                 // No forum topic
                 $ConfirmationSentence = $LANG_ERROR_WRONG_FORUM_TOPIC_ID;
             }
             if ((empty($Email)) || (!isValideEmailAddress($Email)))
             {
                 // The e-mail is empty or wrong
                 $ConfirmationSentence = $LANG_ERROR_SUBSCRIBE_FORUM_TOPIC_EMAIL;
             }
             else
             {
                 // ERROR : some parameters are empty strings
                 $ConfirmationSentence = $LANG_ERROR_WRONG_FIELDS;
             }

             $ConfirmationStyle = "ErrorMsg";
             $UrlParameters = 'SubscribeForumTopic.php?'.$QUERY_STRING; // For the redirection
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
         $UrlParameters = 'SubscribeForumTopic.php?'.$QUERY_STRING; // For the redirection
     }
 }
 else
 {
     // The supporter doesn't come from the SubscribeForumTopic.php page
     $ConfirmationCaption = $LANG_ERROR;
     $ConfirmationSentence = $LANG_ERROR_COME_FORM_PAGE;
     $ConfirmationStyle = "ErrorMsg";
     $UrlParameters = 'SubscribeForumTopic.php?'.$QUERY_STRING; // For the redirection
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