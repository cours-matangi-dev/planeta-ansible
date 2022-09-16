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
 * Support module : process the creation of a new forum message for a given topic.
 * The supporter must be logged.
 *
 * @author Christophe Javouhey
 * @version 3.6
 * @since 2021-04-17
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
 $bIsEmailSent = FALSE;
 $iNbJobsCreated = 0;

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

         // We ckeck if there is a reply to message ID
         $ForumReplyToMessageID = trim(strip_tags($_POST["hidForumReplyToMessageID"]));
         if (!isExistingForumMessage($DbCon, $ForumReplyToMessageID))
         {
             $ForumReplyToMessageID = NULL;
         }

         // We check if a picture is selected
         $MessagePicture = '';
         if (isset($_POST['radMessagePicture']))
         {
             $MessagePicture = trim(strip_tags($_POST['radMessagePicture']));
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
             $ForumMessageID = dbAddForumMessage($DbCon, $CurrentDateTime, $ForumTopicID, $SupportMemberID, addslashes($MessageContent),
                                                 $ForumReplyToMessageID, $MessagePicture);

             if ($ForumMessageID != 0)
             {
                 // Log event
                 logEvent($DbCon, EVT_FORUM, EVT_SERV_FORUM_MESSAGE, EVT_ACT_CREATE, $_SESSION['SupportMemberID'], $ForumMessageID);

                 // The forum message is created
                 $ConfirmationCaption = $LANG_CONFIRMATION;
                 $ConfirmationSentence = $LANG_CONFIRM_FORUM_TOPIC_MESSAGE_ADDED;
                 $ConfirmationStyle = "ConfirmationMsg";
                 $UrlParameters = "DisplayForumTopicMessages.php?Cr=".md5($ForumTopicID)."&Id=$ForumTopicID"; // For the redirection

                 // Check if a notification must be sent to supporters having a subscribtion to this topic
                 if ((isset($CONF_FORUM_NOTIFICATIONS['NewMessage'])) && (!empty($CONF_FORUM_NOTIFICATIONS['NewMessage'][Template])))
                 {
                     // Get infos about the topic
                     $RecordForumTopic = getTableRecordInfos($DbCon, 'ForumTopics', $ForumTopicID);
                     $ForumCategoryName = getForumCategoryName($DbCon, $RecordForumTopic['ForumCategoryID']);

                     $EmailSubject = $LANG_NEW_FORUM_MESSAGE_EMAIL_SUBJECT;

                     if (isset($CONF_EMAIL_OBJECTS_SUBJECT_PREFIX[FCT_FORUM]))
                     {
                         $EmailSubject = $CONF_EMAIL_OBJECTS_SUBJECT_PREFIX[FCT_FORUM].$EmailSubject;
                     }

                     // We compute the n° of the page where the new message is
                     $iMsgPosInTopic = getForumTopicMessagePosInTopic($DbCon, $ForumMessageID);
                     $iPageNum = ceil($iMsgPosInTopic / $CONF_RECORDS_PER_PAGE);

                     $ForumMessageUrl = $CONF_URL_SUPPORT."Forum/DisplayForumTopicMessages.php?Pg=$iPageNum&amp;Cr=".md5($ForumTopicID)."&amp;Id=$ForumTopicID";
                     $ForumMessageLink = stripslashes($RecordForumTopic['ForumTopicTitle']);
                     $ForumMessageLinkTip = $LANG_VIEW_DETAILS_INSTRUCTIONS;

                     // We define the content of the mail
                     $TemplateToUse = $CONF_FORUM_NOTIFICATIONS['NewMessage'][Template];
                     $ReplaceInTemplate = array(
                                                array(
                                                      "{LANG_FORUM_TOPIC}", "{ForumMessageUrl}", "{ForumMessageLink}", "{ForumMessageLinkTip}", "{LANG_FORUM_CATEGORY}",
                                                      "{ForumCategoryName}", "{LANG_CREATION_DATE}", "{ForumMessageDate}"
                                                     ),
                                                array(
                                                      $LANG_FORUM_TOPIC, $ForumMessageUrl, $ForumMessageLink, $ForumMessageLinkTip, $LANG_FORUM_CATEGORY,
                                                      $ForumCategoryName, $LANG_CREATION_DATE,
                                                      date($GLOBALS["CONF_DATE_DISPLAY_FORMAT"].' '.$GLOBALS["CONF_TIME_DISPLAY_FORMAT"], strtotime($CurrentDateTime))
                                                     )
                                               );

                     // Get the recipients of the e-mail notification
                     $MailingList["to"] = array();
                     $MailingList["bcc"] = array();

                     // We get recipients : supporters having a subscribtion to this topic
                     $ArrayTopicSubscribtions = getForumTopicsSubscribtions($DbCon, array('ForumTopic' => $ForumTopicID));
                     if ((isset($ArrayTopicSubscribtions[$ForumTopicID]))
                         && (isset($ArrayTopicSubscribtions[$ForumTopicID]['ForumTopicSubscribtionID']))
                         && (!empty($ArrayTopicSubscribtions[$ForumTopicID]['ForumTopicSubscribtionID'])))
                     {
                         foreach($ArrayTopicSubscribtions[$ForumTopicID]['ForumTopicSubscribtionID'] as $ts => $CurrentTopicSubID)
                         {
                             // The author of this message isn't notified
                             if ($ArrayTopicSubscribtions[$ForumTopicID]['SupportMemberID'][$ts] != $_SESSION['SupportMemberID'])
                             {
                                 $MailingList["bcc"][] = $ArrayTopicSubscribtions[$ForumTopicID]['ForumTopicSubscribtionEmail'][$ts];
                             }
                         }
                     }

                     if ((isset($CONF_FORUM_NOTIFICATIONS['NewMessage'][Cc])) && (!empty($CONF_FORUM_NOTIFICATIONS['NewMessage'][Cc])))
                     {
                         $MailingList["cc"] = $CONF_FORUM_NOTIFICATIONS['NewMessage'][Cc];
                     }

                     // DEBUG MODE
                     if ($GLOBALS["CONF_MODE_DEBUG"])
                     {
                         if (!in_array($CONF_EMAIL_INTRANET_EMAIL_ADDRESS, $MailingList["to"]))
                         {
                             // Without this test, there is a server mail error...
                             $MailingList["to"] = array_merge(array($CONF_EMAIL_INTRANET_EMAIL_ADDRESS), $MailingList["to"]);
                         }
                     }

                     // We send the e-mail : now or after ?
                     if ((isset($CONF_JOBS_TO_EXECUTE[JOB_EMAIL])) && (isset($CONF_JOBS_TO_EXECUTE[JOB_EMAIL][FCT_FORUM]))
                         && (count($CONF_JOBS_TO_EXECUTE[JOB_EMAIL][FCT_FORUM]) == 2))
                     {
                         // The message is delayed (job)
                         $bIsEmailSent = FALSE;

                         $ArrayBccRecipients = array_chunk($MailingList["bcc"], $CONF_JOBS_TO_EXECUTE[JOB_EMAIL][FCT_FORUM][JobSize]);
                         $PlannedDateStamp = strtotime("+1 min", strtotime("now"));

                         $ArrayJobParams = array(
                                                 array(
                                                       "JobParameterName" => "subject",
                                                       "JobParameterValue" => $EmailSubject
                                                      ),
                                                 array(
                                                       "JobParameterName" => "template-name",
                                                       "JobParameterValue" => $TemplateToUse
                                                      ),
                                                 array(
                                                       "JobParameterName" => "replace-in-template",
                                                       "JobParameterValue" => base64_encode(serialize($ReplaceInTemplate))
                                                      )
                                                );

                         $iNbJobsCreated = 0;
                         $CurrentMainlingList = array();
                         foreach($ArrayBccRecipients as $r => $CurrentRecipients)
                         {
                             if ($r == 0)
                             {
                                 // To and CC only for the first job
                                 if (isset($MailingList["to"]))
                                 {
                                     $CurrentMainlingList['to'] = $MailingList["to"];
                                 }

                                 if (isset($MailingList["cc"]))
                                 {
                                     $CurrentMainlingList['cc'] = $MailingList["cc"];
                                 }
                             }
                             elseif ($r == 1)
                             {
                                 // To delete To and CC
                                 unset($CurrentMainlingList);
                             }

                             // Define recipients
                             $CurrentMainlingList['bcc'] = $CurrentRecipients;

                             // Create the job to send a delayed e-mail
                             $JobID = dbAddJob($DbCon, $_SESSION['SupportMemberID'], JOB_EMAIL,
                                               date('Y-m-d H:i:s', $PlannedDateStamp), NULL, 0, NULL,
                                               array_merge($ArrayJobParams,
                                                           array(array("JobParameterName" => "mailinglist",
                                                                       "JobParameterValue" => base64_encode(serialize($CurrentMainlingList)))))
                                              );

                             if ($JobID > 0)
                             {
                                 $iNbJobsCreated++;

                                 // Compute date/time for the next job
                                 $PlannedDateStamp += $CONF_JOBS_TO_EXECUTE[JOB_EMAIL][FCT_FORUM][DelayBetween2Jobs] * 60;

                                 $bIsEmailSent = TRUE;
                             }
                         }

                         unset($ArrayBccRecipients, $ArrayJobParams);
                     }
                     else
                     {
                         // We can send the e-mail
                         $bIsEmailSent = sendEmail($_SESSION, $MailingList, $EmailSubject, $TemplateToUse, $ReplaceInTemplate);
                     }
                 }
             }
             else
             {
                 // The forum message can't be added
                 $ConfirmationCaption = $LANG_ERROR;
                 $ConfirmationSentence = $LANG_ERROR_ADD_FORUM_MESSAGE;
                 $ConfirmationStyle = "ErrorMsg";
                 $UrlParameters = 'CreateForumTopicMessage.php?'.$QUERY_STRING; // For the redirection
             }
         }
         else
         {
             // Errors
             $ConfirmationCaption = $LANG_ERROR;

             if (!$bForumTopicExists)
             {
                 // No forum topic or wrong form category
                 $ConfirmationSentence = $LANG_ERROR_WRONG_FORUM_TOPIC_ID;
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
             $UrlParameters = 'CreateForumTopicMessage.php?'.$QUERY_STRING; // For the redirection
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
         $UrlParameters = 'CreateForumTopicMessage.php?'.$QUERY_STRING; // For the redirection
     }
 }
 else
 {
     // The supporter doesn't come from the CreateForumTopicMessage.php page
     $ConfirmationCaption = $LANG_ERROR;
     $ConfirmationSentence = $LANG_ERROR_COME_FORM_PAGE;
     $ConfirmationStyle = "ErrorMsg";
     $UrlParameters = 'CreateForumTopicMessage.php?'.$QUERY_STRING; // For the redirection
 }

 if ($bIsEmailSent)
 {
     // A notification is sent
     $ConfirmationSentence .= '&nbsp;'.generateStyledPicture($CONF_NOTIFICATION_SENT_ICON);
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