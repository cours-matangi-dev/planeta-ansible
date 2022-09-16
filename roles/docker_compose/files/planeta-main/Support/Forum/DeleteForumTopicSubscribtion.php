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
 * Support module : delete a forum topic subscribtion. The supporter must be logged to
 * delete the usubscribtion.
 *
 * @author Christophe Javouhey
 * @version 3.6
 * @since 2021-04-23
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

 // To take into account the crypted and no-crypted forum topic subscribtion ID
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

         // We get details about the topic subscribtion
         $bCanDelete = FALSE;
         $UrlParameters = "";

         $RecordTopicSubscribtion = getTableRecordInfos($DbCon, "ForumTopicsSubscribtions", $Id);

         if (isset($RecordTopicSubscribtion['ForumTopicSubscribtionID']))
         {
             $ForumTopicID = $RecordTopicSubscribtion['ForumTopicID'];

             // Get the default language of the forum topic
             if (isExistingForumTopic($DbCon, $ForumTopicID))
             {
                 $ForumCategoryDefaultLang = getForumTopicDefaultLang($DbCon, $ForumTopicID);
                 if ($CONF_LANG != $ForumCategoryDefaultLang)
                 {
                     $CONF_LANG = $ForumCategoryDefaultLang;
                     include '../../Languages/SetLanguage.php';
                 }
             }

             $bCanDelete = TRUE;
             $UrlParameters = $CONF_ROOT_DIRECTORY."Support/Forum/DisplayForumTopicMessages.php?Cr=".md5($ForumTopicID)
                              ."&Id=$ForumTopicID";
         }

         // We delete the selected forum topic subscribtion
         if ($bCanDelete)
         {
             // Yes, we can delete this topic subscribtion
             if (dbDeleteForumTopicSubscribtion($DbCon, $Id))
             {
                 // Log event
                 logEvent($DbCon, EVT_FORUM, EVT_SERV_FORUM_TOPIC_SUBSCRIBTION, EVT_ACT_DELETE, $_SESSION['SupportMemberID'], $Id,
                          array('ForumTopicSubscribtionDetails' => $RecordTopicSubscribtion));

                 // The topic subscribtion is deleted
                 $ConfirmationCaption = $LANG_CONFIRMATION;
                 $ConfirmationSentence = $LANG_CONFIRM_FORUM_TOPIC_SUBSCRIBTION_DELETED;
                 $ConfirmationStyle = "ConfirmationMsg";
             }
             else
             {
                 // ERROR : the topic subscribtion isn't deleted
                 $ConfirmationCaption = $LANG_ERROR;
                 $ConfirmationSentence = $LANG_ERROR_DELETE_FORUM_SUBSCRIBTION;
                 $ConfirmationStyle = "ErrorMsg";
             }
         }
         else
         {
             // ERROR : the topic subscribtion ID is wrong
             $ConfirmationCaption = $LANG_ERROR;
             $ConfirmationSentence = $LANG_ERROR_WRONG_FORUM_SUBSCRIBTION_ID;
             $ConfirmationStyle = "ErrorMsg";
         }

         // Release the connection to the database
         dbDisconnection($DbCon);
     }
     else
     {
         // ERROR : the topic subscribtion ID is wrong
         $ConfirmationCaption = $LANG_ERROR;
         $ConfirmationSentence = $LANG_ERROR_WRONG_FORUM_SUBSCRIBTION_ID;
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
     // Error because the ID of the topic subscribtion and the crypted ID don't match
     openFrame($LANG_ERROR);
     displayStyledText($LANG_ERROR_WRONG_FORUM_SUBSCRIBTION_ID, 'ErrorMsg');
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