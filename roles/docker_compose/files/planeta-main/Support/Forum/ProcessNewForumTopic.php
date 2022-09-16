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
 * Support module : process the creation of a new forum topic with the first message.
 * The supporter must be logged.
 *
 * @author Christophe Javouhey
 * @version 3.7
 *     - 2021-11-29 : v3.7. Patch a bug about notified support member states ID
 *
 * @since 2021-04-15
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
         else
         {
             // Get the default language of the forum topic
             $ForumCategoryDefaultLang = getForumCategoryDefaultLang($DbCon, $ForumCategoryID);
             if ($CONF_LANG != $ForumCategoryDefaultLang)
             {
                 $CONF_LANG = $ForumCategoryDefaultLang;
                 include '../../Languages/SetLanguage.php';
             }
         }

         // We have to convert the expiration date in english format (format used in the database)
         $ExpirationDate = nullFormatText(formatedDate2EngDate($_POST["expirationDate"]), "NULL");
         if (!is_null($ExpirationDate))
         {
             // The expiration date must be >= today
             if (strtotime($ExpirationDate) < strtotime($CurrentDateTime))
             {
                 // Error : wrong expiration date
                 $ContinueProcess = FALSE;
             }
         }

         // Get the selected icon of the topic
         $TopicIcon = trim(strip_tags($_POST["radTopicIcon"]));

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
         $TopicStatus = FORUM_TOPIC_OPENED;
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

         // We check if after the creation of the topic, supporters must be notified by e-mail
         $NotifyUsers = existedPOSTFieldValue("chkNotify", NULL);
         if (is_null($NotifyUsers))
         {
             $NotifyUsers = FALSE;
         }
         else
         {
             $NotifyUsers = TRUE;
         }

         // Verification that the parameters are correct
         if ($ContinueProcess)
         {
             $ForumTopicID = dbAddForumTopic($DbCon, addslashes($TopicTitle), $CurrentDateTime, $ForumCategoryID, $SupportMemberID,
                                             $TopicStatus, $TopicIcon, $ExpirationDate, $TopicRank);

             if ($ForumTopicID != 0)
             {
                 $ForumMessageID = dbAddForumMessage($DbCon, $CurrentDateTime, $ForumTopicID, $SupportMemberID, addslashes($MessageContent), NULL,
                                                     $MessagePicture);

                 if ($ForumMessageID != 0)
                 {
                     // Log event
                     logEvent($DbCon, EVT_FORUM, EVT_SERV_FORUM_TOPIC, EVT_ACT_CREATE, $_SESSION['SupportMemberID'], $ForumTopicID);
                     logEvent($DbCon, EVT_FORUM, EVT_SERV_FORUM_MESSAGE, EVT_ACT_CREATE, $_SESSION['SupportMemberID'], $ForumMessageID);

                     // The forum topic is created
                     $ConfirmationCaption = $LANG_CONFIRMATION;
                     $ConfirmationSentence = $LANG_CONFIRM_FORUM_TOPIC_ADDED;
                     $ConfirmationStyle = "ConfirmationMsg";
                     $UrlParameters = "DisplayForumTopicMessages.php?Cr=".md5($ForumTopicID)."&Id=$ForumTopicID"; // For the redirection

                     // We check if we must notify the supporters there is a new topic
                     if (($NotifyUsers) && (isset($CONF_FORUM_NOTIFICATIONS['NewTopic']))
                         && (!empty($CONF_FORUM_NOTIFICATIONS['NewTopic'][Template])))
                     {
                         $ForumCategoryName = getForumCategoryName($DbCon, $ForumCategoryID);

                         $EmailSubject = $LANG_NEW_FORUM_TOPIC_EMAIL_SUBJECT;

                         if (isset($CONF_EMAIL_OBJECTS_SUBJECT_PREFIX[FCT_FORUM]))
                         {
                             $EmailSubject = $CONF_EMAIL_OBJECTS_SUBJECT_PREFIX[FCT_FORUM].$EmailSubject;
                         }

                         $ForumTopicUrl = $CONF_URL_SUPPORT."Forum/DisplayForumTopicMessages.php?Cr=".md5($ForumTopicID)."&amp;Id=$ForumTopicID";
                         $ForumTopicLink = $TopicTitle;
                         $ForumTopicLinkTip = $LANG_VIEW_DETAILS_INSTRUCTIONS;

                         // We define the content of the mail
                         $TemplateToUse = $CONF_FORUM_NOTIFICATIONS['NewTopic'][Template];
                         $ReplaceInTemplate = array(
                                                    array(
                                                          "{LANG_FORUM_TOPIC}", "{ForumTopicUrl}", "{ForumTopicLink}", "{ForumTopicLinkTip}", "{LANG_FORUM_CATEGORY}",
                                                          "{ForumCategoryName}", "{LANG_CREATION_DATE}", "{ForumTopicDate}"
                                                         ),
                                                    array(
                                                          $LANG_FORUM_TOPIC, $ForumTopicUrl, $ForumTopicLink, $ForumTopicLinkTip, $LANG_FORUM_CATEGORY,
                                                          $ForumCategoryName, $LANG_CREATION_DATE,
                                                          date($GLOBALS["CONF_DATE_DISPLAY_FORMAT"].' '.$GLOBALS["CONF_TIME_DISPLAY_FORMAT"], strtotime($CurrentDateTime))
                                                         )
                                                   );

                         // Get the recipients of the e-mail notification
                         $MailingList["to"] = array();
                         $MailingList["bcc"] = array();

                         $ArraySupportMemberStateID = array();
                         if ((isset($CONF_FORUM_NOTIFICATIONS['NewTopic'][OnlyFor])) && (!empty($CONF_FORUM_NOTIFICATIONS['NewTopic'][OnlyFor])))
                         {
                             // Only these support member states ID are notified
                             $ArraySupportMemberStateID = $CONF_FORUM_NOTIFICATIONS['NewTopic'][OnlyFor];
                         }

                         $ArrayTopicRecipients = dbGetForumTopicRecipients($DbCon, $ForumTopicID,
                                                                           array('SupportMemberStateID' => $ArraySupportMemberStateID));

                         if ((isset($ArrayTopicRecipients['SupportMemberID'])) && (!empty($ArrayTopicRecipients['SupportMemberID'])))
                         {
                             foreach($ArrayTopicRecipients['SupportMemberID'] as $tr => $CurrentSMID)
                             {
                                 foreach($ArrayTopicRecipients['Email'][$tr] as $e => $CurrentMail)
                                 {
                                     $MailingList["bcc"][] = $CurrentMail;
                                 }
                             }
                         }

                         $MailingList["bcc"] = array_unique($MailingList["bcc"]);

                         if ((isset($CONF_FORUM_NOTIFICATIONS['NewTopic'][Cc])) && (!empty($CONF_FORUM_NOTIFICATIONS['NewTopic'][Cc])))
                         {
                             $MailingList["cc"] = $CONF_FORUM_NOTIFICATIONS['NewTopic'][Cc];
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
                     // Error : we delete the topic
                     dbDeleteForumTopic($DbCon, $ForumTopicID);

                     // The forum message can't be added
                     $ConfirmationCaption = $LANG_ERROR;
                     $ConfirmationSentence = $LANG_ERROR_ADD_FORUM_MESSAGE;
                     $ConfirmationStyle = "ErrorMsg";
                     $UrlParameters = 'CreateForumTopic.php?'.$QUERY_STRING; // For the redirection
                 }
             }
             else
             {
                 // The forum topic can't be created
                 $ConfirmationCaption = $LANG_ERROR;
                 $ConfirmationSentence = $LANG_ERROR_ADD_FORUM_TOPIC;
                 $ConfirmationStyle = "ErrorMsg";
                 $UrlParameters = 'CreateForumTopic.php?'.$QUERY_STRING; // For the redirection
             }
         }
         else
         {
             // Errors
             $ConfirmationCaption = $LANG_ERROR;

             if (empty($TopicTitle))
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
             $UrlParameters = 'CreateForumTopic.php?'.$QUERY_STRING; // For the redirection
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
         $UrlParameters = 'CreateForumTopic.php?'.$QUERY_STRING; // For the redirection
     }
 }
 else
 {
     // The supporter doesn't come from the CreateForumTopic.php page
     $ConfirmationCaption = $LANG_ERROR;
     $ConfirmationSentence = $LANG_ERROR_COME_FORM_PAGE;
     $ConfirmationStyle = "ErrorMsg";
     $UrlParameters = 'CreateForumTopic.php?'.$QUERY_STRING; // For the redirection
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