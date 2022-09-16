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
 * Support module : delete a picture of a family. The supporter must be logged to
 * delete the picture.
 *
 * @author Christophe Javouhey
 * @version 3.6
 * @since 2021-05-18
 */

 // Include the graphic primitives library
 require '../../GUI/GraphicInterface.php';

 // To measure the execution script time
 initStartTime();

 // Create "supporter" session or use the opened "supporter" session
 session_start();

 // Redirect the user to the login page index.php if he isn't loggued
 setRedirectionToLoginPage();

 // To take into account the crypted and no-crypted Family ID
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

 // Crypted picture ID
 if (!empty($_GET["PCr"]))
 {
     $PicCryptedID = (string)strip_tags($_GET["PCr"]);
 }
 else
 {
     $PicCryptedID = "";
 }

 // No-crypted picture ID
 if (!empty($_GET["PId"]))
 {
     $PicId = (string)strip_tags($_GET["PId"]);
 }
 else
 {
     $PicId = "";
 }

 //################################ FORM PROCESSING ##########################
 $cUserAccess = FCT_ACT_NO_RIGHTS;
 $AccessRules = $CONF_ACCESS_APPL_PAGES[FCT_FAMILY];

 if ((isset($AccessRules[FCT_ACT_UPDATE])) && (in_array($_SESSION["SupportMemberStateID"], $AccessRules[FCT_ACT_UPDATE])))
 {
     // Update mode
     $cUserAccess = FCT_ACT_UPDATE;
 }
 elseif ((isset($AccessRules[FCT_ACT_READ_ONLY])) && (in_array($_SESSION["SupportMemberStateID"], $AccessRules[FCT_ACT_READ_ONLY])))
 {
     // Read mode
     $cUserAccess = FCT_ACT_READ_ONLY;
 }
 elseif ((isset($AccessRules[FCT_ACT_PARTIAL_READ_ONLY])) && (in_array($_SESSION["SupportMemberStateID"], $AccessRules[FCT_ACT_PARTIAL_READ_ONLY])))
 {
     // Partial read mode
     $cUserAccess = FCT_ACT_PARTIAL_READ_ONLY;
 }
 elseif ((isset($AccessRules[FCT_ACT_UPDATE_OLD_USER])) && (in_array($_SESSION["SupportMemberStateID"], $AccessRules[FCT_ACT_UPDATE_OLD_USER])))
 {
     // Update old user mode (for old families)
     $cUserAccess = FCT_ACT_UPDATE_OLD_USER;
 }

 if (isSet($_SESSION["SupportMemberID"]))
 {
     // The ID and the md5 crypted ID must be equal
     if (($Id != '') && (md5($Id) == $CryptedID))
     {
         // Connection to the database
         $DbCon = dbConnection();

         // Load all configuration variables from database
         loadDbConfigParameters($DbCon, array());

         // We get infos about the family
         $RecordFamily = getTableRecordInfos($DbCon, "Families", $Id);
         $FamilyID = 0;
         if (!empty($RecordFamily))
         {
             $FamilyID = $RecordFamily["FamilyID"];
         }

         // We delete the selected picture
         if (($FamilyID > 0) && ($PicId != '') && (md5($PicId) == $PicCryptedID))
         {
             // We check if we must delete the main picture or the second picture
             $MainPicture = NULL;
             $SecondPicture = NULL;

             if ($PicId == '1')
             {
                 // Main picture
                 $MainPicture = '';

                 if ((!empty($RecordFamily['FamilyMainPicture']))
                     && (file_exists($CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY_HDD.$RecordFamily['FamilyMainPicture'])))
                 {
                     // We delete the picture file
                     @unlink($CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY_HDD.$RecordFamily['FamilyMainPicture']);
                 }
             }
             else
             {
                 // Second picture
                 $SecondPicture = '';

                 if ((!empty($RecordFamily['FamilySecondPicture']))
                     && (file_exists($CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY_HDD.$RecordFamily['FamilySecondPicture'])))
                 {
                     // We delete the picture file
                     @unlink($CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY_HDD.$RecordFamily['FamilySecondPicture']);
                 }
             }

             $FamilyID = dbUpdateFamily($DbCon, $FamilyID, NULL, $RecordFamily['FamilyLastname'], NULL, NULL, NULL, NULL,
                                        NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,
                                        $MainPicture, $SecondPicture);

             $UrlParameters = "Cr=".md5($FamilyID)."&Id=$FamilyID"; // For the redirection

             if ($FamilyID != 0)
             {
                 // Log event
                 logEvent($DbCon, EVT_FAMILY, EVT_SERV_FAMILY, EVT_ACT_UPDATE, $_SESSION['SupportMemberID'], $FamilyID);

                 // The picture is deleted
                 $ConfirmationCaption = $LANG_CONFIRMATION;
                 $ConfirmationSentence = $LANG_CONFIRM_FILE_DELETED;
                 $ConfirmationStyle = "ConfirmationMsg";
             }
             else
             {
                 // ERROR : the picture isn't deleted
                 $ConfirmationCaption = $LANG_ERROR;
                 $ConfirmationSentence = $LANG_ERROR_DELETE_FILE;
                 $ConfirmationStyle = "ErrorMsg";
             }
         }
         else
         {
             // ERROR : the family ID is wrong
             $ConfirmationCaption = $LANG_ERROR;
             $ConfirmationSentence = $LANG_ERROR_WRONG_FAMILY_ID;
             $ConfirmationStyle = "ErrorMsg";
             $UrlParameters = ""; // For the redirection
         }

         // Release the connection to the database
         dbDisconnection($DbCon);
     }
     else
     {
         // ERROR : the family ID is wrong
         $ConfirmationCaption = $LANG_ERROR;
         $ConfirmationSentence = $LANG_ERROR_WRONG_FAMILY_ID;
         $ConfirmationStyle = "ErrorMsg";
         $UrlParameters = ""; // For the redirection
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
 $RedirectUrl = "UpdateFamily.php";
 if (in_array($cUserAccess, array(FCT_ACT_PARTIAL_READ_ONLY)))
 {
     $RedirectUrl = "FamilyDetails.php";
 }

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
     // Redirection to the details of the family
     initGraphicInterface(
                          $LANG_INTRANET_NAME,
                          array(
                                '../../GUI/Styles/styles.css' => 'screen',
                                '../Styles_Support.css' => 'screen'
                               ),
                          array($CONF_ROOT_DIRECTORY."Common/JSRedirection/Redirection.js"),
                          'WhitePage',
                          "Redirection('".$CONF_ROOT_DIRECTORY."Support/Canteen/$RedirectUrl?$UrlParameters', $CONF_TIME_LAG)"
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
     // Error because the ID of the family ID and the crypted ID don't match
     openFrame($LANG_ERROR);
     displayStyledText($LANG_ERROR_WRONG_FAMILY_ID, 'ErrorMsg');
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