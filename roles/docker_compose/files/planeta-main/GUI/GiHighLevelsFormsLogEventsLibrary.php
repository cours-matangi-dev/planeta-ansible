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
 * Interface module : XHTML Graphic high level forms library used to display log events
 *
 * @author Christophe Javouhey
 * @version 3.6
 * @since 2021-02-10
 */


/**
 * Display the form to search a log event in the current web page, in the graphic interface in XHTML
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-02-10
 *
 * @param $DbConnection                DB object            Object of the opened database connection
 * @param $TabParams                   Array of Strings     search criterion used to find some log events
 * @param $ProcessFormPage             String               URL of the page which will process the form allowing to find and to sort
 *                                                          the table of the log events found
 * @param $Page                        Integer              Number of the Page to display [1..n]
 * @param $SortFct                     String               Javascript function used to sort the table
 * @param $OrderBy                     Integer              n° Criteria used to sort the log events. If < 0, DESC is used,
 *                                                          otherwise ASC is used
 * @param $DetailsPage                 String               URL of the page to display details about a log event.
 *                                                          This string can be empty
 * @param $AccessRules                 Array of Integers    List used to select only some support members
 *                                                          allowed to create or update log events
 */
 function displaySearchLogEventsForm($DbConnection, $TabParams, $ProcessFormPage, $Page = 1, $SortFct = '', $OrderBy = 0, $DetailsPage = '', $AccessRules = array())
 {
     if (isSet($_SESSION["SupportMemberID"]))
     {
         // The supporter must be allowed to access to log events list
         $cUserAccess = FCT_ACT_NO_RIGHTS;
         if ((isset($AccessRules[FCT_ACT_CREATE])) && (in_array($_SESSION["SupportMemberStateID"], $AccessRules[FCT_ACT_CREATE])))
         {
             // Write mode
             $cUserAccess = FCT_ACT_CREATE;
         }
         elseif ((isset($AccessRules[FCT_ACT_UPDATE])) && (in_array($_SESSION["SupportMemberStateID"], $AccessRules[FCT_ACT_UPDATE])))
         {
             // Write mode
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

         if (in_array($cUserAccess, array(FCT_ACT_CREATE, FCT_ACT_UPDATE, FCT_ACT_READ_ONLY, FCT_ACT_PARTIAL_READ_ONLY)))
         {
             // Get some constant names
             $ArrayAppConsts = get_defined_constants(TRUE);
             $ArrayAppConstItemTypes = array();
             $ArrayAppConstServices = array();
             $ArrayAppConstActions = array();
             $ArrayAppConstLevels = array();
             if (isset($ArrayAppConsts['user']))
             {
                 foreach($ArrayAppConsts['user'] as $ConstName => $ConstValue)
                 {
                     if (substr($ConstName, 0, 4) == 'EVT_')
                     {
                         if (substr($ConstName, 0, 9) == 'EVT_SERV_')
                         {
                             // Log event service constant
                             $ArrayAppConstServices[] = $ConstValue;
                         }
                         elseif (substr($ConstName, 0, 8) == 'EVT_ACT_')
                         {
                             // Log event action constant
                             $ArrayAppConstActions[] = $ConstValue;
                         }
                         elseif (substr($ConstName, 0, 10) == 'EVT_LEVEL_')
                         {
                             // Log event level constant
                             $ArrayAppConstLevels[$ConstValue] = $ConstName;
                         }
                         else
                         {
                             // Log event item type constant
                             $ArrayAppConstItemTypes[] = $ConstValue;
                         }
                     }
                 }
             }

             // Open a form
             openForm("FormSearchLogEvent", "post", "$ProcessFormPage", "", "");

             // Display the table (frame) where the form will take place
             openStyledFrame($GLOBALS["LANG_SEARCH"], "Frame", "Frame", "SearchFrame");

             // Log event ID input text
             $sLogEventID = generateInputField("sLogEventID", "text", "20", "10", $GLOBALS["LANG_LOG_EVENT_DATE"],
                                               stripslashes(strip_tags(existedPOSTFieldValue("sLogEventID",
                                                                                             stripslashes(existedGETFieldValue("sLogEventID", ""))))));

             // Log event item ID input text
             $sLogEventItemID = generateInputField("sLogEventItemID", "text", "20", "10", $GLOBALS["LANG_LOG_EVENT_ITEM_ID_TIP"],
                                               stripslashes(strip_tags(existedPOSTFieldValue("sLogEventItemID",
                                                                                             stripslashes(existedGETFieldValue("sLogEventItemID", ""))))));

             // Log event linked object ID input text
             $sLogEventLinkedObjectID = generateInputField("sLogEventLinkedObjectID", "text", "20", "10", $GLOBALS["LANG_LOG_EVENT_LINKED_OBJECT_ID_TIP"],
                                                           stripslashes(strip_tags(existedPOSTFieldValue("sLogEventLinkedObjectID",
                                                                                                         stripslashes(existedGETFieldValue("sLogEventLinkedObjectID", ""))))));

             // List of levels
             $ArrayLogEventLevels = array(0 => "-");
             foreach($ArrayAppConstLevels as $k => $CurrentValue)
             {
                 $ArrayLogEventLevels[$k] = $CurrentValue;
             }

             if ((isset($TabParams['LogEventLevel'])) && (count($TabParams['LogEventLevel']) > 0))
             {
                 $SelectedItem = $TabParams['LogEventLevel'][0];
             }
             else
             {
                 $SelectedItem = 0;
             }

             $sLogEventLevels = generateSelectField("lLogEventLevel", array_keys($ArrayLogEventLevels), array_values($ArrayLogEventLevels),
                                                    zeroFormatValue(existedPOSTFieldValue("lLogEventLevel",
                                                                                          existedGETFieldValue("lLogEventLevel",
                                                                                                               $SelectedItem))));

             // Multiple list of log event item types
             $ArrayLogEventItemType = array_merge(array(''), $ArrayAppConstItemTypes);
             $ArraySelectedItems = existedPOSTFieldValue("lmLogEventItemType", existedGETFieldValue("lmLogEventItemType", ""));
             if (!is_array($ArraySelectedItems))
             {
                 // Selected values
                 $ArraySelectedItems = explode("_", $ArraySelectedItems);
             }

             $sLogEventItemTypes = generateMultipleSelectField("lmLogEventItemType", $ArrayLogEventItemType, $ArrayLogEventItemType,
                                                               5, $ArraySelectedItems);


             // Multiple list of log event services
             $ArrayLogEventServices = array_merge(array(''), $ArrayAppConstServices);
             $ArraySelectedItems = existedPOSTFieldValue("lmLogEventService", existedGETFieldValue("lmLogEventService", ""));
             if (!is_array($ArraySelectedItems))
             {
                 // Selected values
                 $ArraySelectedItems = explode("_", $ArraySelectedItems);
             }

             $sLogEventServices = generateMultipleSelectField("lmLogEventService", $ArrayLogEventServices, $ArrayLogEventServices,
                                                              5, $ArraySelectedItems);

             // Multiple list of log event actions
             $ArrayLogEventActions = array_merge(array(''), $ArrayAppConstActions);
             $ArraySelectedItems = existedPOSTFieldValue("lmLogEventAction", existedGETFieldValue("lmLogEventAction", ""));
             if (!is_array($ArraySelectedItems))
             {
                 // Selected values
                 $ArraySelectedItems = explode("_", $ArraySelectedItems);
             }

             $sLogEventActions = generateMultipleSelectField("lmLogEventAction", $ArrayLogEventActions, $ArrayLogEventActions,
                                                             5, $ArraySelectedItems);

             // Log event dates
             // <<< Start date INPUTFIELD >>>
             $iDefaultSelectedValue = zeroFormatValue(existedPOSTFieldValue("lOperatorStartDate",
                                                                            existedGETFieldValue("lOperatorStartDate", 0)));
             $sDefaultDateValue = stripslashes(strip_tags(existedPOSTFieldValue("startDate",
                                                                                stripslashes(existedGETFieldValue("startDate", "")))));
             if ((empty($sDefaultDateValue)) && (isset($TabParams['LogEventStartDate'])) && (!empty($TabParams['LogEventStartDate'])))
             {
                 $sDefaultDateValue = date($GLOBALS['CONF_DATE_DISPLAY_FORMAT'], strtotime($TabParams['LogEventStartDate'][0]));
                 $iDefaultSelectedValue = array_search($TabParams['LogEventStartDate'][1], $GLOBALS["CONF_LOGICAL_OPERATORS"]);
             }

             $sStartDate = generateSelectField("lOperatorStartDate", array_keys($GLOBALS["CONF_LOGICAL_OPERATORS"]),
                                               $GLOBALS["CONF_LOGICAL_OPERATORS"], $iDefaultSelectedValue, "");
             $sStartDate .= "&nbsp;".generateInputField("startDate", "text", "10", "10", "", $sDefaultDateValue);
             $sStartDate .= "<script language=\"JavaScript\" type=\"text/javascript\">\n<!--\n\t StartDateCalendar = new dynCalendar('StartDateCalendar', 'calendarCallback', '".$GLOBALS['CONF_ROOT_DIRECTORY']."Common/JSCalendar/images/', 'startDate', '', '".$GLOBALS["CONF_LANG"]."'); \n\t//-->\n</script>\n";

             // <<< End date INPUTFIELD >>>
             $iDefaultSelectedValue = zeroFormatValue(existedPOSTFieldValue("lOperatorEndDate",
                                                                            existedGETFieldValue("lOperatorEndDate", 0)));
             $sDefaultDateValue = stripslashes(strip_tags(existedPOSTFieldValue("endDate",
                                                                                stripslashes(existedGETFieldValue("endDate", "")))));
             if ((empty($sDefaultDateValue)) && (isset($TabParams['LogEventEndDate'])) && (!empty($TabParams['LogEventEndDate'])))
             {
                 $sDefaultDateValue = date($GLOBALS['CONF_DATE_DISPLAY_FORMAT'], strtotime($TabParams['LogEventEndDate'][0]));
                 $iDefaultSelectedValue = array_search($TabParams['LogEventEndDate'][1], $GLOBALS["CONF_LOGICAL_OPERATORS"]);
             }

             $sEndDate = generateSelectField("lOperatorEndDate", array_keys($GLOBALS["CONF_LOGICAL_OPERATORS"]),
                                             $GLOBALS["CONF_LOGICAL_OPERATORS"], $iDefaultSelectedValue, "");
             $sEndDate .= "&nbsp;".generateInputField("endDate", "text", "10", "10", "", $sDefaultDateValue);
             $sEndDate .= "<script language=\"JavaScript\" type=\"text/javascript\">\n<!--\n\t EndDateCalendar = new dynCalendar('EndDateCalendar', 'calendarCallback', '".$GLOBALS['CONF_ROOT_DIRECTORY']."Common/JSCalendar/images/', 'endDate', '', '".$GLOBALS["CONF_LANG"]."'); \n\t//-->\n</script>\n";

             $sLogEventDates = $sStartDate.generateBR(1).$sEndDate;

             // Log event title input text
             $sLogEventTitle = generateInputField("sLogEventTitle", "text", "255", "25", $GLOBALS["LANG_LOG_EVENT_TITLE_TIP"],
                                                  stripslashes(strip_tags(existedPOSTFieldValue("sLogEventTitle",
                                                                                                stripslashes(existedGETFieldValue("sLogEventTitle", ""))))));

             // Log event description input text
             $sLogEventDescription = generateInputField("sLogEventDescription", "text", "255", "25", $GLOBALS["LANG_LOG_EVENT_DESCRIPTION_TIP"],
                                                        stripslashes(strip_tags(existedPOSTFieldValue("sLogEventDescription",
                                                                                                      stripslashes(existedGETFieldValue("sLogEventDescription", ""))))));

             // Support member ID input text
             $sSupportMemberID = generateInputField("sSupportMemberID", "text", "20", "10", $GLOBALS["LANG_LOG_EVENT_SUPPORTMEMBER_ID_TIP"],
                                                    stripslashes(strip_tags(existedPOSTFieldValue("sSupportMemberID",
                                                                                                  stripslashes(existedGETFieldValue("sSupportMemberID", ""))))));

             // Lastname input text
             $sSupportMemberLastname = generateInputField("sSupportMemberLastname", "text", "50", "25", $GLOBALS["LANG_LASTNAME_TIP"],
                                                          stripslashes(strip_tags(existedPOSTFieldValue("sSupportMemberLastname",
                                                                                                        stripslashes(existedGETFieldValue("sSupportMemberLastname", ""))))));

             // Display the form
             echo "<table id=\"SupportMembersList\" cellspacing=\"0\" cellpadding=\"0\">\n";
             echo "<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_LOG_EVENT_ID"]."</td><td class=\"Value\">$sLogEventID</td><td class=\"Label\">".$GLOBALS['LANG_LOG_EVENT_LEVEL']."</td><td class=\"Value\">$sLogEventLevels</td><td class=\"Label\">".$GLOBALS['LANG_LOG_EVENT_ITEM_ID']."</td><td class=\"Value\">$sLogEventItemID</td>\n</tr>\n";
             echo "<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_LOG_EVENT_ITEM_TYPE"]."</td><td class=\"Value\">$sLogEventItemTypes</td><td class=\"Label\">".$GLOBALS['LANG_LOG_EVENT_SERVICE']."</td><td class=\"Value\">$sLogEventServices</td><td class=\"Label\">".$GLOBALS['LANG_LOG_EVENT_ACTION']."</td><td class=\"Value\">$sLogEventActions</td>\n</tr>\n";
             echo "<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_LOG_EVENT_DATE"]."</td><td class=\"Value\">$sLogEventDates</td><td class=\"Label\">".$GLOBALS['LANG_LOG_EVENT_TITLE']."</td><td class=\"Value\">$sLogEventTitle</td><td class=\"Label\">".$GLOBALS['LANG_LOG_EVENT_DESCRIPTION']."</td><td class=\"Value\">$sLogEventDescription</td>\n</tr>\n";
             echo "<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_LOG_EVENT_SUPPORTMEMBER_ID"]."</td><td class=\"Value\">$sSupportMemberID</td><td class=\"Label\">".$GLOBALS["LANG_LASTNAME"]."</td><td class=\"Value\">$sSupportMemberLastname</td><td class=\"Label\">".$GLOBALS['LANG_LOG_EVENT_LINKED_OBJECT_ID']."</td><td class=\"Value\">$sLogEventLinkedObjectID</td>\n</tr>\n";
             echo "</table>\n";

             // Display the hidden fields
             insertInputField("hidOrderByField", "hidden", "", "", "", $OrderBy);
             insertInputField("hidOnPrint", "hidden", "", "", "", zeroFormatValue(existedPOSTFieldValue("hidOnPrint", existedGETFieldValue("hidOnPrint", ""))));
             insertInputField("hidOnExport", "hidden", "", "", "", zeroFormatValue(existedPOSTFieldValue("hidOnExport", existedGETFieldValue("hidOnExport", ""))));
             insertInputField("hidExportFilename", "hidden", "", "", "", existedPOSTFieldValue("hidExportFilename", existedGETFieldValue("hidExportFilename", "")));
             closeStyledFrame();

             echo "<table class=\"validation\">\n<tr>\n\t<td>";
             insertInputField("bSubmit", "submit", "", "", $GLOBALS["LANG_SUBMIT_BUTTON_TIP"], $GLOBALS["LANG_SUBMIT_BUTTON_CAPTION"]);
             echo "</td><td class=\"FormSpaceBetweenButtons\"></td><td>";
             insertInputField("bReset", "reset", "", "", $GLOBALS["LANG_RESET_BUTTON_TIP"], $GLOBALS["LANG_RESET_BUTTON_CAPTION"]);
             echo "</td>\n</tr>\n</table>\n";

             closeForm();

             // The supporter has executed a search
             $NbTabParams = count($TabParams);
             if ($NbTabParams > 0)
             {
                 displayBR(2);

                 $ArrayCaptions = array($GLOBALS["LANG_LOG_EVENT_ID"], $GLOBALS["LANG_LOG_EVENT_DATE"], $GLOBALS["LANG_LOG_EVENT_ITEM_ID"],
                                        $GLOBALS["LANG_LOG_EVENT_ITEM_TYPE"], $GLOBALS["LANG_LOG_EVENT_SERVICE"], $GLOBALS["LANG_LOG_EVENT_ACTION"],
                                        $GLOBALS["LANG_LOG_EVENT_LEVEL"], $GLOBALS["LANG_LOG_EVENT_TITLE"], $GLOBALS["LANG_LOG_EVENT_DESCRIPTION"],
                                        $GLOBALS["LANG_LOG_EVENT_LINKED_OBJECT_ID"], $GLOBALS["LANG_LOG_EVENT_SUPPORTMEMBER_ID"]);

                 $ArraySorts = array("LogEventID", "LogEventDate", "LogEventItemID", "LogEventItemType", "LogEventService", "LogEventAction",
                                     "LogEventLevel", "LogEventTitle", "LogEventDescription", "LogEventLinkedObjectID", "SupportMemberID");

                 // Order by instruction
                 if ((abs($OrderBy) <= count($ArraySorts)) && ($OrderBy != 0))
                 {
                     $StrOrderBy = $ArraySorts[abs($OrderBy) - 1];
                     if ($OrderBy < 0)
                     {
                         $StrOrderBy .= " DESC";
                     }
                 }
                 else
                 {
                     $StrOrderBy = "LogEventID DESC";
                 }

                 // We launch the search
                 // We treat the LogEventDate (start and end dates)
                 if ((isset($TabParams['LogEventStartDate'])) && (isset($TabParams['LogEventEndDate'])))
                 {
                     $TabParams['LogEventDate'] = array_merge($TabParams['LogEventStartDate'], $TabParams['LogEventEndDate']);
                     unset($TabParams['LogEventStartDate'], $TabParams['LogEventEndDate']);
                 }
                 elseif (isset($TabParams['LogEventStartDate']))
                 {
                     $TabParams['LogEventDate'] = $TabParams['LogEventStartDate'];
                     unset($TabParams['LogEventStartDate']);
                 }
                 elseif (isset($TabParams['LogEventEndDate']))
                 {
                     $TabParams['LogEventDate'] = $TabParams['LogEventEndDate'];
                     unset($TabParams['LogEventEndDate']);
                 }

                 $NbRecords = getNbdbSearchLogEvent($DbConnection, $TabParams);
                 if ($NbRecords > 0)
                 {
                     // To get only log events of the page
                     $ArrayRecords = dbSearchLogEvent($DbConnection, $TabParams, $StrOrderBy, $Page, $GLOBALS["CONF_RECORDS_PER_PAGE"]);

                     /*openParagraph('toolbar');
                     displayStyledLinkText($GLOBALS["LANG_PRINT"], "javascript:PrintWebPage()", "", "", "");
                     echo "&nbsp;&nbsp;";
                     displayStyledLinkText($GLOBALS["LANG_EXPORT_TO_XML_FILE"], "javascript:ExportWebPage('".$GLOBALS["CONF_EXPORT_XML_RESULT_FILENAME"].time().".xml')", "", "", "");
                     echo "&nbsp;&nbsp;";
                     displayStyledLinkText($GLOBALS["LANG_EXPORT_TO_CSV_FILE"], "javascript:ExportWebPage('".$GLOBALS["CONF_EXPORT_CSV_RESULT_FILENAME"].time().".csv')", "", "", "");
                     closeParagraph(); */

                     // There are some log events found
                     foreach($ArrayRecords["LogEventID"] as $i => $CurrentValue)
                     {
                         if (empty($DetailsPage))
                         {
                             // We display the ID
                             $ArrayData[0][] = $ArrayRecords["LogEventID"][$i];
                         }
                         else
                         {
                             // We display the ID with a hyperlink
                             $ArrayData[0][] = generateAowIDHyperlink($ArrayRecords["LogEventID"][$i], $ArrayRecords["LogEventID"][$i],
                                                                      $DetailsPage, $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"], "", "_blank");
                         }

                         $ArrayData[1][] = date($GLOBALS['CONF_DATE_DISPLAY_FORMAT'].' '.$GLOBALS['CONF_TIME_DISPLAY_FORMAT'],
                                                strtotime($ArrayRecords["LogEventDate"][$i]));

                         // We add some hyperlinks to vie details when it's possible
                         $LogEventItemID = $ArrayRecords["LogEventItemID"][$i];
                         $LogEventLinkedObjectID = $ArrayRecords["LogEventLinkedObjectID"][$i];

                         switch($ArrayRecords["LogEventItemType"][$i])
                         {
                             case EVT_SYSTEM:
                                 switch($ArrayRecords["LogEventService"][$i])
                                 {
                                     case EVT_SERV_TOWN:
                                         // It's a town
                                         switch($ArrayRecords["LogEventAction"][$i])
                                         {
                                             case EVT_ACT_CREATE:
                                             case EVT_ACT_UPDATE:
                                                 if (isExistingTown($DbConnection, $LogEventItemID))
                                                 {
                                                     $LogEventItemID = generateAowIDHyperlink($LogEventItemID, $LogEventItemID,
                                                                                              "../Canteen/UpdateTown.php",
                                                                                              $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"],
                                                                                              "", "_blank");
                                                }
                                                break;
                                         }
                                         break;
                                 }
                                 break;

                             case EVT_PROFIL:
                                 switch($ArrayRecords["LogEventService"][$i])
                                 {
                                     case EVT_SERV_PROFIL:
                                     case EVT_SERV_LOGIN:
                                         // It's a support member
                                         $LogEventItemID = generateAowIDHyperlink($LogEventItemID, $LogEventItemID, "UpdateSupportMember.php",
                                                                                  $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"], "", "_blank");
                                         break;
                                 }
                                 break;

                             case EVT_MESSAGE:
                                 switch($ArrayRecords["LogEventService"][$i])
                                 {
                                     case EVT_SERV_ALIAS:
                                         // It's an alias used to send messages
                                         switch($ArrayRecords["LogEventAction"][$i])
                                         {
                                             case EVT_ACT_CREATE:
                                             case EVT_ACT_UPDATE:
                                             case EVT_ACT_COPY:
                                                 if (isExistingAlias($DbConnection, $LogEventItemID))
                                                 {
                                                     $LogEventItemID = generateAowIDHyperlink($LogEventItemID, $LogEventItemID,
                                                                                              "../UpdateAlias.php",
                                                                                              $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"],
                                                                                              "", "_blank");
                                                }
                                                break;
                                         }
                                         break;
                                 }
                                 break;

                             case EVT_CANTEEN:
                             case EVT_NURSERY:
                                 // Canteen registration or nursery registration
                                 switch($ArrayRecords["LogEventService"][$i])
                                 {
                                     case EVT_SERV_PLANNING:
                                         switch($ArrayRecords["LogEventAction"][$i])
                                         {
                                             case EVT_ACT_DELETE:
                                                 if (isExistingChild($DbConnection, $LogEventLinkedObjectID))
                                                 {
                                                     // The linked object ID is a child : we can display a hyperlink to view details
                                                     $LogEventLinkedObjectID = generateAowIDHyperlink($LogEventLinkedObjectID, $LogEventLinkedObjectID,
                                                                                                      "../Canteen/UpdateChild.php",
                                                                                                      $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"],
                                                                                                      "", "_blank");
                                                }
                                                break;
                                         }
                                         break;
                                 }
                                 break;


                             case EVT_DOCUMENT_APPROVAL:
                                 switch($ArrayRecords["LogEventService"][$i])
                                 {
                                     case EVT_SERV_DOCUMENT_APPROVAL:
                                         // It's a document to approve
                                         switch($ArrayRecords["LogEventAction"][$i])
                                         {
                                             case EVT_ACT_ADD:
                                             case EVT_ACT_UPDATE:
                                                 if (isExistingEvent($DbConnection, $LogEventItemID))
                                                 {
                                                     $LogEventItemID = generateAowIDHyperlink($LogEventItemID, $LogEventItemID,
                                                                                              "../Canteen/UpdateDocumentApproval.php",
                                                                                              $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"],
                                                                                              "", "_blank");
                                                }
                                                break;
                                         }
                                         break;

                                     case EVT_SERV_DOCUMENT_FAMILY_APPROVAL:
                                         // It's an approval of a document
                                         if (isExistingDocumentApproval($DbConnection, $LogEventLinkedObjectID))
                                         {
                                             // The linked object ID is a document to approve by a family : we can display a hyperlink to view details
                                             $LogEventLinkedObjectID = generateAowIDHyperlink($LogEventLinkedObjectID, $LogEventLinkedObjectID,
                                                                                              "../Canteen/UpdateDocumentApproval.php",
                                                                                              $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"], "", "_blank");
                                         }
                                         break;
                                 }
                                 break;

                             case EVT_DONATION:
                                 switch($ArrayRecords["LogEventService"][$i])
                                 {
                                     case EVT_SERV_DONATION:
                                         // It's a donation
                                         switch($ArrayRecords["LogEventAction"][$i])
                                         {
                                             case EVT_ACT_CREATE:
                                             case EVT_ACT_UPDATE:
                                                 if (isExistingDonation($DbConnection, $LogEventItemID))
                                                 {
                                                     $LogEventItemID = generateAowIDHyperlink($LogEventItemID, $LogEventItemID,
                                                                                              "../Cooperation/UpdateDonation.php",
                                                                                              $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"],
                                                                                              "", "_blank");
                                                }
                                                break;
                                         }
                                         break;
                                 }
                                 break;

                             case EVT_FAMILY:
                                 switch($ArrayRecords["LogEventService"][$i])
                                 {
                                     case EVT_SERV_FAMILY:
                                         // It's a family
                                         switch($ArrayRecords["LogEventAction"][$i])
                                         {
                                             case EVT_ACT_CREATE:
                                             case EVT_ACT_UPDATE:
                                                 if (isExistingFamily($DbConnection, $LogEventItemID))
                                                 {
                                                     $LogEventItemID = generateAowIDHyperlink($LogEventItemID, $LogEventItemID,
                                                                                              "../Canteen/UpdateFamily.php",
                                                                                              $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"],
                                                                                              "", "_blank");
                                                 }
                                                 break;
                                         }
                                         break;

                                     case EVT_SERV_CHILD:
                                         // It's a child of a family
                                         switch($ArrayRecords["LogEventAction"][$i])
                                         {
                                             case EVT_ACT_ADD:
                                             case EVT_ACT_UPDATE:
                                                 if (isExistingChild($DbConnection, $LogEventItemID))
                                                 {
                                                     $LogEventItemID = generateAowIDHyperlink($LogEventItemID, $LogEventItemID,
                                                                                              "../Canteen/UpdateChild.php",
                                                                                              $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"],
                                                                                              "", "_blank");
                                                 }
                                                 break;
                                         }
                                         break;

                                     case EVT_SERV_SUSPENSION:
                                         // It's a child suspension
                                         switch($ArrayRecords["LogEventAction"][$i])
                                         {
                                             case EVT_ACT_ADD:
                                             case EVT_ACT_UPDATE:
                                                 if (isExistingSuspension($DbConnection, $LogEventItemID))
                                                 {
                                                     $LogEventItemID = generateAowIDHyperlink($LogEventItemID, $LogEventItemID,
                                                                                              "../Canteen/UpdateChildSuspension.php",
                                                                                              $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"],
                                                                                              "", "_blank");
                                                 }
                                                 break;
                                         }
                                         break;
                                 }
                                 break;

                             case EVT_EVENT:
                                 switch($ArrayRecords["LogEventService"][$i])
                                 {
                                     case EVT_SERV_EVENT:
                                         // It's an event
                                         switch($ArrayRecords["LogEventAction"][$i])
                                         {
                                             case EVT_ACT_CREATE:
                                             case EVT_ACT_UPDATE:
                                                 if (isExistingEvent($DbConnection, $LogEventItemID))
                                                 {
                                                     // The event still exists : we can display a hyperlink to view details
                                                     $LogEventItemID = generateAowIDHyperlink($LogEventItemID, $LogEventItemID,
                                                                                              "../Cooperation/UpdateEvent.php",
                                                                                              $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"],
                                                                                              "", "_blank");
                                                }
                                                break;
                                         }
                                         break;

                                     case EVT_SERV_EVENT_REGISTRATION:
                                         // It's an event registration
                                         switch($ArrayRecords["LogEventAction"][$i])
                                         {
                                             case EVT_ACT_ADD:
                                             case EVT_ACT_UPDATE:
                                                 if (isExistingEventRegistration($DbConnection, $LogEventItemID))
                                                 {
                                                     $LogEventItemID = generateAowIDHyperlink($LogEventItemID, $LogEventItemID,
                                                                                              "../Cooperation/UpdateEventRegistration.php",
                                                                                              $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"],
                                                                                              "", "_blank");
                                                 }
                                                 break;
                                         }

                                         // The linked object ID is an event
                                         if (isExistingEvent($DbConnection, $LogEventLinkedObjectID))
                                         {
                                             $LogEventLinkedObjectID = generateAowIDHyperlink($LogEventLinkedObjectID, $LogEventLinkedObjectID,
                                                                                              "../Cooperation/UpdateEvent.php",
                                                                                              $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"], "", "_blank");
                                         }
                                         break;

                                     case EVT_SERV_EVENT_SWAPPED_REGISTRATION:
                                         // It's a swap of event registration
                                         // The linked object ID is an event
                                         if (isExistingEvent($DbConnection, $LogEventLinkedObjectID))
                                         {
                                             $LogEventLinkedObjectID = generateAowIDHyperlink($LogEventLinkedObjectID, $LogEventLinkedObjectID,
                                                                                              "../Cooperation/UpdateEvent.php",
                                                                                              $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"], "", "_blank");
                                         }
                                         break;
                                 }
                                 break;

                             case EVT_MEETING:
                                 switch($ArrayRecords["LogEventService"][$i])
                                 {
                                     case EVT_SERV_MEETING:
                                         // It's a meeting rrom registration
                                         switch($ArrayRecords["LogEventAction"][$i])
                                         {
                                             case EVT_ACT_CREATE:
                                             case EVT_ACT_UPDATE:
                                                 if (isExistingMeetingRoomRegistration($DbConnection, $LogEventItemID))
                                                 {
                                                     $LogEventItemID = generateAowIDHyperlink($LogEventItemID, $LogEventItemID,
                                                                                              "../Cooperation/UpdateMeetingRoomRegistration.php",
                                                                                              $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"],
                                                                                              "", "_blank");
                                                }
                                                break;
                                         }
                                         break;
                                 }
                                 break;

                             case EVT_PAYMENT:
                                 switch($ArrayRecords["LogEventService"][$i])
                                 {
                                     case EVT_SERV_PAYMENT:
                                         // It's a payment of a bill of a family
                                         switch($ArrayRecords["LogEventAction"][$i])
                                         {
                                             case EVT_ACT_ADD:
                                             case EVT_ACT_UPDATE:
                                                 if (isExistingPayment($DbConnection, $LogEventItemID))
                                                 {
                                                     $LogEventItemID = generateAowIDHyperlink($LogEventItemID, $LogEventItemID,
                                                                                              "../Canteen/UpdatePayment.php",
                                                                                              $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"],
                                                                                              "", "_blank");
                                                }
                                                break;

                                             case EVT_ACT_DELETE:
                                                 // The linked object ID is a family
                                                 if (isExistingEvent($DbConnection, $LogEventLinkedObjectID))
                                                 {
                                                     $LogEventLinkedObjectID = generateAowIDHyperlink($LogEventLinkedObjectID, $LogEventLinkedObjectID,
                                                                                                      "../Canteen/UpdateFamily.php",
                                                                                                      $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"], "", "_blank");
                                                 }
                                                 break;
                                         }
                                         break;

                                     case EVT_SERV_BANK:
                                         // It's a bank
                                         switch($ArrayRecords["LogEventAction"][$i])
                                         {
                                             case EVT_ACT_CREATE:
                                             case EVT_ACT_UPDATE:
                                                 if (isExistingBank($DbConnection, $LogEventItemID))
                                                 {
                                                     $LogEventItemID = generateAowIDHyperlink($LogEventItemID, $LogEventItemID,
                                                                                              "../Canteen/UpdateBank.php",
                                                                                              $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"],
                                                                                              "", "_blank");
                                                }
                                                break;
                                         }
                                         break;

                                     case EVT_SERV_DISCOUNT:
                                         // It's a discount for a family
                                         switch($ArrayRecords["LogEventAction"][$i])
                                         {
                                             case EVT_ACT_ADD:
                                             case EVT_ACT_UPDATE:
                                                 if (isExistingDiscountFamily($DbConnection, $LogEventItemID))
                                                 {
                                                     $LogEventItemID = generateAowIDHyperlink($LogEventItemID, $LogEventItemID,
                                                                                              "../Canteen/UpdateDiscountFamily.php",
                                                                                              $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"],
                                                                                              "", "_blank");
                                                }
                                                break;

                                             case EVT_ACT_DELETE:
                                                 // The linked object ID is a family
                                                 if (isExistingEvent($DbConnection, $LogEventLinkedObjectID))
                                                 {
                                                     $LogEventLinkedObjectID = generateAowIDHyperlink($LogEventLinkedObjectID, $LogEventLinkedObjectID,
                                                                                                      "../Canteen/UpdateFamily.php",
                                                                                                      $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"], "", "_blank");
                                                 }
                                                 break;
                                         }
                                         break;
                                 }
                                 break;

                             case EVT_SERV_WORKGROUP:
                                 switch($ArrayRecords["LogEventService"][$i])
                                 {
                                     case EVT_SERV_WORKGROUP:
                                         // It's a workgroup
                                         switch($ArrayRecords["LogEventAction"][$i])
                                         {
                                             case EVT_ACT_CREATE:
                                             case EVT_ACT_UPDATE:
                                                 if (isExistingWorkgroup($DbConnection, $LogEventItemID))
                                                 {
                                                     $LogEventItemID = generateAowIDHyperlink($LogEventItemID, $LogEventItemID,
                                                                                              "../Cooperation/UpdateWorkgroup.php",
                                                                                              $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"],
                                                                                              "", "_blank");
                                                }
                                                break;
                                         }
                                         break;

                                     case EVT_SERV_WORKGROUP_REGISTRATION:
                                         // It's a workgroup registration
                                         switch($ArrayRecords["LogEventAction"][$i])
                                         {
                                             case EVT_ACT_ADD:
                                             case EVT_ACT_UPDATE:
                                                 if (isExistingWorkgroupRegistration($DbConnection, $LogEventItemID))
                                                 {
                                                     $LogEventItemID = generateAowIDHyperlink($LogEventItemID, $LogEventItemID,
                                                                                              "../Cooperation/UpdateWorkGroupRegistration.php",
                                                                                              $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"],
                                                                                              "", "_blank");
                                                 }
                                                 break;
                                         }

                                         // The linked object ID is a workgroup
                                         if (isExistingWorkgroup($DbConnection, $LogEventLinkedObjectID))
                                         {
                                             $LogEventLinkedObjectID = generateAowIDHyperlink($LogEventLinkedObjectID, $LogEventLinkedObjectID,
                                                                                              "../Cooperation/UpdateWorkgroup.php",
                                                                                              $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"], "", "_blank");
                                         }
                                         break;
                                 }
                                 break;
                         }

                         $ArrayData[2][] = $LogEventItemID;
                         $ArrayData[3][] = $ArrayRecords["LogEventItemType"][$i];
                         $ArrayData[4][] = $ArrayRecords["LogEventService"][$i];
                         $ArrayData[5][] = $ArrayRecords["LogEventAction"][$i];

                         $sLogEventLevel = $ArrayRecords["LogEventLevel"][$i];
                         switch($ArrayRecords["LogEventLevel"][$i])
                         {
                             case EVT_LEVEL_WARNING:
                                 $sLogEventLevel = "EVT_LEVEL_WARNING";
                                 break;

                             case EVT_LEVEL_SYSTEM:
                                 $sLogEventLevel = "EVT_LEVEL_SYSTEM";
                                 break;

                             case EVT_LEVEL_OTHER_EVT:
                                 $sLogEventLevel = "EVT_LEVEL_OTHER_EVT";
                                 break;

                             case EVT_LEVEL_MAIN_EVT:
                             default:
                                 $sLogEventLevel = "EVT_LEVEL_MAIN_EVT";
                                 break;
                         }

                         $ArrayData[6][] = $sLogEventLevel;
                         $ArrayData[7][] = $ArrayRecords["LogEventTitle"][$i];
                         $ArrayData[8][] = $ArrayRecords["LogEventDescription"][$i];
                         $ArrayData[9][] = $LogEventLinkedObjectID;

                         $sSupportMemberName = "";
                         if (!empty($ArrayRecords["SupportMemberID"][$i]))
                         {
                             // Display infos about the supporter and a hyperlink to view details
                             $sSupportMemberName = generateAowIDHyperlink($ArrayRecords["SupportMemberID"][$i].' ('.$ArrayRecords["Supporter"][$i].')',
                                                                          $ArrayRecords["SupportMemberID"][$i], "UpdateSupportMember.php",
                                                                          $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"], "", "_blank");
                         }

                         $ArrayData[10][] = $sSupportMemberName;
                     }

                     // Display the table which contains the log events found
                     $ArraySortedFields = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11");

                     displayStyledTable($ArrayCaptions, $ArraySortedFields, $SortFct, $ArrayData, '', '', '', '',
                                        array(), $OrderBy, array());

                     // Display the previous and next links
                     $NoPage = 0;
                     if ($Page <= 1)
                     {
                         $PreviousLink = '';
                     }
                     else
                     {
                         $NoPage = $Page - 1;

                         // We get the parameters of the GET form or the POST form
                         if (count($_POST) == 0)
                         {
                             // GET form
                             if (count($_GET) == 0)
                             {
                                 // No form submitted
                                 $PreviousLink = "$ProcessFormPage?Pg=$NoPage&amp;Ob=$OrderBy";
                             }
                             else
                             {
                                 // GET form
                                 $PreviousLink = "$ProcessFormPage?";
                                 foreach($_GET as $i => $CurrentValue)
                                 {
                                     if ($i == "Pg")
                                     {
                                         $CurrentValue = $NoPage;
                                     }
                                     $PreviousLink .= "&amp;$i=".urlencode(str_replace(array("&", "+"), array("&amp;", "@@@"), $CurrentValue));
                                 }
                             }
                         }
                         else
                         {
                             // POST form
                             $PreviousLink = "$ProcessFormPage?Pg=$NoPage&amp;Ob=$OrderBy";
                             foreach($_POST as $i => $CurrentValue)
                             {
                                 if (is_array($CurrentValue))
                                 {
                                     // The value is an array
                                     $CurrentValue = implode("_", $CurrentValue);
                                 }

                                 $PreviousLink .= "&amp;$i=".urlencode(str_replace(array("&", "+"), array("&amp;", "@@@"), $CurrentValue));
                             }
                         }
                     }

                     if ($Page < ceil($NbRecords / $GLOBALS["CONF_RECORDS_PER_PAGE"]))
                     {
                         $NoPage = $Page + 1;

                         // We get the parameters of the GET form or the POST form
                         if (count($_POST) == 0)
                         {
                             if (count($_GET) == 0)
                             {
                                 // No form submitted
                                 $NextLink = "$ProcessFormPage?Pg=$NoPage&amp;Ob=$OrderBy";
                             }
                             else
                             {
                                 // GET form
                                 $NextLink = "$ProcessFormPage?";
                                 foreach($_GET as $i => $CurrentValue)
                                 {
                                     if ($i == "Pg")
                                     {
                                         $CurrentValue = $NoPage;
                                     }
                                     $NextLink .= "&amp;$i=".urlencode(str_replace(array("&", "+"), array("&amp;", "@@@"), $CurrentValue));
                                 }
                             }
                         }
                         else
                         {
                             // POST form
                             $NextLink = "$ProcessFormPage?Pg=$NoPage&amp;Ob=$OrderBy";
                             foreach($_POST as $i => $CurrentValue)
                             {
                                 if (is_array($CurrentValue))
                                 {
                                     // The value is an array
                                     $CurrentValue = implode("_", $CurrentValue);
                                 }

                                 $NextLink .= "&amp;$i=".urlencode(str_replace(array("&", "+"), array("&amp;", "@@@"), $CurrentValue));
                             }
                         }
                     }
                     else
                     {
                         $NextLink = '';
                     }

                     displayPreviousNext("&nbsp;".$GLOBALS["LANG_PREVIOUS"], $PreviousLink, $GLOBALS["LANG_NEXT"]."&nbsp;", $NextLink,
                                         '', $Page, ceil($NbRecords / $GLOBALS["CONF_RECORDS_PER_PAGE"]));

                     openParagraph('nbentriesfound');
                     echo $GLOBALS['LANG_NB_RECORDS_FOUND'].$NbRecords;
                     closeParagraph();
                 }
                 else
                 {
                     // No log event found
                     openParagraph('nbentriesfound');
                     echo $GLOBALS['LANG_NO_RECORD_FOUND'];
                     closeParagraph();
                 }
             }
         }
         else
         {
             // The supporter isn't allowed to view the list of support members
             openParagraph('ErrorMsg');
             echo $GLOBALS["LANG_ERROR_NOT_ALLOWED_TO_CREATE_OR_UPDATE"];
             closeParagraph();
         }
     }
     else
     {
         // The user isn't logged
         openParagraph('ErrorMsg');
         echo $GLOBALS["LANG_ERROR_NOT_LOGGED"];
         closeParagraph();
     }
 }
?>