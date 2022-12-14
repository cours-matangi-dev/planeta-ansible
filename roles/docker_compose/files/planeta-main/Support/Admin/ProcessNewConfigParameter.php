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
 * Support module : process the creation of a new config parameter. The supporter must be logged.
 *
 * @author Christophe Javouhey
 * @version 3.0
 * @since 2016-11-04
 */

 // Include the graphic primitives library
 require '../../GUI/GraphicInterface.php';

 // To measure the execution script time
 initStartTime();

 // Create "supporter" session or use the opened "supporter" session
 session_start();

 // Redirect the user to the login page index.php if he isn't loggued
 setRedirectionToLoginPage();

 //################################ FORM PROCESSING ##########################
 if (!empty($_POST["bSubmit"]))
 {
     if ((isSet($_SESSION["SupportMemberID"])) && (isAdmin()))
     {
         // Connection to the database
         $DbCon = dbConnection();

         // Load all configuration variables from database
         loadDbConfigParameters($DbCon, array());

         $ContinueProcess = TRUE; // Used to check that the parameters are correct

         // We get the values entered by the user
         $ConfigParameterName = trim(strip_tags($_POST["sParamName"]));
         if (empty($ConfigParameterName))
         {
             // Error
             $ContinueProcess = FALSE;
         }

         $ConfigParameterType = trim(strip_tags($_POST["sParamType"]));
         if (empty($ConfigParameterType))
         {
             // Error
             $ContinueProcess = FALSE;
         }

         $ConfigParameterValue = trim($_POST["sParamValue"]);

         // Verification that the parameters are correct
         if ($ContinueProcess)
         {
             $ConfigParameterID = dbAddConfigParameter($DbCon, $ConfigParameterName, $ConfigParameterType, $ConfigParameterValue);

             if ($ConfigParameterID != 0)
             {
                 // The config parameter is added
                 $ConfirmationCaption = $LANG_CONFIRMATION;
                 $ConfirmationSentence = $LANG_CONFIRM_RECORD_ADDED;
                 $ConfirmationStyle = "ConfirmationMsg";
                 $UrlParameters = "UpdateConfigParameter.php?Cr=".md5($ConfigParameterID)."&Id=$ConfigParameterID"; // For the redirection
             }
             else
             {
                 // The config parameter can't be added
                 $ConfirmationCaption = $LANG_ERROR;
                 $ConfirmationSentence = $LANG_ERROR_ADD_RECORD;
                 $ConfirmationStyle = "ErrorMsg";
                 $UrlParameters = "AddConfigParameter.php?$QUERY_STRING"; // For the redirection
             }
         }
         else
         {
             // Errors
             $ConfirmationCaption = $LANG_ERROR;

             if ((empty($ConfigParameterName)) || (empty($ConfigParameterType)))
             {
                 // One or several mandatory fields are empty
                 $ConfirmationSentence = $LANG_ERROR_MANDORY_FIELDS;
             }
             else
             {
                 // ERROR : some parameters are empty strings
                 $ConfirmationSentence = $LANG_ERROR_WRONG_FIELDS;
             }

             $ConfirmationStyle = "ErrorMsg";
             $UrlParameters = "AddConfigParameter.php?$QUERY_STRING"; // For the redirection
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
         $UrlParameters = "AddConfigParameter.php?$QUERY_STRING"; // For the redirection
     }
 }
 else
 {
     // The supporter doesn't come from the AddConfigParameter.php page
     $ConfirmationCaption = $LANG_ERROR;
     $ConfirmationSentence = $LANG_ERROR_COME_FORM_PAGE;
     $ConfirmationStyle = "ErrorMsg";
     $UrlParameters = "AddConfigParameter.php?$QUERY_STRING"; // For the redirection
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
                      "Redirection('".$CONF_ROOT_DIRECTORY."Support/Admin/$UrlParameters', $CONF_TIME_LAG)"
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