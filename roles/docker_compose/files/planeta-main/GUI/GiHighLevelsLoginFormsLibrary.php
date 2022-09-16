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
 * Interface module : XHTML Graphic high level login/logout anf GDPR forms library
 *
 * @author Christophe Javouhey
 * @version 3.6
 * @since 2012-01-12
 */


/**
 * Display the login form with a login and a password in the current row of the table of the web page,
 * in the graphic interface in XHTML
 *
 * @author Christophe Javouhey
 * @version 1.1
 *     - 2014-02-25 : display an achor to go directly to content
 *
 * @since 2012-01-12
 */
 function displayPwdLoginForm()
 {
     // Display the table (frame) where the form will take place
     openFrame($GLOBALS['LANG_LOGIN_FRAME_TITLE']);

     // Open the temporary form
     openForm('FormTmp', 'post', '');

     // Display the fields
     echo "<table class=\"Form\">\n<tr>\n\t<td id=\"LoginForm\">".$GLOBALS['LANG_LOGIN_NAME'].' : ';
     insertInputField('sLogin', 'text', '25', '15', $GLOBALS['LANG_LOGIN_NAME_TIP'], '', FALSE, FALSE, "onkeypress=\"LoginEnterKey(event, '".$GLOBALS['LANG_ERROR_JS_LOGIN_NAME']."', '".$GLOBALS['LANG_ERROR_JS_PASSWORD']."')\"");
     echo "</td><td class=\"AowFormSpace\"></td><td>".$GLOBALS['LANG_PASSWORD'].' : ';
     insertInputField('sPassword', 'password', '25', '15', $GLOBALS['LANG_PASSWORD_TIP'], '', FALSE, FALSE, "onkeypress=\"LoginEnterKey(event, '".$GLOBALS['LANG_ERROR_JS_LOGIN_NAME']."', '".$GLOBALS['LANG_ERROR_JS_PASSWORD']."')\"");
     echo "</td>\n</tr>\n</table>\n";
     closeForm();
     displayBR(1);

     // Open the temporary form
     openForm('FormLogin', 'post', 'index.php', '', "VerificationIndexPage('".$GLOBALS['LANG_ERROR_JS_LOGIN_NAME']."', '".$GLOBALS['LANG_ERROR_JS_PASSWORD']."')");

     // Display the hidden fields
     insertInputField('hidEncLogin', 'hidden', '', '', '', '');
     insertInputField('hidEncPassword', 'hidden', '', '', '', '');

     // Display the buttons
     echo "<table class=\"validation\">\n<tr>\n\t<td>";
     insertInputField('bSubmit', 'submit', '', '', $GLOBALS['LANG_SUBMIT_BUTTON_TIP'], $GLOBALS['LANG_SUBMIT_BUTTON_CAPTION']);
     echo "</td><td class=\"FormSpaceBetweenButtons\"></td><td>";
     insertInputField('bReset', 'reset', '', '', $GLOBALS['LANG_RESET_BUTTON_TIP'], $GLOBALS['LANG_RESET_BUTTON_CAPTION']);
     echo "</td>\n</tr>\n</table>\n";
     closeForm();
     closeFrame();
 }


/**
 * Display the login form for OpenID in the current row of the table of the web page,
 * in the graphic interface in XHTML
 *
 * @author STNA/7SQ
 * @version 1.0
 * @since 2008-02-04
 */
 function displayOpenIDLoginForm()
 {
     // Display the table (frame) where the form will take place
     openFrame($GLOBALS['LANG_LOGIN_OPENID_FRAME_TITLE']);

     openForm('FormLoginOpenID', 'post', 'OpenIdTryAuth.php', '', "VerificationOpenIDIndexPage('".$GLOBALS['LANG_ERROR_LOGIN_OPENID']."')");

     // Display the fields
     echo "<table class=\"Form\">\n<tr>\n\t<td>".$GLOBALS['LANG_OPENID'].' : ';
     insertInputField('openid_identifier', 'text', '255', '60', $GLOBALS['LANG_OPENID_TIP'], '');
     echo "</td>\n</tr>\n</table>\n";
     displayBR(1);

     // Display the buttons
     echo "<table class=\"validation\">\n<tr>\n\t<td>";
     insertInputField('bSubmit', 'submit', '', '', $GLOBALS['LANG_SUBMIT_BUTTON_TIP'], $GLOBALS['LANG_SUBMIT_BUTTON_CAPTION']);
     echo "</td><td class=\"FormSpaceBetweenButtons\"></td><td>";
     insertInputField('bReset', 'reset', '', '', $GLOBALS['LANG_RESET_BUTTON_TIP'], $GLOBALS['LANG_RESET_BUTTON_CAPTION']);
     echo "</td>\n</tr>\n</table>\n";
     closeForm();
     closeFrame();
 }


/**
 * Display the diconnection form of the logged customer in the current row of the table of the web page,
 * in the graphic interface in XHTML
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2012-01-12
 */
 function displayLogout()
 {
     if (isSet($_SESSION['SupportMemberID']))
     {
         // It's a supporter session
         $UserInfos = $_SESSION['SupportMemberLastname'].' '.$_SESSION['SupportMemberFirstname'];
     }
     else
     {
         $UserInfos = '';
     }

     // Destroy the session
     $Result = session_destroy();

     if ($Result)
     {
         // The session is destroyed
         $ConfirmationCaption = $GLOBALS['LANG_CONFIRMATION'];

         if ($UserInfos == '')
         {
             $ConfirmationSentence = $GLOBALS['LANG_CONFIRM_DISCONNECTION_USER'].'.';
         }
         else
         {
             $ConfirmationSentence = $GLOBALS['LANG_CONFIRM_DISCONNECTION_USER'].", $UserInfos.";
         }
         $ConfirmationStyle = 'ConfirmationMsg';
     }
     else
     {
         // The session isn't destroyed
         $ConfirmationCaption = $GLOBALS['LANG_ERROR'];

         if ($UserInfos == '')
         {
             $ConfirmationSentence = $GLOBALS['LANG_ERROR_DISCONNECTION_USER']."!";
         }
         else
         {
             $ConfirmationSentence = $GLOBALS['LANG_ERROR_DISCONNECTION_USER'].", $UserInfos!";
         }
         $ConfirmationStyle = 'ErrorMsg';
     }

     openFrame($ConfirmationCaption);
     displayStyledText($ConfirmationSentence, $ConfirmationStyle);
     closeFrame();
 }


/**
 * Display the form to apply GDPR treatment on data of a supporter in the current row of the table of the web page,
 * in the graphic interface in XHTML
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-05-01
 *
 * @param $DbConnection         DB object             Object of the opened database connection
 * @param $SupportMemberID      Integer               ID or the supporter [1..n]
 * @param $AccessRules          Array of Integers     List used to select only some support members
 *                                                    allowed to apply GDPR on data of the supporter
 */
 function displayGDPRForm($DbConnection, $SupportMemberID, $AccessRules = array())
 {
     if ($SupportMemberID > 0)
     {
         // The supporter must be allowed to access to support members data
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

         // We chek if the support member is desactivated
         $SupportMemberRecord = getSupportMemberInfos($DbConnection, $SupportMemberID);

         if (in_array($cUserAccess, array(FCT_ACT_CREATE, FCT_ACT_UPDATE, FCT_ACT_READ_ONLY)))
         {
             // Open a form
             openForm("FormDetailsGDPRSupportMember", "post", "ProcessGDPRonSupportMember.php?".$GLOBALS["QUERY_STRING"], "",
                      "VerificationGDPR('".$GLOBALS["LANG_ERROR_MANDORY_FIELDS"]."')");

             // Display the table (frame) where the form will take place
             openStyledFrame($GLOBALS["LANG_GDPR"], "Frame", "Frame", "DetailsNews");

             $ArrayChoices = array(0 => $GLOBALS['LANG_NO'], 1 => $GLOBALS['LANG_YES']);

             // <<< Profile user data SELECTFIELD >>>
             switch($cUserAccess)
             {
                 case FCT_ACT_READ_ONLY:
                 case FCT_ACT_PARTIAL_READ_ONLY:
                     $sProfileUserData = "-";
                     break;

                 case FCT_ACT_CREATE:
                 case FCT_ACT_UPDATE:
                     $sProfileUserData = generateSelectField("lUserProfileGDPR", array_keys($ArrayChoices), array_values($ArrayChoices), 0);
                     break;
             }

             // <<< Events user data SELECTFIELD >>>
             switch($cUserAccess)
             {
                 case FCT_ACT_READ_ONLY:
                 case FCT_ACT_PARTIAL_READ_ONLY:
                     $sEventsUserData = "-";
                     break;

                 case FCT_ACT_CREATE:
                 case FCT_ACT_UPDATE:
                     $sEventsUserData = generateSelectField("lEventsGDPR", array_keys($ArrayChoices), array_values($ArrayChoices), 0);
                     break;
             }

             // <<< Forum user data SELECTFIELD >>>
             switch($cUserAccess)
             {
                 case FCT_ACT_READ_ONLY:
                 case FCT_ACT_PARTIAL_READ_ONLY:
                     $sForumUserData = "-";
                     break;

                 case FCT_ACT_CREATE:
                 case FCT_ACT_UPDATE:
                     $sForumUserData = generateSelectField("lForumGDPR", array_keys($ArrayChoices), array_values($ArrayChoices), 0);
                     break;
             }

             // Display the form
             echo "<table cellspacing=\"0\" cellpadding=\"0\">\n";

             if ($SupportMemberRecord['SupportMemberActivated'] == 0)
             {
                 // Displayed only for a desactivated supporter
                 echo "<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_USER_STATUS"]."</td><td class=\"Value\">$sProfileUserData</td>\n</tr>\n";
                 echo "<tr>\n\t<td class=\"Label\">".$GLOBALS['LANG_EVENTS']."</td><td class=\"Value\">$sEventsUserData</td>\n</tr>\n";
             }

             echo "<tr>\n\t<td class=\"Label\">".$GLOBALS['LANG_FORUM']."</td><td class=\"Value\">$sForumUserData</td>\n</tr>\n";
             echo "</table>\n";

             insertInputField("hidSupportMemberID", "hidden", "", "", "", $SupportMemberID);
             closeStyledFrame();

             switch($cUserAccess)
             {
                 case FCT_ACT_CREATE:
                 case FCT_ACT_UPDATE:
                     // We display the buttons
                     echo "<table class=\"validation\">\n<tr>\n\t<td>";
                     insertInputField("bSubmit", "submit", "", "", $GLOBALS["LANG_SUBMIT_BUTTON_TIP"], $GLOBALS["LANG_SUBMIT_BUTTON_CAPTION"]);
                     echo "</td><td class=\"FormSpaceBetweenButtons\"></td><td>";
                     insertInputField("bReset", "reset", "", "", $GLOBALS["LANG_RESET_BUTTON_TIP"], $GLOBALS["LANG_RESET_BUTTON_CAPTION"]);
                     echo "</td>\n</tr>\n</table>\n";
                     break;
             }

             closeForm();
         }
         else
         {
             // The supporter isn't allowed to apply GDPR
             openParagraph('ErrorMsg');
             echo $GLOBALS["LANG_ERROR_NOT_ALLOWED_TO_CREATE_OR_UPDATE"];
             closeParagraph();
         }
     }
     else
     {
         // The supporter isn't logged
         openParagraph('ErrorMsg');
         echo $GLOBALS["LANG_ERROR_NOT_LOGGED"];
         closeParagraph();
     }
 }


/**
 * Display the form about GDPRin the current web page, in the graphic interface in XHTML
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-05-05
 *
 * @param $DbConnection                DB object            Object of the opened database connection
 * @param $TabParams                   Array of Strings     search criterion used to find some data about GDPR
 * @param $ProcessFormPage             String               URL of the page which will process the form allowing to find and to sort
 *                                                          the table of the data about DGRP found
 * @param $Page                        Integer              Number of the Page to display [1..n]
 * @param $SortFct                     String               Javascript function used to sort the table
 * @param $OrderBy                     Integer              n° Criteria used to sort the data about GDPR. If < 0, DESC is used,
 *                                                          otherwise ASC is used
 * @param $AccessRules                 Array of Integers    List used to select only some support members
 *                                                          allowed to manage data about GDPR
 */
 function displaySearchGDPRDataForm($DbConnection, $TabParams, $ProcessFormPage, $Page = 1, $SortFct = '', $OrderBy = 0, $AccessRules = array())
 {
     if (isSet($_SESSION["SupportMemberID"]))
     {
         // The supporter must be allowed to manage data about GDPR
         $cUserAccess = FCT_ACT_NO_RIGHTS;
         $bCanDelete = FALSE;
         if ((isset($AccessRules[FCT_ACT_CREATE])) && (in_array($_SESSION["SupportMemberStateID"], $AccessRules[FCT_ACT_CREATE])))
         {
             // Write mode
             $cUserAccess = FCT_ACT_CREATE;
             $bCanDelete = TRUE;
         }
         elseif ((isset($AccessRules[FCT_ACT_UPDATE])) && (in_array($_SESSION["SupportMemberStateID"], $AccessRules[FCT_ACT_UPDATE])))
         {
             // Write mode
             $cUserAccess = FCT_ACT_UPDATE;
             $bCanDelete = TRUE;
         }
         elseif ((isset($AccessRules[FCT_ACT_READ_ONLY])) && (in_array($_SESSION["SupportMemberStateID"], $AccessRules[FCT_ACT_READ_ONLY])))
         {
             // Read mode
             $cUserAccess = FCT_ACT_READ_ONLY;
         }

         if (in_array($cUserAccess, array(FCT_ACT_CREATE, FCT_ACT_UPDATE)))
         {
             // Open a form
             openForm("FormSearchGDPR", "post", "$ProcessFormPage", "", "");

             // Display the table (frame) where the form will take place
             openStyledFrame($GLOBALS["LANG_GDPR"], "Frame", "Frame", "SearchFrame");


             // Display the form
             echo "<table id=\"GDPRList\" cellspacing=\"0\" cellpadding=\"0\">\n<tr>\n\t</tr>\n";
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

                 $ArrayCaptions = array($GLOBALS["LANG_REFERENCE"], $GLOBALS["LANG_TOTAL"], "");
                 $ArraySorts = array("TableName", "NB", "");
                 $ArraySortedFields = array("1", "2", "");

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
                     $StrOrderBy = "TableName";
                 }

                 // We launch the search
                 $NbRecords = getNbdbSearchGDPRData($DbConnection, $TabParams);
                 if ($NbRecords > 0)
                 {
                     // To get only data bout GDPR of the page
                     $ArrayRecords = dbSearchGDPRData($DbConnection, $TabParams, $StrOrderBy, $Page, $GLOBALS["CONF_RECORDS_PER_PAGE"]);

                     // There are some too old data about GDPR found
                     foreach($ArrayRecords["TableName"] as $i => $CurrentValue)
                     {
                         // We display the table name
                         $ArrayData[0][] = $CurrentValue;
                         $ArrayData[1][] = $ArrayRecords["NbRecords"][$i];

                         // Hyperlink to delete the data too old
                         $ArrayData[2][] = generateStyledPictureHyperlink($GLOBALS["CONF_DELETE_ICON"],
                                                                          "DeleteGDPRData.php?Cr=".md5($CurrentValue)."&amp;Id=$CurrentValue&amp;Return=$ProcessFormPage&amp;RCr=".md5($CurrentValue)."&amp;RId=$CurrentValue",
                                                                          $GLOBALS["LANG_DELETE"], 'Affectation');
                     }

                     // Display the table which contains the data about GDPR found
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
                     // No too old GDPR data found
                     openParagraph('nbentriesfound');
                     echo $GLOBALS['LANG_NO_RECORD_FOUND'];
                     closeParagraph();
                 }
             }
         }
         else
         {
             // The supporter isn't allowed to manage data about GDPR
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