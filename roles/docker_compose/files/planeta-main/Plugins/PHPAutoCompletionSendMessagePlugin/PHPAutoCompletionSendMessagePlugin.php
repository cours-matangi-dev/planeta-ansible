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
 * PHP plugin autocompletion module : add the autocompletion function for the recipients and in copy
 * fields of the "send message" form
 *
 * @author Christophe Javouhey
 * @version 4.0
 *     - 2016-06-20 : v2.0. Taken into account $CONF_CHARSET
 *     - 2016-11-02 : v3.0. Load some configuration variables from database
 *     - 2021-05-19 : v4.0. Get pictures of families
 *
 * @since 2016-03-02
 */


 // Include Config.php because of the name of the session
 require '../../GUI/GraphicInterface.php';

 session_start();

 // Connection to the database
 $DbCon = dbConnection();

 // Load all configuration variables from database
 loadDbConfigParameters($DbCon, array('CONF_SCHOOL_YEAR_START_DATES',
                                      'CONF_CLASSROOMS'));

 $sHTML = '';

 if (isset($_POST['debut']))
 {
     // Autocompletion : get the value
     $sEnteredValue = $_POST['debut'];

     // We detect the charset : it must be ISO-8859-1
     if (mb_detect_encoding($sEnteredValue, 'UTF-8') == 'UTF-8')
     {
         $sEnteredValue = utf8_decode($sEnteredValue);
     }

     $sEnteredValue = strip_tags(strtolower(trim($sEnteredValue)));
     $sEnteredValue = "%$sEnteredValue%";


     // Get the name of the form field in which we have entered the value
     $sFormFieldName = '';
     if (isset($_POST['FormFieldName']))
     {
         $sFormFieldName = strip_tags(trim($_POST['FormFieldName']));
     }

     $ArrayFoundValues = array();
     if (!empty($sFormFieldName))
     {
         $ArrayParams = array(
                              "SchoolYear" => array(getSchoolYear(date('Y-m-d'))),
                              "SupportMemberActivated" => array(1),
                              "Name" => $sEnteredValue
                             );

         $ArrayRecipients = dbSearchMessageRecipients($DbCon, $ArrayParams, "rName", 1, 0);

         if (isset($ArrayRecipients['rName']))
         {
             foreach($ArrayRecipients['rName'] as $n => $rName)
             {
                 $sNameToDisplay = $rName." (".$ArrayRecipients['rStateName'][$n].")";
                 if (!in_array($sNameToDisplay, array_values($ArrayFoundValues)))
                 {
                     $ArrayFoundValues[$ArrayRecipients['rID'][$n]] = $sNameToDisplay;
                 }
             }
         }

         $sHTML = '<ul class="FormFieldAutoCompletionList">';

         foreach($ArrayFoundValues as $id => $CurrentName)
         {
             $sHTML .= "<li id=\"$id\">$CurrentName</li>";
         }

         $sHTML .= '</ul>';
     }
 }
 elseif (isset($_GET['getPictures']))
 {
     // Get pictures of the family
     $sID = trim(strip_tags($_GET['getPictures']));
     switch(substr($sID, 0, 1))
     {
         case 'F':
             // It's a family
             $FamilyID = substr($sID, 1);
             $FamilyRecord = getTableRecordInfos($DbCon, "Families", $FamilyID);

             if ((!empty($FamilyRecord['FamilyMainPicture']))
                 && (file_exists($CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY_HDD.$FamilyRecord['FamilyMainPicture'])))
             {
                 // The main picture is set
                 $sHTML = "<img id=\"Pic$sID-1\" class=\"SendMsgLittlePicture\" src=\"".$CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY.$FamilyRecord['FamilyMainPicture']
                          ."\" title=\"".$FamilyRecord['FamilyLastname'].".\" />";
             }

             if ((!empty($FamilyRecord['FamilySecondPicture']))
                 && (file_exists($CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY_HDD.$FamilyRecord['FamilySecondPicture'])))
             {
                 // The second picture is set
                 $sHTML .= "<img id=\"Pic$sID-2\" class=\"SendMsgLittlePicture\"  src=\"".$CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY.$FamilyRecord['FamilySecondPicture']
                           ."\" title=\"".$FamilyRecord['FamilyLastname'].".\" />";
             }
             break;

         case 'R':
             // It's a workgroup registration
             // We check if he is linked to a family and we get his picture thanks to its e-mail address
             $WorkgroupRegistrationID = substr($sID, 1);
             $WorkgroupRegistrationRecord = getTableRecordInfos($DbCon, "WorkGroupRegistrations", $WorkgroupRegistrationID);
             if ((isset($WorkgroupRegistrationRecord['WorkGroupRegistrationID'])) && (!empty($WorkgroupRegistrationRecord['FamilyID'])))
             {
                 $FamilyID = $WorkgroupRegistrationRecord['FamilyID'];
                 $FamilyRecord = getTableRecordInfos($DbCon, "Families", $FamilyID);

                 $SupporterName = $WorkgroupRegistrationRecord['WorkGroupRegistrationLastname'].' '.$WorkgroupRegistrationRecord['WorkGroupRegistrationFirstname'];

                 if (strToLower($WorkgroupRegistrationRecord['WorkGroupRegistrationEmail']) == strToLower($FamilyRecord['FamilyMainEmail']))
                 {
                     if ((!empty($FamilyRecord['FamilyMainPicture']))
                         && (file_exists($CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY_HDD.$FamilyRecord['FamilyMainPicture'])))
                     {
                         // The main picture is set
                         $sHTML = "<img id=\"Pic$sID-1\" class=\"SendMsgLittlePicture\" src=\"".$CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY.$FamilyRecord['FamilyMainPicture']
                                  ."\" title=\"$SupporterName.\" />";
                     }
                 }
                 elseif (strToLower($WorkgroupRegistrationRecord['WorkGroupRegistrationEmail']) == strToLower($FamilyRecord['FamilySecondEmail']))
                 {
                     if ((!empty($FamilyRecord['FamilySecondPicture']))
                         && (file_exists($CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY_HDD.$FamilyRecord['FamilySecondPicture'])))
                     {
                         // The second picture is set
                         $sHTML = "<img id=\"Pic$sID-2\" class=\"SendMsgLittlePicture\" src=\"".$CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY.$FamilyRecord['FamilySecondPicture']
                                  ."\" title=\"$SupporterName.\" />";
                     }
                 }
             }
             break;

         case 'S':
             // It's a supporter
             // We check if he is linked to a family and we get his picture thanks to its e-mail address
             $SupportMemberID = substr($sID, 1);
             $SupporterRecord = getTableRecordInfos($DbCon, "SupportMembers", $SupportMemberID);
             if ((isset($SupporterRecord['SupportMemberID'])) && (!empty($SupporterRecord['FamilyID'])))
             {
                 $FamilyID = $SupporterRecord['FamilyID'];
                 $FamilyRecord = getTableRecordInfos($DbCon, "Families", $FamilyID);

                 $SupporterName = $SupporterRecord['SupportMemberLastname'];
                 if ((!empty($SupporterRecord['SupportMemberFirstname'])) && (strLen($SupporterRecord['SupportMemberFirstname']) > 1))
                 {
                     $SupporterName .= ' '.$SupporterRecord['SupportMemberFirstname'];
                 }

                 if (strToLower($SupporterRecord['SupportMemberEmail']) == strToLower($FamilyRecord['FamilyMainEmail']))
                 {
                     if ((!empty($FamilyRecord['FamilyMainPicture']))
                         && (file_exists($CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY_HDD.$FamilyRecord['FamilyMainPicture'])))
                     {
                         // The main picture is set
                         $sHTML = "<img id=\"Pic$sID-1\" class=\"SendMsgLittlePicture\" src=\"".$CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY.$FamilyRecord['FamilyMainPicture']
                                  ."\" title=\"$SupporterName.\" />";
                     }
                 }
                 elseif (strToLower($SupporterRecord['SupportMemberEmail']) == strToLower($FamilyRecord['FamilySecondEmail']))
                 {
                     if ((!empty($FamilyRecord['FamilySecondPicture']))
                         && (file_exists($CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY_HDD.$FamilyRecord['FamilySecondPicture'])))
                     {
                         // The second picture is set
                         $sHTML = "<img id=\"Pic$sID-2\" class=\"SendMsgLittlePicture\" src=\"".$CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY.$FamilyRecord['FamilySecondPicture']
                                  ."\" title=\"$SupporterName.\" />";
                     }
                 }
             }
             break;
     }
 }

 // Release the connection to the database
 dbDisconnection($DbCon);

 // We send the response to the browser
 header('Content-type: text/html; charset='.strtolower($CONF_CHARSET));
 echo $sHTML;
?>
