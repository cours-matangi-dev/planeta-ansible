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
 * Support module : list some records of LogEvents table and allow to search in
 *
 * @author Christophe javouhey
 * @version 3.6
 * @since 2021-02-10
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

 $LaunchSearch = FALSE;

 // To take into account the page of the log events to display
 if (!empty($_GET["Pg"]))
 {
     $Page = (integer)strip_tags($_GET["Pg"]);
     $ParamsPOST_GET = "_GET";
     $LaunchSearch = TRUE;
 }
 else
 {
     $Page = 1;
     $ParamsPOST_GET = "_POST";
 }

 // To take into account the order by field to sort the table of the log events
 if (!empty($_POST["hidOrderByField"]))
 {
     $OrderBy = $_POST["hidOrderByField"];
 }
 else
 {
     if (!empty($_GET["Ob"]))
     {
         $OrderBy = (integer)strip_tags($_GET["Ob"]);
     }
     else
     {
         $OrderBy = existedGETFieldValue("hidOrderByField", 0);
     }
 }

 //################################ FORM PROCESSING ##########################
 // We define the params structure containing the search criterion
 $TabParams = array();

 if ((!empty($_POST["bSubmit"])) || (array_key_exists("hidOrderByField", $_POST)) || ($LaunchSearch))
 {
     // <<< LogEventID field >>>
     if (array_key_exists("sLogEventID", ${$ParamsPOST_GET}))
     {
         $LogEventID = trim(strip_tags(${$ParamsPOST_GET}["sLogEventID"]));
         if ($LogEventID > 0)
         {
             $TabParams["LogEventID"] = $LogEventID;
         }
     }

     // <<< LogEventItemID field >>>
     if (array_key_exists("sLogEventItemID", ${$ParamsPOST_GET}))
     {
         $LogEventItemID = trim(strip_tags(${$ParamsPOST_GET}["sLogEventItemID"]));
         if ($LogEventItemID > 0)
         {
             $TabParams["LogEventItemID"] = array($LogEventItemID);
         }
     }

     // <<< LogEventLinkedObjectID field >>>
     if (array_key_exists("sLogEventLinkedObjectID", ${$ParamsPOST_GET}))
     {
         $LogEventLinkedObjectID = trim(strip_tags(${$ParamsPOST_GET}["sLogEventLinkedObjectID"]));
         if ($LogEventLinkedObjectID > 0)
         {
             $TabParams["LogEventLinkedObjectID"] = array($LogEventLinkedObjectID);
         }
     }

     // <<< LogEventLevel field >>>
     if (array_key_exists("lLogEventLevel", ${$ParamsPOST_GET}))
     {
         $LogEventLevel = ${$ParamsPOST_GET}["lLogEventLevel"];
         if ($LogEventLevel > 0)
         {
             $TabParams["LogEventLevel"] = array($LogEventLevel);
         }
     }

     // <<< LogEventItemType field >>>
     if (is_array(${$ParamsPOST_GET}["lmLogEventItemType"]))
     {
         // Value stored in the POST variable
         $ArrayLogEventItemType = ${$ParamsPOST_GET}["lmLogEventItemType"];
     }
     else
     {
         // Value stored in the GET variable
         $ArrayLogEventItemType = explode("_", ${$ParamsPOST_GET}["lmLogEventItemType"]);
     }

     if (count($ArrayLogEventItemType) > 0)
     {
         $TabParams["LogEventItemType"] = array_extractElement($ArrayLogEventItemType, array_search("", $ArrayLogEventItemType));
     }

     // <<< LogEventService field >>>
     if (is_array(${$ParamsPOST_GET}["lmLogEventService"]))
     {
         // Value stored in the POST variable
         $ArrayLogEventService = ${$ParamsPOST_GET}["lmLogEventService"];
     }
     else
     {
         // Value stored in the GET variable
         $ArrayLogEventService = explode("_", ${$ParamsPOST_GET}["lmLogEventService"]);
     }

     if (count($ArrayLogEventService) > 0)
     {
         $TabParams["LogEventService"] = array_extractElement($ArrayLogEventService, array_search("", $ArrayLogEventService));
     }

     // <<< LogEventAction field >>>
     if (is_array(${$ParamsPOST_GET}["lmLogEventAction"]))
     {
         // Value stored in the POST variable
         $ArrayLogEventAction = ${$ParamsPOST_GET}["lmLogEventAction"];
     }
     else
     {
         // Value stored in the GET variable
         $ArrayLogEventAction = explode("_", ${$ParamsPOST_GET}["lmLogEventAction"]);
     }

     if (count($ArrayLogEventAction) > 0)
     {
         $TabParams["LogEventAction"] = array_extractElement($ArrayLogEventAction, array_search("", $ArrayLogEventAction));
     }

     // <<< LogEventStartDate field >>>
     if (array_key_exists("startDate", ${$ParamsPOST_GET}))
     {
         $StartDate = trim(strip_tags(${$ParamsPOST_GET}["startDate"]));
         if (!empty($StartDate))
         {
             $TabParams["LogEventStartDate"] = array($CONF_LOGICAL_OPERATORS[${$ParamsPOST_GET}["lOperatorStartDate"]], $StartDate);
         }
     }

     // <<< LogEventEndDate field >>>
     if (array_key_exists("endDate", ${$ParamsPOST_GET}))
     {
         $EndDate = trim(strip_tags(${$ParamsPOST_GET}["endDate"]));
         if (!empty($EndDate))
         {
             $TabParams["LogEventEndDate"] = array($CONF_LOGICAL_OPERATORS[${$ParamsPOST_GET}["lOperatorEndDate"]], $EndDate);
         }
     }

     // <<< LogEventTitle field >>>
     if (array_key_exists("sLogEventTitle", ${$ParamsPOST_GET}))
     {
         $LogEventTitle = trim(strip_tags(${$ParamsPOST_GET}["sLogEventTitle"]));
         if (!empty($LogEventTitle))
         {
             $TabParams["LogEventTitle"] = $LogEventTitle;
         }
     }

     // <<< LogEventDescription field >>>
     if (array_key_exists("sLogEventDescription", ${$ParamsPOST_GET}))
     {
         $LogEventDescription = trim(strip_tags(${$ParamsPOST_GET}["sLogEventDescription"]));
         if (!empty($LogEventDescription))
         {
             $TabParams["LogEventDescription"] = $LogEventDescription;
         }
     }

     // <<< SupportMemberID field >>>
     if (array_key_exists("sSupportMemberID", ${$ParamsPOST_GET}))
     {
         $iSupportMemberID = trim(strip_tags(${$ParamsPOST_GET}["sSupportMemberID"]));
         if ($iSupportMemberID > 0)
         {
             $TabParams["SupportMemberID"] = array($iSupportMemberID);
         }
     }

     // <<< SupportMemberLastname field >>>
     if (array_key_exists("sSupportMemberLastname", ${$ParamsPOST_GET}))
     {
         $SupportMemberLastname = trim(strip_tags(${$ParamsPOST_GET}["sSupportMemberLastname"]));
         if (!empty($SupportMemberLastname))
         {
             $TabParams["SupportMemberName"] = $SupportMemberLastname;
         }
     }

     // To launch the search
     $TabParams["All"] = TRUE;
 }
 else
 {
     // First display
     $TabParams = array();
 }
 //################################ END FORM PROCESSING ##########################

 $HidOnPrint = 0;
 if (isSet(${$ParamsPOST_GET}["hidOnPrint"]))
 {
     $HidOnPrint = ${$ParamsPOST_GET}["hidOnPrint"];
 }

 if ($HidOnPrint == 0)
 {
     // Display the search form and the result
     initGraphicInterface(
                          $LANG_INTRANET_NAME,
                          array(
                                '../../GUI/Styles/styles.css' => 'screen',
                                '../../Common/JSCalendar/dynCalendar.css' => 'screen',
                                '../Styles_Support.css' => 'screen'
                               ),
                          array(
                                '../../Common/JSCalendar/browserSniffer.js',
                                '../../Common/JSCalendar/dynCalendar.js',
                                '../../Common/JSCalendar/UseCalendar.js',
                                '../../Common/JSSortFct/SortFct.js',
                                '../../Common/JSSortFct/SortFct.js',
                                '../Verifications.js'
                               )
                         );
     openWebPage();

     // Display invisible link to go directly to content
     displayStyledLinkText($LANG_GO_TO_CONTENT, '#LogsList', 'Accessibility');

     // Display the header of the application
     displayHeader($LANG_INTRANET_HEADER);

     // Display the main menu at the top of the web page
     displaySupportMainMenu(1);

     // Content of the web page
     openArea('id="content"');

     // Display the "admin" and "parameters" contextual menus if the supporter isn't logged, an empty contextual menu otherwise
     if (isSet($_SESSION["SupportMemberID"]))
     {
         // Open the contextual menu area
         openArea('id="contextualmenu"');

         displaySupportMemberContextualMenu("admin", 1, Admin_LogsAccess);
         displaySupportMemberContextualMenu("parameters", 1, 0);

         // Display information about the logged user
         displayLoggedUser($_SESSION);

         // Close the <div> "contextualmenu"
         closeArea();

         openArea('id="page"');
     }

     // Display the informations, forms, etc. on the right of the web page
     displayTitlePage($LANG_SUPPORT_ADMIN_LOGS_ACCESS_PAGE_TITLE, 2);

     openParagraph();
     echo $LANG_SUPPORT_ADMIN_LOGS_ACCESS_PAGE_INTRODUCTION;
     closeParagraph();

     displaySearchLogEventsForm($DbCon, $TabParams, "LogsAccess.php", $Page, "SortFct", $OrderBy, "",
                                $CONF_ACCESS_APPL_PAGES[FCT_ADMIN]);

     // Exporting?
     $HidOnExport = 0;
     if (isSet(${$ParamsPOST_GET}["hidOnExport"]))
     {
         $HidOnExport = ${$ParamsPOST_GET}["hidOnExport"];
     }

     if ($HidOnExport == 1)
     {
         exportSearchLogEventsForm($DbCon, $TabParams, ${$ParamsPOST_GET}["hidExportFilename"]);

         openParagraph('InfoMsg');
         displayStyledLinkText($LANG_DOWNLOAD, $CONF_EXPORT_DIRECTORY.${$ParamsPOST_GET}["hidExportFilename"], '',
                               $LANG_DOWNLOAD_EXPORT_TIP, '_blank');
         closeParagraph();
     }

     // Release the connection to the database
     dbDisconnection($DbCon);

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
 }
 else
 {
     // Print the web page
     printSearchLogEventsForm($DbCon, $TabParams);

     // Release the connection to the database
     dbDisconnection($DbCon);
 }
?>