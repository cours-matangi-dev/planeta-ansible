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
 * Interface module : XHTML Graphic high level forms library used to manage the forum categories, topics, messages
 * and subscribtions.
 *
 * @author Christophe Javouhey
 * @version 3.7
 * @since 2021-04-11
 */


/**
 * Display the forum categories a loggued supporter can access in the current web page, in the graphic interface in XHTML
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-11
 *
 * @param $DbConnection                DB object            Object of the opened database connection
 * @param $TabParams                   Array of Strings     search criterion used to find some forum categories
 * @param $ProcessFormPage             String               URL of the page which will process the form allowing to find and to sort
 *                                                          the table of the forum categories found
 * @param $Page                        Integer              Number of the Page to display [1..n]
 * @param $SortFct                     String               Javascript function used to sort the table
 * @param $OrderBy                     Integer              n° Criteria used to sort the forum categories. If < 0, DESC is used, otherwise ASC
 *                                                          is used
 * @param $DetailsPage                 String               URL of the page to display topics of a forum category
 * @param $AccessRules                 Array of Integers    List used to select only some support members
 *                                                          allowed to create or update topics and messages in forum categories
 */
 function displayForumCategoriesList($DbConnection, $TabParams, $ProcessFormPage, $Page = 1, $SortFct = '', $OrderBy = 0, $DetailsPage, $AccessRules = array())
 {
     if (isSet($_SESSION["SupportMemberID"]))
     {
         // The supporter must be allowed to access to forum categories list
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

         if (in_array($cUserAccess, array(FCT_ACT_CREATE, FCT_ACT_UPDATE, FCT_ACT_READ_ONLY)))
         {
             // Open a form
             openForm("FormForumCategories", "post", "$ProcessFormPage", "", "");
             insertInputField("hidOrderByField", "hidden", "", "", "", $OrderBy);
             closeForm();

             // The supporter has executed a search
             $NbTabParams = count($TabParams);
             if ($NbTabParams > 0)
             {
                 displayBR(2);

                 $ArrayCaptions = array($GLOBALS["LANG_FORUM_CATEGORIES"], $GLOBALS["LANG_FORUM_NB_MESSAGES"]);
                 $ArraySorts = array("ForumCategoryName","NbMessages");

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
                     $StrOrderBy = "ForumCategoryName ASC";
                 }

                 // We launch the search
                 $NbRecords = getNbdbSearchForumCategory($DbConnection, $TabParams);
                 if ($NbRecords > 0)
                 {
                     // To get only forum categories of the page
                     $ArrayRecords = dbSearchForumCategory($DbConnection, $TabParams, $StrOrderBy, $Page, $GLOBALS["CONF_RECORDS_PER_PAGE"]);

                     // There are some forum categories found
                     foreach($ArrayRecords["ForumCategoryID"] as $i => $CurrentValue)
                     {
                         if (empty($DetailsPage))
                         {
                             // We display the topics of the forum categoy
                             $sForumCategoryName = $ArrayRecords["ForumCategoryName"][$i];
                         }
                         else
                         {
                             // We display the forum category name with a hyperlink
                             $sForumCategoryName = generateAowIDHyperlink($ArrayRecords["ForumCategoryName"][$i], $ArrayRecords["ForumCategoryID"][$i],
                                                                          $DetailsPage, $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"], "", "");
                         }

                         // Display the description of the forum category
                         $ArrayData[0][] = $sForumCategoryName.generateBR(1).$ArrayRecords["ForumCategoryDescription"][$i];
                         $ArrayData[1][] = $ArrayRecords["NbMessages"][$i];
                     }

                     // Display the table which contains the forum categories found
                     $ArraySortedFields = array("1", "2");
                     displayStyledTable($ArrayCaptions, $ArraySortedFields, $SortFct, $ArrayData, '', '', '', '',
                                        array(), $OrderBy, array('ForumCategoryName', ''), 'ForumsList');

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
                     // No forum category found
                     openParagraph('nbentriesfound');
                     echo $GLOBALS['LANG_NO_RECORD_FOUND'];
                     closeParagraph();
                 }
             }
         }
         else
         {
             // The supporter isn't allowed to view the list of forum categories
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


/**
 * Display the form to search a forum category in the current web page, in the graphic interface in XHTML
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-30
 *
 * @param $DbConnection                DB object            Object of the opened database connection
 * @param $TabParams                   Array of Strings     search criterion used to find some forum categories
 * @param $ProcessFormPage             String               URL of the page which will process the form allowing to find and to sort
 *                                                          the table of the forum categories found
 * @param $Page                        Integer              Number of the Page to display [1..n]
 * @param $SortFct                     String               Javascript function used to sort the table
 * @param $OrderBy                     Integer              n° Criteria used to sort the forum categories. If < 0, DESC is used,
 *                                                          otherwise ASC is used
 * @param $DetailsPage                 String               URL of the page to display details about a forum category.
 *                                                          This string can be empty
 * @param $AccessRules                 Array of Integers    List used to select only some support members
 *                                                          allowed to create or update forum categories
 */
 function displaySearchForumCategoriesForm($DbConnection, $TabParams, $ProcessFormPage, $Page = 1, $SortFct = '', $OrderBy = 0, $DetailsPage = '', $AccessRules = array())
 {
     if (isSet($_SESSION["SupportMemberID"]))
     {
         // The supporter must be allowed to access to forum categories list
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

         if (in_array($cUserAccess, array(FCT_ACT_CREATE, FCT_ACT_UPDATE, FCT_ACT_READ_ONLY)))
         {
             // Open a form
             openForm("FormSearchForumCategory", "post", "$ProcessFormPage", "", "");

             // Display the table (frame) where the form will take place
             openStyledFrame($GLOBALS["LANG_SEARCH"], "Frame", "Frame", "SearchFrame");


             // Display the form
             echo "<table id=\"ForumCategoriesList\" cellspacing=\"0\" cellpadding=\"0\">\n<tr>\n\t</tr>\n";
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

                 $ArrayCaptions = array($GLOBALS["LANG_REFERENCE"], $GLOBALS["LANG_FORUM_CATEGORY_NAME"], $GLOBALS['LANG_FORUM_CATEGORY_DESCRIPTION'],
                                        $GLOBALS['LANG_FORUM_CATEGORY_DEFAULT_LANG']);
                 $ArraySorts = array("ForumCategoryID", "ForumCategoryName", "ForumCategoryDescription", "ForumCategoryDefaultLang");

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
                     $StrOrderBy = "ForumCategoryID";
                 }

                 // We launch the search
                 $NbRecords = getNbdbSearchForumCategory($DbConnection, $TabParams);
                 if ($NbRecords > 0)
                 {
                     // To get only forum categories of the page
                     $ArrayRecords = dbSearchForumCategory($DbConnection, $TabParams, $StrOrderBy, $Page, $GLOBALS["CONF_RECORDS_PER_PAGE"]);

                     // There are some forum categories found
                     foreach($ArrayRecords["ForumCategoryID"] as $i => $CurrentValue)
                     {
                         if (empty($DetailsPage))
                         {
                             // We display the forum category ID
                             $ArrayData[0][] = $ArrayRecords["ForumCategoryID"][$i];
                         }
                         else
                         {
                             // We display the forum category ID  with a hyperlink
                             $ArrayData[0][] = generateAowIDHyperlink($ArrayRecords["ForumCategoryID"][$i],
                                                                      $ArrayRecords["ForumCategoryID"][$i],
                                                                      $DetailsPage, $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"],
                                                                      "", "_blank");
                         }

                         $ArrayData[1][] = $ArrayRecords["ForumCategoryName"][$i];
                         $ArrayData[2][] = $ArrayRecords["ForumCategoryDescription"][$i];
                         $ArrayData[3][] = $ArrayRecords["ForumCategoryDefaultLang"][$i];

                         // Hyperlink to delete the forum category if allowed
                         if ($bCanDelete)
                         {
                             $ArrayData[4][] = generateStyledPictureHyperlink($GLOBALS["CONF_DELETE_ICON"],
                                                                              "DeleteForumCategory.php?Cr=".md5($CurrentValue)."&amp;Id=$CurrentValue&amp;Return=$ProcessFormPage&amp;RCr=".md5($CurrentValue)."&amp;RId=$CurrentValue",
                                                                              $GLOBALS["LANG_DELETE"], 'Affectation');
                         }
                     }

                     // Display the table which contains the forum categories found
                     $ArraySortedFields = array("1", "2", "3", "4");
                     if ($bCanDelete)
                     {
                         $ArrayCaptions[] = "";
                         $ArraySorts[] = "";
                         $ArraySortedFields[] = "";
                     }

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
                     // No forum category found
                     openParagraph('nbentriesfound');
                     echo $GLOBALS['LANG_NO_RECORD_FOUND'];
                     closeParagraph();
                 }
             }
         }
         else
         {
             // The supporter isn't allowed to view the list of forum categories
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


/**
 * Display the form to submit a new forum category or update a forum category, in the current row
 * of the table of the web page, in the graphic interface in XHTML
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-30
 *
 * @param $DbConnection             DB object             Object of the opened database connection
 * @param $ForumCategoryID          String                ID of the forum category to display
 * @param $ProcessFormPage          String                URL of the page which will process the form
 * @param $AccessRules              Array of Integers     List used to select only some support members
 *                                                        allowed to create, update or view forum categories
 */
 function displayDetailsForumCategoryForm($DbConnection, $ForumCategoryID, $ProcessFormPage, $AccessRules = array())
 {
     // The supporter must be logged,
     if (isSet($_SESSION["SupportMemberID"]))
     {
         // The supporter must be allowed to create or update a forum category
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

         if (in_array($cUserAccess, array(FCT_ACT_CREATE, FCT_ACT_UPDATE, FCT_ACT_READ_ONLY)))
         {
             // Open a form
             openForm("FormDetailsForumCategory", "post", "$ProcessFormPage?".$GLOBALS["QUERY_STRING"], "",
                      "VerificationForumCategory('".$GLOBALS["LANG_ERROR_MANDORY_FIELDS"]."')");

             // <<< ForumCategoryID >>>
             if ($ForumCategoryID == 0)
             {
                 // Define default values to create the new forum category
                 $Reference = "";
                 $ForumCategoryRecord = array(
                                              "ForumCategoryName" => '',
                                              "ForumCategoryDescription" => '',
                                              "ForumCategoryDefaultLang" => $GLOBALS['CONF_LANG']
                                             );
             }
             else
             {
                 if (isExistingForumCategory($DbConnection, $ForumCategoryID))
                 {
                     // We get the details of the forum category
                     $ForumCategoryRecord = getTableRecordInfos($DbConnection, "ForumCategories", $ForumCategoryID);
                     $Reference = $ForumCategoryID;
                 }
                 else
                 {
                     // Error, the forum category doesn't exist
                     $ForumCategoryID = 0;
                     $Reference = "";
                 }
             }

             // Display the table (frame) where the form will take place
             $FrameTitle = $GLOBALS["LANG_FORUM_CATEGORY"];
             if (!empty($ForumCategoryID))
             {
                 $FrameTitle .= " ($Reference)";
             }

             openStyledFrame($FrameTitle, "Frame", "Frame", "DetailsNews");

             // <<< ForumCategoryName INPUTFIELD >>>
             switch($cUserAccess)
             {
                 case FCT_ACT_READ_ONLY:
                 case FCT_ACT_PARTIAL_READ_ONLY:
                     $sCategoryName = stripslashes($ForumCategoryRecord["ForumCategoryName"]);
                     break;

                 case FCT_ACT_CREATE:
                 case FCT_ACT_UPDATE:
                     $sCategoryName = generateInputField("sCategoryName", "text", "50", "25", $GLOBALS['LANG_FORUM_CATEGORY_NAME_TIP'],
                                                         $ForumCategoryRecord["ForumCategoryName"]);
                     break;
             }

             // <<< ForumCategoryDefaultLang INPUTFIELD >>>
             switch($cUserAccess)
             {
                 case FCT_ACT_READ_ONLY:
                 case FCT_ACT_PARTIAL_READ_ONLY:
                     $sCategoryLang = $ForumCategoryRecord["ForumCategoryDefaultLang"];
                     break;

                 case FCT_ACT_CREATE:
                 case FCT_ACT_UPDATE:
                     $sCategoryLang = generateInputField("sCategoryLang", "text", "2", "2", $GLOBALS['LANG_FORUM_CATEGORY_DEFAULT_LANG_TIP'],
                                                         $ForumCategoryRecord["ForumCategoryDefaultLang"]);
                     break;
             }

             // <<< ForumCategoryDescription INPUTFIELD >>>
             switch($cUserAccess)
             {
                 case FCT_ACT_READ_ONLY:
                 case FCT_ACT_PARTIAL_READ_ONLY:
                     $sDescription = nullFormatText(stripslashes($ForumCategoryRecord["ForumCategoryDescription"]));
                     break;

                 case FCT_ACT_CREATE:
                 case FCT_ACT_UPDATE:
                     $sDescription = generateInputField("sDescription", "text", "255", "50", $GLOBALS['LANG_FORUM_CATEGORY_DESCRIPTION_TIP'],
                                                        $ForumCategoryRecord["ForumCategoryDescription"]);
                     break;
             }

             // Display the form
             echo "<table cellspacing=\"0\" cellpadding=\"0\">\n<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_FORUM_CATEGORY_NAME"]."*</td><td class=\"Value\">$sCategoryName</td>\n</tr>\n";
             echo "<tr>\n\t<td class=\"Label\">".$GLOBALS['LANG_FORUM_CATEGORY_DEFAULT_LANG']."*</td><td class=\"Value\">$sCategoryLang</td>\n</tr>\n";
             echo "<tr>\n\t<td class=\"Label\">".$GLOBALS['LANG_FORUM_CATEGORY_DESCRIPTION']."</td><td class=\"Value\">$sDescription</td>\n</tr>\n";
             echo "</table>\n";

             insertInputField("hidForumCategoryID", "hidden", "", "", "", $ForumCategoryID);
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

             // Display access rights of support member states to the categry
             if ($ForumCategoryID > 0)
             {
                 displayBR(1);

                 // Display the button ton add a new access
                 openParagraph('toolbar');
                 echo generateStyledPictureHyperlink($GLOBALS["CONF_ADD_ICON"], "../Admin/AddForumCategoryAccess.php?Cr=".md5($ForumCategoryID)."&amp;Id=$ForumCategoryID",
                                                     $GLOBALS['LANG_SUPPORT_ADMIN_UPDATE_FORUM_CATEGORY_PAGE_ADD_ACCESS'].".", 'Affectation', '_blank')
                      ." ".$GLOBALS['LANG_SUPPORT_ADMIN_UPDATE_FORUM_CATEGORY_PAGE_ADD_ACCESS'].".";
                 closeParagraph();

                 // Get access rights of the category
                 $ArrayCategoryAccess = getForumCategoryAccess($DbConnection, array($ForumCategoryID), array(), array(),
                                                               'ForumCategoryID, SupportMemberStateID');

                 if ((isset($ArrayCategoryAccess['ForumCategoryAccessID'])) && (!empty($ArrayCategoryAccess['ForumCategoryAccessID'])))
                 {
                     // The category has access rihgts for support member states
                     $ArrayCaptions = array($GLOBALS["LANG_REFERENCE"], $GLOBALS["LANG_USER_STATUS"], $GLOBALS['LANG_ACCESS'], "");
                     $ArraySorts = array("ForumCategoryAccessID", "SupportMemberStateID", "ForumCategoryAccess", "");

                     foreach($ArrayCategoryAccess['ForumCategoryAccessID'] as $a => $CurrentAccessID)
                     {
                         $ArrayData[0][] = generateAowIDHyperlink($CurrentAccessID, $CurrentAccessID,
                                                                  "UpdateForumCategoryAccess.php", $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"],
                                                                  "", "_blank");

                         $ArrayData[1][] = getSupportMemberStateName($DbConnection, $ArrayCategoryAccess['SupportMemberStateID'][$a]);
                         $ArrayData[2][] = $ArrayCategoryAccess['ForumCategoryAccess'][$a];

                         // Button to delete access
                         $ArrayData[3][] = generateStyledPictureHyperlink($GLOBALS["CONF_DELETE_ICON"],
                                                                          "DeleteForumCategoryAccess.php?Cr=".md5($CurrentAccessID)."&amp;Id=$CurrentAccessID",
                                                                          $GLOBALS["LANG_DELETE"], 'Affectation');
                     }

                     $ArraySortedFields = array("", "", "", "");
                     displayStyledTable($ArrayCaptions, $ArraySortedFields, '', $ArrayData, '', '', '', '', array(), '', array());
                 }
             }
         }
         else
         {
             // The supporter isn't allowed to create or update a forum category
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
 * Display the form to submit a new forum category access or update a forum category access, in the current row
 * of the table of the web page, in the graphic interface in XHTML
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-30
 *
 * @param $DbConnection             DB object             Object of the opened database connection
 * @param $ForumCategoryAccessID    String                ID of the forum category access to display
 * @param $ProcessFormPage          String                URL of the page which will process the form
 * @param $AccessRules              Array of Integers     List used to select only some support members
 *                                                        allowed to create, update or view forum category access
 */
 function displayDetailsForumCategoryAccessForm($DbConnection, $ForumCategoryAccessID, $ForumCategoryID, $ProcessFormPage, $AccessRules = array())
 {
     // The supporter must be logged,
     if (isSet($_SESSION["SupportMemberID"]))
     {
         // The supporter must be allowed to create or update a forum category access
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

         if (in_array($cUserAccess, array(FCT_ACT_CREATE, FCT_ACT_UPDATE, FCT_ACT_READ_ONLY)))
         {
             // Open a form
             openForm("FormDetailsForumCategoryAccess", "post", "$ProcessFormPage?".$GLOBALS["QUERY_STRING"], "",
                      "VerificationForumCategoryAccess('".$GLOBALS["LANG_ERROR_MANDORY_FIELDS"]."')");

             // <<< ForumCategoryAccessID >>>
             if ($ForumCategoryAccessID == 0)
             {
                 // Define default values to create the new forum category access
                 $Reference = "";
                 $ForumCategoryAccessRecord = array(
                                                    "ForumCategoryAccess" => FORUM_ACCESS_READ_MSG,
                                                    "SupportMemberStateID" => 0,
                                                    "ForumCategoryID" => $ForumCategoryID
                                                   );
             }
             else
             {
                 if (isExistingForumCategoryAccess($DbConnection, $ForumCategoryAccessID))
                 {
                     // We get the details of the forum category access
                     $ForumCategoryAccessRecord = getTableRecordInfos($DbConnection, "ForumCategoriesAccess", $ForumCategoryAccessID);
                     $Reference = $ForumCategoryAccessID;
                 }
                 else
                 {
                     // Error, the forum category access doesn't exist
                     $ForumCategoryAccessID = 0;
                     $Reference = "";
                 }
             }

             // Display the table (frame) where the form will take place
             $FrameTitle = $GLOBALS["LANG_ACCESS"];
             if (!empty($ForumCategoryAccessID))
             {
                 $FrameTitle .= " ($Reference)";
             }

             openStyledFrame($FrameTitle, "Frame", "Frame", "DetailsNews");

             // <<< SupportMemberStateID SELECTFIELD >>>
             switch($cUserAccess)
             {
                 case FCT_ACT_READ_ONLY:
                 case FCT_ACT_PARTIAL_READ_ONLY:
                     $sStatusName = getSupportMemberStateName($DbConnection, $ForumCategoryAccessRecord['SupportMemberStateID']);
                     break;

                 case FCT_ACT_CREATE:
                 case FCT_ACT_UPDATE:
                     // List of support members states
                     $ArraySupportMembersStates = getTableContent($DbConnection, 'SupportMembersStates', 'SupportMemberStateID');
                     $ArrayStateID = array();
                     $ArrayStateName = array();

                     if (empty($ForumCategoryAccessID))
                     {
                         $ArrayStateID[] = 0;
                         $ArrayStateName[] = "-";
                     }

                     if (isset($ArraySupportMembersStates['SupportMemberStateID']))
                     {
                         $ArrayStateID = array_merge($ArrayStateID, $ArraySupportMembersStates['SupportMemberStateID']);
                         $ArrayStateName = array_merge($ArrayStateName, $ArraySupportMembersStates['SupportMemberStateName']);

                         $sStatusName = generateSelectField("lSupportMemberStateID", $ArrayStateID, $ArrayStateName,
                                                            $ForumCategoryAccessRecord['SupportMemberStateID']);
                     }
                     break;
             }

             // <<< ForumCategoryAccess SELECTFIELD >>>
             switch($cUserAccess)
             {
                 case FCT_ACT_READ_ONLY:
                 case FCT_ACT_PARTIAL_READ_ONLY:
                     $sCategoryAccess = $ForumCategoryAccessRecord["ForumCategoryAccess"];
                     break;

                 case FCT_ACT_CREATE:
                 case FCT_ACT_UPDATE:
                     $ArrayAccess = array(FORUM_ACCESS_READ_MSG, FORUM_ACCESS_WRITE_MSG, FORUM_ACCESS_CREATE_TOPIC);
                     $sCategoryAccess = generateSelectField("lAccess", $ArrayAccess, $ArrayAccess,
                                                            $ForumCategoryAccessRecord['ForumCategoryAccess']);
                     break;
             }

             // Display the form
             echo "<table cellspacing=\"0\" cellpadding=\"0\">\n<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_USER_STATUS"]."*</td><td class=\"Value\">$sStatusName</td>\n</tr>\n";
             echo "<tr>\n\t<td class=\"Label\">".$GLOBALS['LANG_ACCESS']."*</td><td class=\"Value\">$sCategoryAccess</td>\n</tr>\n";
             echo "</table>\n";

             insertInputField("hidForumCategoryAccessID", "hidden", "", "", "", $ForumCategoryAccessID);
             insertInputField("hidForumCategoryID", "hidden", "", "", "", $ForumCategoryID);
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
             // The supporter isn't allowed to create or update a forum category access
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
 * Display the topics of a forum category a loggued supporter in the current web page, in the graphic interface in XHTML
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-11
 *
 * @param $DbConnection                DB object            Object of the opened database connection
 * @param $TabParams                   Array of Strings     search criterion used to find some forum topics
 * @param $ProcessFormPage             String               URL of the page which will process the form allowing to find and to sort
 *                                                          the table of the forum topics found
 * @param $Page                        Integer              Number of the Page to display [1..n]
 * @param $SortFct                     String               Javascript function used to sort the table
 * @param $OrderBy                     Integer              n° Criteria used to sort the forum topics. If < 0, DESC is used, otherwise ASC
 *                                                          is used
 * @param $DetailsPage                 String               URL of the page to display topics of a forum category
 * @param $AccessRules                 Array of Integers    List used to select only some support members
 *                                                          allowed to create or update topics and messages in forum category
 */
 function displayForumCategoryTopicsList($DbConnection, $TabParams, $ProcessFormPage, $Page = 1, $SortFct = '', $OrderBy = 0, $DetailsPage, $AccessRules = array())
 {
     if (isSet($_SESSION["SupportMemberID"]))
     {
         // The supporter must be allowed to access to forum topics list
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

         if (in_array($cUserAccess, array(FCT_ACT_CREATE, FCT_ACT_UPDATE, FCT_ACT_READ_ONLY)))
         {
             // We get forum category access of the loggued supporter
             $ForumCategoryID = $TabParams['ForumCategoryID'][0];
             $SupportMemberStateID = $_SESSION["SupportMemberStateID"];
             $ArraySupportMemberAccess = getForumCategoryAccess($DbConnection, $ForumCategoryID, array(),
                                                                $SupportMemberStateID, 'ForumCategoryID, SupportMemberStateID');

             $cUserForumAccess = FORUM_ACCESS_NO_ACCESS;
             if ((isset($ArraySupportMemberAccess['ForumCategoryID'])) && (!empty($ArraySupportMemberAccess['ForumCategoryID'])))
             {
                 $cUserForumAccess = $ArraySupportMemberAccess['ForumCategoryAccess'][0];
             }

             if ($cUserForumAccess == FORUM_ACCESS_CREATE_TOPIC)
             {
                 // The supporter can create topics in this forum category
                 // Display a button to create a new topic in this forum category
                 openParagraph('ForumButton');
                 echo generateStyledPictureHyperlink($GLOBALS["CONF_FORUM_ICONS"]['CreateTopic'],
                                                     "CreateForumTopic.php?Cr=".md5($ForumCategoryID)."&amp;Id=$ForumCategoryID",
                                                     $GLOBALS["LANG_SUPPORT_FORUM_TOPICS_LIST_PAGE_CREATE_TOPIC_TIP"], 'Affectation', '');
                 closeParagraph();
             }
             else
             {
                 displayBR(2);
             }

             // Open a form
             openForm("FormForumTopics", "post", "$ProcessFormPage", "", "");
             insertInputField("hidOrderByField", "hidden", "", "", "", $OrderBy);
             closeForm();

             // The supporter has executed a search
             $NbTabParams = count($TabParams);
             if ($NbTabParams > 0)
             {
                 $ArrayCaptions = array("", "", $GLOBALS["LANG_FORUM_TOPIC_TITLE"], $GLOBALS["LANG_FORUM_TOPIC_NB_PAGES"], $GLOBALS["LANG_FORUM_TOPIC_AUTHOR"],
                                        $GLOBALS["LANG_FORUM_TOPIC_NB_VIEWS"], $GLOBALS["LANG_FORUM_TOPIC_NB_ANSWERS"],
                                        $GLOBALS["LANG_FORUM_TOPIC_LAST_MESSAGE_DATE"]);
                 $ArraySorts = array("", "", "", "", "", "", "", "");

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
                     $StrOrderBy = "TopicPos";
                 }

                 // We launch the search
                 $NbRecords = getNbdbSearchForumTopic($DbConnection, $TabParams);
                 if ($NbRecords > 0)
                 {
                     // To get only forum topics of the page for the given forum category
                     $ArrayRecords = dbSearchForumTopic($DbConnection, $TabParams, $StrOrderBy, $Page, $GLOBALS["CONF_RECORDS_PER_PAGE"]);

                     // Get topics flags of the loggued supporter
                     $ArrayTopicsReadFlags = getForumTopicsLastReads($DbConnection,
                                                                     array(
                                                                           'ForumTopicID' => $ArrayRecords["ForumTopicID"],
                                                                           'SupportMemberID' => $_SESSION['SupportMemberID']
                                                                          ));

                     // Get topics subscribtions of the loggued supporter
                     $ArrayTopicsSubscribtions = getForumTopicsSubscribtions($DbConnection,
                                                                             array(
                                                                                   'ForumTopicID' => $ArrayRecords["ForumTopicID"],
                                                                                   'SupportMemberID' => $_SESSION['SupportMemberID']
                                                                                  ));

                     // There are some forum topics found
                     foreach($ArrayRecords["ForumTopicID"] as $i => $CurrentValue)
                     {
                         // The supporter has read this topic ? New answers ?
                         $TopicReadIcon = generateStyledPicture($GLOBALS["CONF_FORUM_ICONS"]["TopicNotRead"],
                                                                $GLOBALS['LANG_FORUM_TOPIC_NOT_READ_TIP']);

                         if (isset($ArrayTopicsReadFlags[$CurrentValue]))
                         {
                             // The supporter has read some messages of the topic
                             if ($ArrayTopicsReadFlags[$CurrentValue]['IsRead'][0])
                             {
                                 // The supporter has read all messages
                                 $TopicReadIcon = generateStyledPicture($GLOBALS["CONF_FORUM_ICONS"]["TopicReadWithoutNewAnswer"],
                                                                        $GLOBALS['LANG_FORUM_TOPIC_READ_TIP']);
                             }
                             else
                             {
                                 // We get the position of the last read message in the topic
                                 $iMsgPosInTopic = getForumTopicMessagePosInTopic($DbConnection,
                                                                                  $ArrayTopicsReadFlags[$CurrentValue]['ForumTopicLastReadMessageID'][0]);
                                 $iPageNum = ceil($iMsgPosInTopic / $GLOBALS["CONF_RECORDS_PER_PAGE"]);
                                 $TopicReadIcon = generateStyledPictureHyperlink($GLOBALS["CONF_FORUM_ICONS"]["TopicWithNewAnswerNotRead"],
                                                                                 "$DetailsPage?Pg=$iPageNum&amp;Cr=".md5($CurrentValue)
                                                                                 ."&amp;Id=$CurrentValue#Msg".$ArrayTopicsReadFlags[$CurrentValue]['ForumTopicLastReadMessageID'][0],
                                                                                 $GLOBALS['LANG_FORUM_TOPIC_READ_WITH_NEW_ANSWERS_TIP'], '', '');
                             }
                         }

                         $ArrayData[0][] = $TopicReadIcon;

                         // Icon of the topic
                         $ArrayData[1][] = generateStyledPicture($GLOBALS["CONF_FORUM_ICONS"]['TopicIcons'][$ArrayRecords["ForumTopicIcon"][$i]]);

                         // Check if the topic is "always on top"
                         $sForumTopicTitle = "";
                         if (!empty($ArrayRecords["ForumTopicRank"][$i]))
                         {
                             $sForumTopicTitle .= generateStyledPicture($GLOBALS["CONF_FORUM_ICONS"]['TopicAlwaysOnTop']);
                         }

                         // Check if the topic isn't opened for all supporters
                         if (!empty($GLOBALS["CONF_FORUM_ICONS"]['TopicStatus'][$ArrayRecords["ForumTopicStatus"][$i]]))
                         {
                             $sTip = '';
                             switch($ArrayRecords["ForumTopicStatus"][$i])
                             {
                                 case FORUM_TOPIC_AUTHOR_ONLY:
                                     $sTip = $GLOBALS['LANG_FORUM_TOPIC_STATUS_EDITABLE_ONLY_BY_AUTHOR'].'.';
                                     break;

                                 case FORUM_TOPIC_CLOSED:
                                     $sTip = $GLOBALS['LANG_FORUM_TOPIC_STATUS_CLOSED'].'.';
                                     break;
                             }

                             $sForumTopicTitle .= generateStyledPicture($GLOBALS["CONF_FORUM_ICONS"]['TopicStatus'][$ArrayRecords["ForumTopicStatus"][$i]],
                                                                        $sTip);
                         }

                         // Check if the topic is a temporary topic
                         if (!empty($ArrayRecords["ForumTopicExpirationDate"][$i]))
                         {
                             // We display the expiration date in tooltip
                             $sForumTopicTitle .= generateStyledPicture($GLOBALS["CONF_FORUM_ICONS"]['TemporaryTopic'],
                                                                        $GLOBALS['LANG_FORUM_TEMPORARY_TOPIC_INFOS']
                                                                        .' '.date($GLOBALS["CONF_DATE_DISPLAY_FORMAT"],
                                                                                  strtotime($ArrayRecords["ForumTopicExpirationDate"][$i])));
                         }

                         if (empty($DetailsPage))
                         {
                             // We display the topic title only
                             $sForumTopicTitle .= $ArrayRecords["ForumTopicTitle"][$i];
                         }
                         else
                         {
                             // We display the forum topic title with a hyperlink
                             $sForumTopicTitle .= generateAowIDHyperlink($ArrayRecords["ForumTopicTitle"][$i], $CurrentValue,
                                                                         $DetailsPage, $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"], "", "");
                         }

                         $ArrayData[2][] = $sForumTopicTitle;

                         // Compute the number of pages of the topic
                         $iTopicNbPages = ceil($ArrayRecords["ForumTopicNbAnswers"][$i] / $GLOBALS["CONF_RECORDS_PER_PAGE"]);
                         $sLastPageUrl = "$DetailsPage?Pg=$iTopicNbPages&amp;Cr=".md5($CurrentValue)."&amp;Id=$CurrentValue";
                         $ArrayData[3][] = generateStyledLinkText($iTopicNbPages, $sLastPageUrl, '', $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"], '');

                         // Author of the topic
                         $ArrayData[4][] = $ArrayRecords["SupportMemberLastname"][$i].' '.$ArrayRecords["SupportMemberFirstname"][$i]
                                           .generateBR(1).' ('.$ArrayRecords["SupportMemberStateName"][$i].')';

                         // Stats nb views / nb answers
                         $ArrayData[5][] = $ArrayRecords["ForumTopicNbViews"][$i];
                         $ArrayData[6][] = $ArrayRecords["ForumTopicNbAnswers"][$i];

                         // Last posted message date and author
                         $RecordAuthorTopicLastMessage = getForumMessageAuthorInfos($DbConnection, $ArrayRecords["ForumMessageID"][$i]);
                         $sLastMessageDate = date($GLOBALS["CONF_DATE_DISPLAY_FORMAT"].' '.$GLOBALS["CONF_TIME_DISPLAY_FORMAT"],
                                                  strtotime($ArrayRecords["LastTopicForumMessageDate"][$i]))
                                             .generateBR(1).$RecordAuthorTopicLastMessage['SupportMemberLastname']
                                             .' '.$RecordAuthorTopicLastMessage['SupportMemberFirstname']
                                             .generateBR(1).'('.$RecordAuthorTopicLastMessage['SupportMemberStateName'].')';

                         // We check if the loggued supporter has a subscribtion for this topic
                         if (isset($ArrayTopicsSubscribtions[$CurrentValue]))
                         {
                             // There is a subscribtion
                             $sLastMessageDate .= ' '.generateStyledPicture($GLOBALS["CONF_FORUM_ICONS"]['SubscribedToTopic'],
                                                                            $GLOBALS['LANG_FORUM_TOPIC_SUBSCRIBED'].' : '
                                                                            .implode(', ', $ArrayTopicsSubscribtions[$CurrentValue]['ForumTopicSubscribtionEmail']), '');
                         }

                         $ArrayData[7][] = $sLastMessageDate;
                     }

                     // Display the table which contains the forum topics found
                     $ArraySortedFields = array("", "", "", "", "", "", "", "");
                     displayStyledTable($ArrayCaptions, $ArraySortedFields, $SortFct, $ArrayData, '', '', '', '',
                                        array(), $OrderBy, array('ForumTopicReadFlag', '', 'ForumTopicTitle', '', '', '', '', 'ForumTopicLastMessage'),
                                        'TopicsList');

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
                             elseif(!isset($_GET['Pg']))
                             {
                                 // No form submitted
                                 $PreviousLink = "$ProcessFormPage?Pg=$NoPage&amp;Ob=$OrderBy&amp;Cr=".md5($ForumCategoryID)."&amp;Id=$ForumCategoryID";
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
                             elseif(!isset($_GET['Pg']))
                             {
                                 // No form submitted
                                 $NextLink = "$ProcessFormPage?Pg=$NoPage&amp;Ob=$OrderBy&amp;Cr=".md5($ForumCategoryID)."&amp;Id=$ForumCategoryID";
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

                     // Display the legends of the icons
                     displayBR(1);

                     $ArrayLegendsOfVisualIndicators = array(
                                                             array($GLOBALS["CONF_FORUM_ICONS"]["TopicNotRead"],
                                                                   $GLOBALS['LANG_FORUM_TOPIC_NOT_READ_TIP']),
                                                             array($GLOBALS["CONF_FORUM_ICONS"]["TopicReadWithoutNewAnswer"],
                                                                   $GLOBALS['LANG_FORUM_TOPIC_READ_TIP']),
                                                             array($GLOBALS["CONF_FORUM_ICONS"]["TopicWithNewAnswerNotRead"],
                                                                   $GLOBALS['LANG_FORUM_TOPIC_READ_WITH_NEW_ANSWERS_TIP']),
                                                             array($GLOBALS["CONF_FORUM_ICONS"]['TopicStatus'][FORUM_TOPIC_AUTHOR_ONLY],
                                                                   $GLOBALS["LANG_FORUM_TOPIC_STATUS_EDITABLE_ONLY_BY_AUTHOR"].'.'),
                                                             array($GLOBALS["CONF_FORUM_ICONS"]['TopicStatus'][FORUM_TOPIC_CLOSED],
                                                                   $GLOBALS["LANG_FORUM_TOPIC_STATUS_CLOSED"].'.'),
                                                             array($GLOBALS["CONF_FORUM_ICONS"]['TemporaryTopic'],
                                                                   $GLOBALS["LANG_FORUM_TEMPORARY_TOPIC"].'.'),
                                                             array($GLOBALS["CONF_FORUM_ICONS"]['SubscribedToTopic'],
                                                                   $GLOBALS["LANG_FORUM_TOPIC_SUBSCRIBED"].'.')
                                                            );

                     echo generateLegendsOfVisualIndicators($ArrayLegendsOfVisualIndicators, ICON);
                 }
                 else
                 {
                     // No forum topic found
                     openParagraph('nbentriesfound');
                     echo $GLOBALS['LANG_NO_RECORD_FOUND'];
                     closeParagraph();
                 }
             }
         }
         else
         {
             // The supporter isn't allowed to view the list of forum topics
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


/**
 * Display the form to create a new forum topic in a given forum category or to update an existing topic,
 * in the current row of the table of the web page, in the graphic interface in XHTML
 *
 * @author Christophe Javouhey
 * @version 1.1
 *     - 2022-02-07 : add tooltips on editor icons
 *
 * @since 2021-04-15
 *
 * @param $DbConnection                 DB object             Object of the opened database connection
 * @param $ForumTopicID                 String                ID of the forum topic to update [0..n]
 * @param $ForumCategoryID              String                ID of the forum category in which the new topic will be created [1..n]
 * @param $ProcessFormPage              String                URL of the page which will process the form
 * @param $AccessRules                  Array of Integers     List used to select only some support members
 *                                                            allowed to create a new forum topic in the given category
 */
 function displayForumTopicForm($DbConnection, $ForumTopicID, $ForumCategoryID, $ProcessFormPage, $AccessRules = array())
 {
     // The supporter must be logged,
     if (isSet($_SESSION["SupportMemberID"]))
     {
         // The supporter must be allowed to create a new forum topic
         $cUserAccess = FCT_ACT_NO_RIGHTS;
         if (isExistingForumCategory($DbConnection, $ForumCategoryID))
         {
             // Creation mode
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

             if (in_array($cUserAccess, array(FCT_ACT_CREATE, FCT_ACT_UPDATE)))
             {
                 // We get forum category access of the loggued supporter
                 $SupportMemberID = $_SESSION['SupportMemberID'];
                 $SupportMemberStateID = $_SESSION['SupportMemberStateID'];

                 $ArraySupportMemberAccess = getForumCategoryAccess($DbConnection, array($ForumCategoryID), array(),
                                                                    array($SupportMemberStateID), 'ForumCategoryID, SupportMemberStateID');

                 $cUserForumAccess = FORUM_ACCESS_NO_ACCESS;
                 if ((isset($ArraySupportMemberAccess['ForumCategoryID'])) && (!empty($ArraySupportMemberAccess['ForumCategoryID'])))
                 {
                     $cUserForumAccess = $ArraySupportMemberAccess['ForumCategoryAccess'][0];
                 }

                 if ($cUserForumAccess == FORUM_ACCESS_CREATE_TOPIC)
                 {
                     // The supporter can create topics in this forum category
                     $DefaultEditorContentMessage = '';

                     // We check if it's an update of an existing topic
                     if ($ForumTopicID > 0)
                     {
                         if (isExistingForumTopic($DbConnection, $ForumTopicID))
                         {
                             // We get content of the tpoic and the first message
                             $ForumTopicRecord = getTableRecordInfos($DbConnection, "ForumTopics", $ForumTopicID);
                             $ForumMessageID = getForumTopicMessageIDwithPosInTopic($DbConnection, $ForumTopicID, 1);
                             $ForumMessageRecord = getTableRecordInfos($DbConnection, "ForumMessages", $ForumMessageID);
                             $DefaultEditorContentMessage = $ForumMessageRecord['ForumMessageContent'];
                         }
                     }
                     else
                     {
                         // New forum topic
                         $ForumTopicRecord = array(
                                                   'ForumTopicTitle' => '',
                                                   'ForumTopicDate' => date('Y-m-d H:i:s'),
                                                   'ForumTopicStatus' => FORUM_TOPIC_OPENED,
                                                   'ForumTopicIcon' => 0,
                                                   'ForumTopicRank' => '',
                                                   'ForumTopicExpirationDate' => NULL
                                                  );

                         $ForumMessageRecord = array(
                                                     'ForumMessagePicture' => ''
                                                    );
                     }

                     // Display the category name
                     echo "[ ".generateAowIDHyperlink(getForumCategoryName($DbConnection, $ForumCategoryID), $ForumCategoryID,
                                                                       "DisplayForumCategoryTopics.php",
                                                                       $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"], "", "")." ]";

                     // Open a form
                     openForm("FormDetailsForumTopic", "post", "$ProcessFormPage?".$GLOBALS["QUERY_STRING"], "",
                              "VerificationForumTopic('".$GLOBALS["LANG_ERROR_JS_FORUM_TOPIC_TITLE"]."', '".$GLOBALS['LANG_ERROR_JS_FORUM_CATEGORY']
                                                      ."', '".$GLOBALS["LANG_ERROR_JS_FORUM_TOPIC_MESSAGE"]."', '".$GLOBALS["LANG_ERROR_JS_FORUM_TOPIC_WRONG_RANK"]
                                                      ."', '".$GLOBALS["LANG_ERROR_JS_FORUM_TOPIC_WRONG_EXPIRATON_DATE"]."')");

                     // Display the table (frame) where the form will take place
                     $sFormNewForumTopicTitle = $GLOBALS["LANG_FORUM_TOPIC"];

                     openStyledFrame($sFormNewForumTopicTitle, "Frame", "Frame", "DetailsObjectForm");

                     // Creation datetime of the forum topic
                     $CreationDate = date($GLOBALS["CONF_DATE_DISPLAY_FORMAT"].' '.$GLOBALS["CONF_TIME_DISPLAY_FORMAT"],
                                          strtotime($ForumTopicRecord['ForumTopicDate']));

                     // We get infos about the author of the forum topic
                     $ArrayInfosLoggedSupporter = getSupportMemberInfos($DbConnection, $SupportMemberID);
                     $Author = $ArrayInfosLoggedSupporter["SupportMemberLastname"].' '.$ArrayInfosLoggedSupporter["SupportMemberFirstname"]
                               .' ('.getSupportMemberStateName($DbConnection, $ArrayInfosLoggedSupporter["SupportMemberStateID"]).')';
                     $Author .= generateInputField("hidSupportMemberID", "hidden", "", "", "", $SupportMemberID);

                     // <<< ForumTopicTitle INPUTFIELD >>>
                     $Title = generateInputField("sTopicTitle", "text", "255", "100", $GLOBALS["LANG_FORUM_TOPIC_TITLE_TIP"],
                                                 htmlspecialchars($ForumTopicRecord['ForumTopicTitle'], ENT_COMPAT, $GLOBALS['CONF_CHARSET'], true));

                     // <<< Icons list of the topic >>>
                     $IconsList = '';
                     foreach($GLOBALS['CONF_FORUM_ICONS']['TopicIcons'] as $i => $CurrentIcon)
                     {
                         $bChecked = FALSE;
                         if ($i == $ForumTopicRecord['ForumTopicIcon'])
                         {
                             $bChecked = TRUE;
                         }

                         $IconsList .= generateInputField("radTopicIcon", "radio", "1", "1", $GLOBALS["LANG_FORUM_TOPIC_ICON_TIP"],
                                                          $i, FALSE, $bChecked).generateStyledPicture($CurrentIcon)."&nbsp;&nbsp;";
                     }

                     // <<< Picture of the message >>>
                     // We get picture of the family linked to this support member
                     $Picture = '';
                     $ArrayPictureList = array();
                     if ((isset($_SESSION['FamilyID'])) && ($_SESSION['FamilyID'] > 0))
                     {
                         $FamilyRecord = getTableRecordInfos($DbConnection, "Families", $_SESSION['FamilyID']);
                         if (isset($FamilyRecord['FamilyID']))
                         {
                             // We add the pictures if set
                             if ((!empty($FamilyRecord['FamilyMainPicture']))
                                 && (file_exists($GLOBALS['CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY_HDD'].$FamilyRecord['FamilyMainPicture'])))
                             {
                                 $ArrayPictureList[] = $GLOBALS['CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY'].$FamilyRecord['FamilyMainPicture'];
                             }

                             if ((!empty($FamilyRecord['FamilySecondPicture']))
                                 && (file_exists($GLOBALS['CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY_HDD'].$FamilyRecord['FamilySecondPicture'])))
                             {
                                 $ArrayPictureList[] = $GLOBALS['CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY'].$FamilyRecord['FamilySecondPicture'];
                             }
                         }
                     }

                     foreach($ArrayPictureList as $i => $CurrentPicture)
                     {
                         $CurrentFilename = basename($CurrentPicture);
                         $bChecked = FALSE;
                         if ($CurrentFilename == $ForumMessageRecord['ForumMessagePicture'])
                         {
                             $bChecked = TRUE;
                         }

                         // Set a style if the picture is a family picture
                         $PicStyle = '';
                         if ((isset($_SESSION['FamilyID'])) && ($_SESSION['FamilyID'] > 0))
                         {
                             $Prefix = "F".$_SESSION['FamilyID']."-";
                             if (substr($CurrentFilename, 0, strlen($Prefix)) == $Prefix)
                             {
                                 $PicStyle = 'LittlePicture';
                             }
                         }

                         $Picture .= generateInputField("radMessagePicture", "radio", "1", "1", $GLOBALS["LANG_FORUM_MESSAGE_PICTURE_TIP"], $CurrentFilename,
                                                        FALSE, $bChecked).generateStyledPicture($CurrentPicture, '', $PicStyle)."&nbsp;&nbsp;";
                     }

                     // <<< ForumTopicExpirationDate INPUTFIELD >>>
                     $ForumTopicExpirationDate = '';
                     if (!empty($ForumTopicRecord['ForumTopicExpirationDate']))
                     {
                         $ForumTopicExpirationDate = date($GLOBALS["CONF_DATE_DISPLAY_FORMAT"],
                                                          strtotime($ForumTopicRecord['ForumTopicExpirationDate']));
                     }

                     $ExpirationDate = generateInputField("expirationDate", "text", "10", "10", $GLOBALS["LANG_FORUM_TOPIC_EXPIRATION_DATE_TIP"],
                                                          $ForumTopicExpirationDate, TRUE);

                     // Insert the javascript to use the calendar component
                     $ExpirationDate .= "<script language=\"JavaScript\" type=\"text/javascript\">\n<!--\n\t ExpirationDateCalendar = new dynCalendar('ExpirationDateCalendar', 'calendarCallback', '".$GLOBALS['CONF_ROOT_DIRECTORY']."Common/JSCalendar/images/', 'expirationDate', '', '".$GLOBALS["CONF_LANG"]."'); \n\t//-->\n</script>\n";

                     // If there is an expiration date set, we display a button to allow to delete the date
                     if (($ForumTopicID > 0) && (!empty($ForumTopicRecord['ForumTopicExpirationDate'])))
                     {
                        $ExpirationDate .= ' '.generateStyledPictureHyperlink($GLOBALS["CONF_DELETE_ICON"],
                                                                              "javascript:ResetForumTopicExpirationDate();",
                                                                              $GLOBALS["LANG_DELETE"].'.', '', '');
                     }

                     // <<< ForumTopicRank INPUTFIELD >>>
                     if (in_array($SupportMemberStateID, $GLOBALS['CONF_FORUM_ALLOWED_TO_USE_TOPIC_RANK']))
                     {
                         // The supporter is allowed to set the topic rank
                         $Rank = generateInputField("sTopicRank", "text", "3", "3", $GLOBALS["LANG_FORUM_TOPIC_RANK_TIP"],
                                                    $ForumTopicRecord['ForumTopicRank']);
                     }
                     else
                     {
                         $Rank = $ForumTopicRecord['ForumTopicRank'];
                     }

                     // <<< ForumTopicStatus SELECTFIELD >>>
                     $ArrayTopicStatus = array(
                                               FORUM_TOPIC_OPENED => $GLOBALS['LANG_FORUM_TOPIC_STATUS_OPENED'],
                                               FORUM_TOPIC_AUTHOR_ONLY => $GLOBALS['LANG_FORUM_TOPIC_STATUS_EDITABLE_ONLY_BY_AUTHOR'],
                                               FORUM_TOPIC_CLOSED => $GLOBALS['LANG_FORUM_TOPIC_STATUS_CLOSED']
                                              );

                     if (in_array($SupportMemberStateID, $GLOBALS['CONF_FORUM_ALLOWED_TO_USE_TOPIC_STATUS']))
                     {
                         // The supporter is allowed to change the topic
                         $Status = generateSelectField("lTopicStatus", array_keys($ArrayTopicStatus), array_values($ArrayTopicStatus),
                                                       $ForumTopicRecord['ForumTopicStatus']);
                     }
                     else
                     {
                         $Status = $ArrayTopicStatus[$ForumTopicRecord['ForumTopicStatus']];
                     }

                     // <<< ForumTopicContent area >>>
                     $ArrayEditorButtons = array(
                                                 'button' => array(
                                                                   1 => array('.ql-bold', '.ql-italic', '.ql-underline', '.ql-strike',
                                                                              '.ql-blockquote', '.ql-code-block', '.ql-direction',
                                                                              '.ql-link', '.ql-image', '.ql-video', '.ql-emoji', '.ql-clean'),
                                                                   2 => array('.ql-script', '.ql-header', '.ql-list', '.ql-indent')
                                                                  ),
                                                 'span' => array(
                                                                 1 => array('.ql-font', '.ql-color-picker', '.ql-background', '.ql-header',
                                                                            '.ql-align')
                                                                ),
                                                 '#ql-picker-options-1' => array(
                                                                                 35 => array(' .ql-picker-item')
                                                                                )
                                                );

                     $TopicContent = "<div id=\"editor\">$DefaultEditorContentMessage</div>\n";
                     $TopicContent .= generateInputField("hidTopicContent", "hidden", "", "", "", "");

                     $TopicContent .= "<script>\n
                                       const Tooltip = Quill.import('ui/tooltip');
                                       const toolbarOptions = {
                                                               container: [
                                                                           ['bold', 'italic', 'underline', 'strike', { 'script': 'sub'}, { 'script': 'super' }],
                                                                           ['blockquote', 'code-block'],
                                                                           [{ 'font': [] }, { 'color': [] }, { 'background': [] }],
                                                                           [{ 'header': 1 }, { 'header': 2 }],
                                                                           [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                                                                           [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                                                           [{ 'indent': '-1'}, { 'indent': '+1' }, { 'align': [] }, { 'direction': 'rtl' }],
                                                                           ['link', 'image', 'video', 'emoji'],
                                                                           ['clean']
                                                                          ],
                                                               handlers: {
                                                                          'emoji': function() {}
                                                                         }
                                                              };

                                       var quill = new Quill('#editor', {
                                                                         modules: {
                                                                                   'emoji-toolbar': true,
                                                                                   'emoji-textarea': false,
                                                                                   'emoji-shortname': false,
                                                                                   toolbar: toolbarOptions,
                                                                                   imageResize: {
                                                                                                 displaySize: true
                                                                                                }
                                                                                  },
                                                                         theme: 'snow'
                                                            });

                                       var toolbar = quill.container.previousSibling;\n";

                     // Display tooltips on icons of the editor
                     foreach($ArrayEditorButtons as $elt => $ArrayCurrentElts)
                     {
                         foreach($ArrayCurrentElts as $iNbElts => $ArrayCurrentEltStyles)
                         {
                             foreach($ArrayCurrentEltStyles as $CurrentEditButton)
                             {
                                 switch($iNbElts)
                                 {
                                     case 1:
                                         $sLangVarName = 'LANG_FORUM_EDITOR_'.strToUpper(str_replace(array('.ql-', '-', ' '),
                                                                                                     array('', '_', ''), $CurrentEditButton))."_TIP";

                                         if (isset($GLOBALS[$sLangVarName]))
                                         {
                                             $TopicContent .= "                 toolbar.querySelector('$elt$CurrentEditButton').setAttribute('title', '"
                                                                                                      .$GLOBALS[$sLangVarName]."');\n";
                                         }
                                         break;

                                     default:
                                         for($e = 0; $e < $iNbElts; $e++)
                                         {
                                             $sLangVarName = 'LANG_FORUM_EDITOR_'.strToUpper(str_replace(array('.ql-', '-', ' '),
                                                                                                         array('', '_', '',), $CurrentEditButton))."_".$e."_TIP";

                                             if (isset($GLOBALS[$sLangVarName]))
                                             {
                                                 $TopicContent .= "                 toolbar.querySelectorAll('$elt$CurrentEditButton')[$e].setAttribute('title', '"
                                                                                                             .$GLOBALS[$sLangVarName]."');\n";
                                             }
                                         }
                                         break;
                                 }
                             }
                         }
                     }

                     $TopicContent .= "                 quill.focus();\n
                                       quill.setSelection(quill.getLength(), 0);
                                   </script>\n";

                     // Display the form
                     echo "<table id=\"TopicDetails\" cellspacing=\"0\" cellpadding=\"0\">\n<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_CREATION_DATE"]."</td><td class=\"Value\">$CreationDate, $Author</td><td class=\"Label\">".$GLOBALS["LANG_FORUM_TOPIC_EXPIRATION_DATE"]."</td><td class=\"Value\">$ExpirationDate</td>\n</tr>\n";

                     // The supporter is allowed to notify other users there is a new topic
                     if ((in_array($SupportMemberStateID, $GLOBALS['CONF_FORUM_ALLOWED_TO_NOTIFY_NEW_TOPIC'])) && (empty($ForumTopicID)))
                     {
                         $Notify = generateInputField("chkNotify", "checkbox", "1", "1", $GLOBALS["LANG_FORUM_TOPIC_NOTIFY_TIP"], 'notify');

                         echo "<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_FORUM_TOPIC_TITLE"]."*</td><td class=\"Value\">$Title</td><td class=\"Label\">"
                              .$GLOBALS["LANG_FORUM_TOPIC_NOTIFY"]."</td><td class=\"Value\">$Notify</td>\n</tr>\n";
                     }
                     else
                     {
                         echo "<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_FORUM_TOPIC_TITLE"]."*</td><td class=\"Value\" colspan=\"3\">$Title</td>\n</tr>\n";
                     }

                     echo "<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_FORUM_TOPIC_ICON"]."</td><td class=\"Value\" colspan=\"3\">$IconsList</td>\n</tr>\n";
                     echo "<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_FORUM_TOPIC_RANK"]."</td><td class=\"Value\">$Rank</td><td class=\"Label\">".$GLOBALS["LANG_FORUM_TOPIC_STATUS"]."</td><td class=\"Value\">$Status</td>\n</tr>\n";

                     // We display this field only if there is at least one picture
                     if (!empty($Picture))
                     {
                         echo "<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_FORUM_MESSAGE_PICTURE"]."</td><td class=\"Value\" colspan=\"3\">$Picture</td>\n</tr>\n";
                     }

                     echo "<tr>\n\t<td class=\"LabelEditor\" colspan=\"4\">$TopicContent</td>\n</tr>\n";
                     echo "</table>\n";

                     insertInputField("hidForumCategoryID", "hidden", "", "", "", $ForumCategoryID);
                     insertInputField("hidForumTopicID", "hidden", "", "", "", $ForumTopicID);
                     closeStyledFrame();

                     // We display the buttons
                     echo "<table class=\"validation\">\n<tr>\n\t<td>";
                     insertInputField("bSubmit", "submit", "", "", $GLOBALS["LANG_SUBMIT_BUTTON_TIP"], $GLOBALS["LANG_SUBMIT_BUTTON_CAPTION"]);
                     echo "</td><td class=\"FormSpaceBetweenButtons\"></td><td>";
                     insertInputField("bReset", "reset", "", "", $GLOBALS["LANG_RESET_BUTTON_TIP"], $GLOBALS["LANG_RESET_BUTTON_CAPTION"], FALSE, FALSE, "onclick=\"ResetEditorContent();\"");
                     echo "</td>\n</tr>\n</table>\n";

                     closeForm();
                 }
             }
             else
             {
                 // The supporter isn't allowed to create a new forum topic
                 openParagraph('ErrorMsg');
                 echo $GLOBALS["LANG_ERROR_NOT_ALLOWED_TO_CREATE_OR_UPDATE"];
                 closeParagraph();
             }
         }
         else
         {
             // The forum category doesn't exist
             openParagraph('ErrorMsg');
             echo $GLOBALS["LANG_ERROR_WRONG_FORUM_CATEGORY_ID"];
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
 * Display the messages of a forum topic a loggued supporter in the current web page, in the graphic interface in XHTML
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-16
 *
 * @param $DbConnection                DB object            Object of the opened database connection
 * @param $ForumTopicID                Integer              Id of the forum topic to display messages [1..n]
 * @param $ProcessFormPage             String               URL of the page which will process the form allowing to find and to sort
 *                                                          the table of the forum messages found
 * @param $Page                        Integer              Number of the Page to display [1..n]
 * @param $SortFct                     String               Javascript function used to sort the table
 * @param $OrderBy                     Integer              n° Criteria used to sort the forum messages. If < 0, DESC is used, otherwise ASC
 *                                                          is used
 * @param $AccessRules                 Array of Integers    List used to select only some support members
 *                                                          allowed to create or update topics and messages in forum category
 */
 function displayForumTopicMessagesList($DbConnection, $ForumTopicID, $ProcessFormPage, $Page = 1, $SortFct = '', $OrderBy = 0, $AccessRules = array())
 {
     if (isSet($_SESSION["SupportMemberID"]))
     {
         // The supporter must be allowed to access to forum messages list of a given topic
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

         if (in_array($cUserAccess, array(FCT_ACT_CREATE, FCT_ACT_UPDATE, FCT_ACT_READ_ONLY)))
         {
             if (($ForumTopicID > 0) && (isExistingForumTopic($DbConnection, $ForumTopicID)))
             {
                 // We get details about the forum topic to display
                 $ForumTopicRecord = getTableRecordInfos($DbConnection, "ForumTopics", $ForumTopicID);

                 // We get forum category access of the loggued supporter
                 $ForumCategoryID = $ForumTopicRecord['ForumCategoryID'];
                 $SupportMemberStateID = $_SESSION["SupportMemberStateID"];
                 $ArraySupportMemberAccess = getForumCategoryAccess($DbConnection, $ForumCategoryID, array(), $SupportMemberStateID,
                                                                    'ForumCategoryID, SupportMemberStateID');

                 $cUserForumAccess = FORUM_ACCESS_NO_ACCESS;
                 if ((isset($ArraySupportMemberAccess['ForumCategoryID'])) && (!empty($ArraySupportMemberAccess['ForumCategoryID'])))
                 {
                     $cUserForumAccess = $ArraySupportMemberAccess['ForumCategoryAccess'][0];
                 }

                 // The loggued supporter can read or write messages of the topic ?
                 if (in_array($cUserForumAccess, array(FORUM_ACCESS_CREATE_TOPIC, FORUM_ACCESS_WRITE_MSG, FORUM_ACCESS_READ_MSG)))
                 {
                     // The loggued supporter can add a message if :
                     // 1) the topic isn't closed
                     // 2) the topic is editable only by the author and he is the author
                     // 3) he has "create topic" or "write message" rights for this category
                     // 4) he is admin
                     $bCanAddMessage = FALSE;
                     if ((($ForumTopicRecord['ForumTopicStatus'] == FORUM_TOPIC_OPENED)
                         && (in_array($cUserForumAccess, array(FORUM_ACCESS_CREATE_TOPIC, FORUM_ACCESS_WRITE_MSG))))
                        || (($ForumTopicRecord['ForumTopicStatus'] == FORUM_TOPIC_AUTHOR_ONLY)
                            && ($_SESSION['SupportMemberID'] == $ForumTopicRecord['SupportMemberID']))
                        || ($_SESSION['SupportMemberStateID'] == 1))
                     {
                         $bCanAddMessage = TRUE;
                     }

                     openParagraph('ForumButton');

                     $bCreateTopicButtonDisplayed = FALSE;
                     if ($cUserForumAccess == FORUM_ACCESS_CREATE_TOPIC)
                     {
                         // The supporter can create topics in this forum category
                         // Display a button to create a new topic in this forum category
                         $bCreateTopicButtonDisplayed = TRUE;
                         echo generateStyledPictureHyperlink($GLOBALS["CONF_FORUM_ICONS"]['CreateTopic'],
                                                             "CreateForumTopic.php?Cr=".md5($ForumCategoryID)."&amp;Id=$ForumCategoryID",
                                                             $GLOBALS["LANG_SUPPORT_FORUM_TOPICS_LIST_PAGE_CREATE_TOPIC_TIP"], '', '');

                     }

                     // Display the subscribtion button to the topic if the topic isn't closed
                     if ($ForumTopicRecord['ForumTopicStatus'] != FORUM_TOPIC_CLOSED)
                     {
                         $sStyleSubscribtionButton = '';
                         if ($bCreateTopicButtonDisplayed)
                         {
                             $sStyleSubscribtionButton = 'ForumSpaceButton';
                         }

                         echo generateStyledPictureHyperlink($GLOBALS["CONF_FORUM_ICONS"]['TopicSubscribtion'],
                                                             "SubscribeForumTopic.php?Cr=".md5($ForumTopicID)."&amp;Id=$ForumTopicID",
                                                             $GLOBALS["LANG_SUPPORT_FORUM_TOPICS_MESSAGES_PAGE_SUBSCRIBE_TO_TOPIC_TIP"],
                                                             $sStyleSubscribtionButton, '');
                     }

                     closeParagraph();

                     // Display the category name
                     echo "[ ".generateAowIDHyperlink(getForumCategoryName($DbConnection, $ForumCategoryID), $ForumCategoryID,
                                                                           "DisplayForumCategoryTopics.php",
                                                                           $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"], "", "")." ]";

                     // Display warning messages
                     $sWarningMsgs = '';

                     // Topic status
                     switch($ForumTopicRecord['ForumTopicStatus'])
                     {
                         case FORUM_TOPIC_AUTHOR_ONLY:
                             $sWarningMsgs = generateStyledPicture($GLOBALS["CONF_FORUM_ICONS"]['TopicStatus'][FORUM_TOPIC_AUTHOR_ONLY], '', '')
                                             .$GLOBALS['LANG_FORUM_TOPIC_STATUS_EDITABLE_ONLY_BY_AUTHOR'].'. ';
                             break;

                         case FORUM_TOPIC_CLOSED:
                             $sWarningMsgs = generateStyledPicture($GLOBALS["CONF_FORUM_ICONS"]['TopicStatus'][FORUM_TOPIC_CLOSED], '', '')
                                             .$GLOBALS['LANG_FORUM_TOPIC_STATUS_CLOSED'].'. ';
                             break;
                     }

                     // We check if the supporter has a subscribtion to the topic
                     $ArrayTopicSubscribtions = getForumTopicsSubscribtions($DbConnection, array(
                                                                                                 'ForumTopicID' => $ForumTopicID,
                                                                                                 'SupportMemberID' => $_SESSION['SupportMemberID']
                                                                                                ));

                     if ((isset($ArrayTopicSubscribtions[$ForumTopicID]))
                         && (isset($ArrayTopicSubscribtions[$ForumTopicID]['ForumTopicSubscribtionID']))
                         && (!empty($ArrayTopicSubscribtions[$ForumTopicID]['ForumTopicSubscribtionID'])))
                     {
                         // We display subscribtions of the supporter
                         if (!empty($sWarningMsgs))
                         {
                             $sWarningMsgs .= generateBR(1);
                         }

                         $sWarningMsgs .= "<ul class=\"TopicSubscribtions\">\n";

                         foreach($ArrayTopicSubscribtions[$ForumTopicID]['ForumTopicSubscribtionID'] as $ts => $CurrentTopicSubID)
                         {
                             $sWarningMsgs .= "<li>".generateStyledPicture($GLOBALS["CONF_FORUM_ICONS"]['SubscribedToTopic'], '', '')
                                              .' '.$GLOBALS['LANG_FORUM_TOPIC_SUBSCRIBED'].' : '
                                              .$ArrayTopicSubscribtions[$ForumTopicID]['ForumTopicSubscribtionEmail'][$ts]
                                              ."&nbsp;&nbsp;".generateStyledPictureHyperlink($GLOBALS["CONF_DELETE_ICON"],
                                                                                            "DeleteForumTopicSubscribtion.php?Cr=".md5($CurrentTopicSubID)
                                                                                            ."&amp;Id=$CurrentTopicSubID",
                                                                                            $GLOBALS["LANG_DELETE"].'.', '', '')
                                              ."</li>\n";
                         }

                         $sWarningMsgs .= "</ul>\n";
                     }

                     // We check if its a temporary topic (deleted after a given date)
                     if (!empty($ForumTopicRecord['ForumTopicExpirationDate']))
                     {
                         if (!empty($sWarningMsgs))
                         {
                             $sWarningMsgs .= generateBR(1);
                         }

                         $sWarningMsgs .= $GLOBALS['LANG_FORUM_TEMPORARY_TOPIC_INFOS']
                                          .' <strong>'.date($GLOBALS["CONF_DATE_DISPLAY_FORMAT"],
                                                            strtotime($ForumTopicRecord['ForumTopicExpirationDate'])).'</strong>. ';

                     }

                     if (!empty($sWarningMsgs))
                     {
                         openParagraph('TemporayTopicWarning');
                         echo $sWarningMsgs;
                         closeParagraph();
                     }

                     // Open a form
                     openForm("FormForumMessages", "post", "$ProcessFormPage", "", "");
                     insertInputField("hidOrderByField", "hidden", "", "", "", $OrderBy);
                     closeForm();

                     // The supporter has executed a search
                     $ArrayCaptions = array($GLOBALS["LANG_FORUM_TOPIC_AUTHOR"], $ForumTopicRecord['ForumTopicTitle']);
                     $ArraySorts = array("", "");

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
                         $StrOrderBy = "ForumMessageDate ASC";
                     }

                     // We launch the search
                     $TabParams = array('ForumTopicID' => $ForumTopicID);
                     $NbRecords = getNbdbSearchForumMessage($DbConnection, $TabParams);
                     if ($NbRecords > 0)
                     {
                         // To get only forum messages of the page for the given forum topic
                         $ArrayRecords = dbSearchForumMessage($DbConnection, $TabParams, $StrOrderBy, $Page, $GLOBALS["CONF_RECORDS_PER_PAGE"]);

                         // There are some forum messages found
                         foreach($ArrayRecords["ForumMessageID"] as $i => $CurrentValue)
                         {
                             // We check if the message if the first of the topic
                             $iLastMsgID = $CurrentValue;
                             $bIsFirstTopicMessage = isFirstForumTopicMessage($DbConnection, $CurrentValue);
                             $bCanDeleteFiles = FALSE;

                             // The author of the message
                             $sMessageAuthor = $ArrayRecords["SupportMemberLastname"][$i].' '.$ArrayRecords["SupportMemberFirstname"][$i]
                                              .generateBR(1).' ('.$ArrayRecords["SupportMemberStateName"][$i].')';

                             // We check if there is a picture to display
                             if (!empty($ArrayRecords["ForumMessagePicture"][$i]))
                             {
                                 $PicStyle = '';
                                 if (preg_match("/F[0-9]+-[0-9]_/", $ArrayRecords["ForumMessagePicture"][$i]) == 0)
                                 {
                                     // The picture is an icon : we search its path in $CONF_FORUM_ICONS['TopicIcons']
                                     $sPicPath = '';
                                     foreach($GLOBALS['CONF_FORUM_ICONS']['TopicIcons'] as $ic => $CurrentIcon)
                                     {
                                         if (basename($CurrentIcon) == $ArrayRecords["ForumMessagePicture"][$i])
                                         {
                                             $sPicPath = $CurrentIcon;
                                         }
                                     }
                                 }
                                 else
                                 {
                                     // It's a picture of the family
                                     $PicStyle = 'LittlePicture';
                                     $sPicPath = $GLOBALS['CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY'].$ArrayRecords["ForumMessagePicture"][$i];
                                 }

                                 $sMessageAuthor .= generateBR(2)
                                                    .generateStyledPicture($sPicPath, '', $PicStyle);
                             }

                             $ArrayData[0][] = $sMessageAuthor;

                             // The message content
                             $MessageCellContent = "<table class=\"ForumMessageToolbar\" cellspacing=\"0\" id=\"Msg$CurrentValue\">";

                             // Message date
                             $iNbToolbarCells = 1;
                             $MessageCellContent .= "<tr><td class=\"ForumMessageDate\">".$GLOBALS['LANG_FORUM_MESSAGE_POSTED_DATE'].' '
                                                    .date($GLOBALS["CONF_DATE_DISPLAY_FORMAT"].' '.$GLOBALS["CONF_TIME_DISPLAY_FORMAT"],
                                                          strtotime($ArrayRecords["ForumMessageDate"][$i]))."</td>";

                             // Check if the loggued supported has rights to create/edit a message
                             if ($bCanAddMessage)
                             {
                                 // Check if the loggued supported is the author of the message or an admin
                                 if (($_SESSION['SupportMemberID'] == $ArrayRecords["SupportMemberID"][$i]) || ($_SESSION['SupportMemberStateID'] == 1))
                                 {
                                     // Yes
                                     $bCanDeleteFiles = TRUE;

                                     if ($bIsFirstTopicMessage)
                                     {
                                         // Display the edit message button : allow to edit the topic
                                         $iNbToolbarCells++;
                                         $MessageCellContent .= "<td class=\"button\">".generateStyledPictureHyperlink($GLOBALS["CONF_FORUM_ICONS"]['EditMessage'],
                                                                                                      "UpdateForumTopic.php?Cr=".md5($ForumTopicID)."&amp;Id=$ForumTopicID",
                                                                                                      $GLOBALS["LANG_FORUM_TOPIC_EDIT_TOPIC_TIP"], '', '')."</td>";
                                     }
                                     else
                                     {
                                         // Display the edit message button
                                         $iNbToolbarCells += 2;
                                         $MessageCellContent .= "<td class=\"button\">".generateStyledPictureHyperlink($GLOBALS["CONF_FORUM_ICONS"]['EditMessage'],
                                                                                                      "UpdateForumTopicMessage.php?Cr=".md5($CurrentValue)."&amp;Id=$CurrentValue",
                                                                                                      $GLOBALS["LANG_FORUM_MESSAGE_EDIT_MESSAGE_TIP"], '', '')."</td>";

                                         // Display the delete message button
                                         $MessageCellContent .= "<td class=\"button\">".generateStyledPictureHyperlink($GLOBALS["CONF_FORUM_ICONS"]['DeleteMessage'],
                                                                                                      "DeleteForumTopicMessage.php?Cr=".md5($CurrentValue)."&amp;Id=$CurrentValue",
                                                                                                      $GLOBALS["LANG_FORUM_MESSAGE_DELETE_MESSAGE_TIP"], '', '',
                                                                                                      " onclick=\"return ConfirmationBox('".$GLOBALS['LANG_JS_FORUM_MESSAGE_CONFIRM_DELETE_MESSAGE']."');\"")
                                                                ."</td>";
                                     }

                                     if ($_SESSION['SupportMemberStateID'] == 1)
                                     {
                                         // For admin, we add the "reply" button
                                         $iNbToolbarCells++;
                                         $MessageCellContent .= "<td class=\"button\">".generateStyledPictureHyperlink($GLOBALS["CONF_FORUM_ICONS"]['ReplyToMessage'],
                                                                                                      "CreateForumTopicMessage.php?Cr=".md5($ForumTopicID)
                                                                                                      ."&amp;Id=$ForumTopicID&amp;Action=ReplyTo&amp;MCr="
                                                                                                      .md5($CurrentValue)."&amp;MId=$CurrentValue",
                                                                                                      $GLOBALS["LANG_FORUM_MESSAGE_REPLY_TO_MESSAGE_TIP"], '', '')."</td>";
                                     }

                                     // We check if the supporter is allowed to upload files to a forum message
                                     if ($GLOBALS['CONF_UPLOAD_FORUM_MESSAGE_FILES'])
                                     {
                                         // Display the upload message button
                                         $iNbToolbarCells++;
                                         $MessageCellContent .= "<td class=\"button\">".generateStyledPictureHyperlink($GLOBALS["CONF_FORUM_ICONS"]['UploadFile'],
                                                                                                      "UploadForumTopicMessageFile.php?Type=".OBJ_FORUM_MESSAGE
                                                                                                      ."&amp;Cr=".md5($CurrentValue)."&amp;Id=$CurrentValue",
                                                                                                      $GLOBALS["LANG_FORUM_MESSAGE_UPLOAD_FILE_TIP"], '', '')."</td>";
                                     }
                                 }
                                 else
                                 {
                                     // Display the reply button
                                     $iNbToolbarCells++;
                                     $MessageCellContent .= "<td class=\"button\">".generateStyledPictureHyperlink($GLOBALS["CONF_FORUM_ICONS"]['ReplyToMessage'],
                                                                                                  "CreateForumTopicMessage.php?Cr=".md5($ForumTopicID)
                                                                                                  ."&amp;Id=$ForumTopicID&amp;Action=ReplyTo&amp;MCr="
                                                                                                  .md5($CurrentValue)."&amp;MId=$CurrentValue",
                                                                                                  $GLOBALS["LANG_FORUM_MESSAGE_REPLY_TO_MESSAGE_TIP"], '', '')."</td>";
                                 }
                             }

                             // End of the toolbar
                             $MessageCellContent .= "</tr>\n";

                             // We check if the forum message has uploaded files
                             $UploadedFilesList = '';
                             $ArrayUploadedFiles = getForumMessageUploadedFiles($DbConnection, $CurrentValue, array(), 'UploadedFileDate');
                             if ((isset($ArrayUploadedFiles['UploadedFileID'])) && (!empty($ArrayUploadedFiles['UploadedFileID'])))
                             {
                                 $UploadedFilesList = "<dl class=\"ForumMessageUploadedFilesList\">\n";

                                 foreach($ArrayUploadedFiles['UploadedFileID'] as $uf => $CurrentUploadedFileID)
                                 {
                                     // Url to download the file (one directory for each year)
                                     $UploadedFileUrl = $GLOBALS['CONF_UPLOAD_FORUM_MESSAGE_FILES_DIRECTORY']
                                                        .date('Y', strtotime($ArrayUploadedFiles['UploadedFileDate'][$uf]))
                                                        .'/'.$ArrayUploadedFiles['UploadedFileName'][$uf];

                                     $UploadedFilesList .= "<dt>".generateStyledPicture($GLOBALS['CONF_FORUM_ICONS']['DownloadFile'], '', '').' '
                                                                                        .generateAowIDHyperlink($ArrayUploadedFiles['UploadedFileName'][$uf],
                                                                                        $CurrentUploadedFileID, $UploadedFileUrl,
                                                                                        $GLOBALS["LANG_FORUM_MESSAGE_DOWNLOAD_FILE_TIP"],  "", "_blank");

                                     if ($bCanDeleteFiles)
                                     {
                                         // Button to delete the file
                                         $UploadedFilesList .= "&nbsp;&nbsp;".generateStyledPictureHyperlink($GLOBALS["CONF_DELETE_ICON"],
                                                                                                             "DeleteForumTopicMessageFile.php?Cr=".md5($CurrentUploadedFileID)
                                                                                                             ."&amp;Id=$CurrentUploadedFileID",
                                                                                                             $GLOBALS["LANG_DELETE_FILE_INSTRUCTIONS"], '', '',
                                                                                                             " onclick=\"return ConfirmationBox('".$GLOBALS['LANG_JS_FORUM_MESSAGE_CONFIRM_DELETE_FILE']."');\"");
                                     }

                                     $UploadedFilesList .= "</dt>\n";

                                     // We check if the file has a description
                                     if (!empty($ArrayUploadedFiles['UploadedFileDescription'][$uf]))
                                     {
                                         $UploadedFilesList .= "<dd>".$ArrayUploadedFiles['UploadedFileDescription'][$uf]."</dd>\n";
                                     }
                                 }

                                 $UploadedFilesList .= "</dl>\n";
                             }

                             // Display the forum message content
                             // Before, we must treat the message content
                             $sForumMessageContent = stripslashes($ArrayRecords["ForumMessageContent"][$i]);

                             // It's a "reply to" message ?
                             if (!empty($ArrayRecords["ForumReplyToMessageID"][$i]))
                             {
                                 // Get infos about the author of the quoted message
                                 $ArrayMsgAuthorInfos = getForumMessageAuthorInfos($DbConnection, $ArrayRecords["ForumReplyToMessageID"][$i]);
                                 $sAuthorInfos = $ArrayMsgAuthorInfos['SupportMemberLastname'].' '.$ArrayMsgAuthorInfos['SupportMemberFirstname'].' ('.
                                                 $ArrayMsgAuthorInfos['SupportMemberStateName'].') '.$GLOBALS['LANG_FORUM_MESSAGE_QUOTED_WROTE'].' : ';

                                 // We format the quoted message : we get position of all "</blockquote><blockquote>" tags;
                                 $ArrayQuotedTags = explode("</blockquote><blockquote>", $sForumMessageContent);
                                 $iNbElements = count($ArrayQuotedTags);
                                 if ($iNbElements == 1)
                                 {
                                     // Only <blockquote>...</blockquote>
                                     $sSingleQuoteContent = '';
                                     $CurrentMsgPart = $ArrayQuotedTags[0];

                                     // First part : we search the <blockquote> tag to add before a <div> tag
                                     $iPos = stripos($CurrentMsgPart, "<blockquote>");
                                     if ($iPos !== FALSE)
                                     {
                                         $iPosEnd = stripos($CurrentMsgPart, "</blockquote>", $iPos + strlen("<blockquote>"));
                                         if ($iPosEnd !== FALSE)
                                         {
                                             $sStartMsgPart = substr($CurrentMsgPart, 0, $iPos);
                                             $sQuoteMsgPart = substr($CurrentMsgPart, $iPos, $iPosEnd);
                                             $sEndMsgPart = substr($CurrentMsgPart, $iPosEnd + strlen("</blockquote>"));

                                             // We check if the quotedmessage is in this page of the topic
                                             if (in_array($ArrayRecords["ForumReplyToMessageID"][$i], $ArrayRecords["ForumMessageID"]))
                                             {
                                                 // Yes : the url is just an anchor
                                                 $sGoToMsgUrl = "#Msg".$ArrayRecords["ForumReplyToMessageID"][$i];
                                             }
                                             else
                                             {
                                                 // No : the quoted message is in another page of the topic
                                                 // We must compute the n° of the page
                                                 $iMsgPosInTopic = getForumTopicMessagePosInTopic($DbConnection, $ArrayRecords["ForumReplyToMessageID"][$i]);
                                                 $iPageNum = ceil($iMsgPosInTopic / $GLOBALS["CONF_RECORDS_PER_PAGE"]);
                                                 $sGoToMsgUrl = "$ProcessFormPage?Pg=$iPageNum&amp;Cr=".md5($ForumTopicID)
                                                                ."&amp;Id=$ForumTopicID#Msg".$ArrayRecords["ForumReplyToMessageID"][$i];
                                             }

                                             $sSingleQuoteContent = "$sStartMsgPart<div class=\"ForumReplyToMessage\">
                                                                    <p><a href=\"$sGoToMsgUrl\" title=\"\">$sAuthorInfos</a></p>$sQuoteMsgPart</div>$sEndMsgPart";

                                             $sForumMessageContent = $sSingleQuoteContent;
                                         }
                                     }
                                 }
                                 else
                                 {
                                     foreach($ArrayQuotedTags as $qt => $CurrentMsgPart)
                                     {
                                         if ($qt == 0)
                                         {
                                             // First part : we search the <blockquote> tag to add before a <div> tag
                                             $iPos = stripos($CurrentMsgPart, "<blockquote>");
                                             if ($iPos !== FALSE)
                                             {
                                                 $sStartMsgPart = substr($CurrentMsgPart, 0, $iPos);
                                                 $sEndMsgPart = substr($CurrentMsgPart, $iPos);

                                                 // We check if the quotedmessage is in this page of the topic
                                                 if (in_array($ArrayRecords["ForumReplyToMessageID"][$i], $ArrayRecords["ForumMessageID"]))
                                                 {
                                                     // Yes : the url is just an anchor
                                                     $sGoToMsgUrl = "#Msg".$ArrayRecords["ForumReplyToMessageID"][$i];
                                                 }
                                                 else
                                                 {
                                                     // No : the quoted message is in another page of the topic
                                                     // We must compute the n° of the page
                                                     $iMsgPosInTopic = getForumTopicMessagePosInTopic($DbConnection, $ArrayRecords["ForumReplyToMessageID"][$i]);
                                                     $iPageNum = ceil($iMsgPosInTopic / $GLOBALS["CONF_RECORDS_PER_PAGE"]);
                                                     $sGoToMsgUrl = "$ProcessFormPage?Pg=$iPageNum&amp;Cr=".md5($ForumTopicID)
                                                                    ."&amp;Id=$ForumTopicID#Msg".$ArrayRecords["ForumReplyToMessageID"][$i];
                                                 }

                                                 $ArrayQuotedTags[$qt] = "$sStartMsgPart<div class=\"ForumReplyToMessage\">
                                                                          <p><a href=\"$sGoToMsgUrl\" title=\"\">$sAuthorInfos</a></p>$sEndMsgPart";
                                             }
                                         }
                                         elseif ($qt == $iNbElements - 1)
                                         {
                                             // Last part : we search the </blockquote> tag to add after a </div> tag
                                             $iPos = stripos($CurrentMsgPart, "</blockquote>");
                                             if ($iPos !== FALSE)
                                             {
                                                 $sStartMsgPart = substr($CurrentMsgPart, 0, $iPos + strlen("</blockquote>"));
                                                 $sEndMsgPart = substr($CurrentMsgPart, $iPos + strlen("</blockquote>"));
                                                 $ArrayQuotedTags[$qt] = "$sStartMsgPart</div>$sEndMsgPart";
                                             }
                                         }
                                     }

                                     $sForumMessageContent = implode("</blockquote><blockquote>", $ArrayQuotedTags);
                                 }
                             }

                             // Replace smileys' codes by pictures
                             $ArraySmileysConv = array(
                                                       'Codes' => array(),
                                                       'Pictures' => array()
                                                      );
                             foreach($GLOBALS['CONF_FORUM_SMILEYS'] as $SmCode => $SmPicture)
                             {
                                 $ArraySmileysConv['Codes'][] = $SmCode;
                                 $ArraySmileysConv['SmPicture'][] = generateStyledPicture($GLOBALS['CONF_FORUM_ICONS']['SmileysDirectory'].$SmPicture,
                                                                                          $SmCode, '');
                             }

                             $sForumMessageContent = str_replace($ArraySmileysConv['Codes'], $ArraySmileysConv['SmPicture'], $sForumMessageContent);

                             // We check if the message has been updated
                             if (!empty($ArrayRecords["ForumMessageUpdateDate"][$i]))
                             {
                                 $sForumMessageContent .= "<p class=\"ForumUpdatedMessage\">".$GLOBALS['LANG_FORUM_MESSAGE_UPDATED_DATE'].' '
                                                          .date($GLOBALS["CONF_DATE_DISPLAY_FORMAT"].' '.$GLOBALS["CONF_TIME_DISPLAY_FORMAT"],
                                                                strtotime($ArrayRecords["ForumMessageUpdateDate"][$i]))."</p>";
                             }

                             $MessageCellContent .= "<tr><td class=\"ForumMessageContent\" colspan=\"$iNbToolbarCells\">".$UploadedFilesList.$sForumMessageContent."</td></tr>\n";
                             $MessageCellContent .= "</table>\n";
                             $ArrayData[1][] = $MessageCellContent;
                         }

                         // Display the table which contains the forum messages found
                         $ArraySortedFields = array("", "");
                         displayStyledTable($ArrayCaptions, $ArraySortedFields, $SortFct, $ArrayData, '', '', '', '',
                                            array(), $OrderBy, array('ForumMessageAuthor', 'ForumMessageCell'), 'TopicMessagesList');

                         // Get topic flags of the loggued supporter
                         $ArrayTopicsReadFlags = getForumTopicsLastReads($DbConnection, array(
                                                                                              'ForumTopicID' => $ForumTopicID,
                                                                                              'SupportMemberID' => $_SESSION['SupportMemberID']
                                                                                             ));

                         if ((isset($ArrayTopicsReadFlags[$ForumTopicID]))
                             && ($ArrayTopicsReadFlags[$ForumTopicID]['ForumTopicLastReadMessageID'][0] < $iLastMsgID))
                         {
                             // The current last message is > the previous last read message by the loggued supporter : we update the flag
                             dbUpdateForumTopicLastRead($DbConnection, $ArrayTopicsReadFlags[$ForumTopicID]['ForumTopicLastReadID'][0],
                                                        $iLastMsgID, $ForumTopicID, $_SESSION['SupportMemberID']);
                         }
                         else
                         {
                             // Add a read flag for this topic : the last read message is the last message of this page
                             dbAddForumTopicLastRead($DbConnection, $iLastMsgID, $ForumTopicID, $_SESSION['SupportMemberID']);
                         }

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
                                 elseif(!isset($_GET['Pg']))
                                 {
                                     // No form submitted
                                     $PreviousLink = "$ProcessFormPage?Pg=$NoPage&amp;Ob=$OrderBy&amp;Cr=".md5($ForumTopicID)."&amp;Id=$ForumTopicID";
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
                                 elseif(!isset($_GET['Pg']))
                                 {
                                     // No form submitted
                                     $NextLink = "$ProcessFormPage?Pg=$NoPage&amp;Ob=$OrderBy&amp;Cr=".md5($ForumTopicID)."&amp;Id=$ForumTopicID";
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

                         // Display the "Add message" button if the supporter is allowed
                         if ($bCanAddMessage)
                         {
                             openParagraph('ForumButton');
                             echo generateStyledPictureHyperlink($GLOBALS["CONF_FORUM_ICONS"]['CreateMessage'],
                                                                 "CreateForumTopicMessage.php?Cr=".md5($ForumTopicID)."&amp;Id=$ForumTopicID",
                                                                 $GLOBALS["LANG_FORUM_MESSAGE_ADD_MESSAGE_TIP"], 'Affectation', '');
                             closeParagraph();
                         }
                     }
                     else
                     {
                         // No forum topic message found
                         openParagraph('nbentriesfound');
                         echo $GLOBALS['LANG_NO_RECORD_FOUND'];
                         closeParagraph();
                     }
                 }
                 else
                 {
                     // The supporter isn't allowed to view the messages of the forum topic
                     openParagraph('ErrorMsg');
                     echo $GLOBALS["LANG_ERROR_NOT_ALLOWED_TO_CREATE_OR_UPDATE"];
                     closeParagraph();
                 }
             }
             else
             {
                 // The forum topic ID doesn't exist or is wrong
                 openParagraph('ErrorMsg');
                 echo $GLOBALS["LANG_ERROR_WRONG_FORUM_TOPIC_ID"];
                 closeParagraph();
             }
         }
         else
         {
             // The supporter isn't allowed to view the messages of the forum topic
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


/**
 * Display the form to create a new forum message or update a message in a given forum topic,
 * in the current row of the table of the web page, in the graphic interface in XHTML
 *
 * @author Christophe Javouhey
 * @version 1.1
 *     - 2022-02-07 : add tooltips on editor icons
 *
 * @since 2021-04-17
 *
 * @param $DbConnection                 DB object             Object of the opened database connection
 * @param $ForumMessageID               String                ID of the forum message to update [1..n]
 * @param $ForumTopicID                 String                ID of the forum topic in which the new message will be created [1..n]
 * @param $ProcessFormPage              String                URL of the page which will process the form
 * @param $AccessRules                  Array of Integers     List used to select only some support members
 *                                                            allowed to create a new forum message in the given topic
 * @param $$ReplyToMessageID            String                ID of the "reply to" message [1..n], NULL if it isn't a reply to message
 */
 function displayForumTopicMessageForm($DbConnection, $ForumMessageID, $ForumTopicID, $ProcessFormPage, $AccessRules = array(), $ReplyToMessageID = NULL)
 {
     // The supporter must be logged,
     if (isSet($_SESSION["SupportMemberID"]))
     {
         // The supporter must be allowed to create a new forum message
         $cUserAccess = FCT_ACT_NO_RIGHTS;
         if (isExistingForumTopic($DbConnection, $ForumTopicID))
         {
             // Creation mode
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

             if (in_array($cUserAccess, array(FCT_ACT_CREATE, FCT_ACT_UPDATE)))
             {
                 // We get details about the forum topic concerned by the new message
                 $ForumTopicRecord = getTableRecordInfos($DbConnection, "ForumTopics", $ForumTopicID);

                 // We get forum category access of the loggued supporter
                 $ForumCategoryID = $ForumTopicRecord['ForumCategoryID'];
                 $SupportMemberID = $_SESSION['SupportMemberID'];
                 $SupportMemberStateID = $_SESSION['SupportMemberStateID'];

                 $ArraySupportMemberAccess = getForumCategoryAccess($DbConnection, array($ForumCategoryID), array(),
                                                                    array($SupportMemberStateID), 'ForumCategoryID, SupportMemberStateID');

                 $cUserForumAccess = FORUM_ACCESS_NO_ACCESS;
                 if ((isset($ArraySupportMemberAccess['ForumCategoryID'])) && (!empty($ArraySupportMemberAccess['ForumCategoryID'])))
                 {
                     $cUserForumAccess = $ArraySupportMemberAccess['ForumCategoryAccess'][0];
                 }

                 if (in_array($cUserForumAccess, array(FORUM_ACCESS_CREATE_TOPIC, FORUM_ACCESS_WRITE_MSG)))
                 {
                     // The supporter can create messages in this forum topic (and category)
                     $DefaultEditorContentMessage = '';

                     // We check if it's an update of an existing message
                     if ($ForumMessageID == 0)
                     {
                         $ForumMessageRecord = array(
                                                     'ForumMessageContent' => '',
                                                     'ForumMessagePicture' => ''
                                                    );
                     }
                     else
                     {
                         if (isExistingForumMessage($DbConnection, $ForumMessageID))
                         {
                             // We get its content
                             $ForumMessageRecord = getTableRecordInfos($DbConnection, "ForumMessages", $ForumMessageID);
                             $DefaultEditorContentMessage = $ForumMessageRecord['ForumMessageContent'];
                         }
                     }

                     // We check if it's a "reply to" message
                     if ($ReplyToMessageID > 0)
                     {
                         if (isExistingForumMessage($DbConnection, $ReplyToMessageID))
                         {
                             // We get its content
                             $ForumMessageReplyToRecord = getTableRecordInfos($DbConnection, "ForumMessages", $ReplyToMessageID);
                             $DefaultEditorContentMessage = "<blockquote>".$ForumMessageReplyToRecord['ForumMessageContent']."</blockquote><br />";
                         }
                     }

                     // Display the category name
                     echo "[ ".generateAowIDHyperlink(getForumCategoryName($DbConnection, $ForumCategoryID), $ForumCategoryID,
                                                      "DisplayForumCategoryTopics.php",
                                                      $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"], "", "")." ]";

                     // Display the topic title
                     echo generateBR(2).str_repeat("&nbsp;", 8)
                          ."[ ".generateAowIDHyperlink($ForumTopicRecord['ForumTopicTitle'], $ForumTopicID,
                                                       "DisplayForumTopicMessages.php",
                                                       $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"], "", "")." ]";

                     // Open a form
                     openForm("FormDetailsForumTopicMessage", "post", "$ProcessFormPage?".$GLOBALS["QUERY_STRING"], "",
                              "VerificationForumTopicMessage('".$GLOBALS["LANG_ERROR_JS_FORUM_TOPIC_MESSAGE"]."')");

                     // Display the table (frame) where the form will take place
                     $sFormNewForumMessageTitle = $GLOBALS["LANG_FORUM_TOPIC"].' : '.$ForumTopicRecord['ForumTopicTitle'];

                     openStyledFrame($sFormNewForumMessageTitle, "Frame", "Frame", "DetailsObjectForm");

                     // Creation datetime of the forum message
                     $CreationDate = date($GLOBALS["CONF_DATE_DISPLAY_FORMAT"].' '.$GLOBALS["CONF_TIME_DISPLAY_FORMAT"]);

                     // We get infos about the author of the forum message
                     $ArrayInfosLoggedSupporter = getSupportMemberInfos($DbConnection, $SupportMemberID);
                     $Author = $ArrayInfosLoggedSupporter["SupportMemberLastname"].' '.$ArrayInfosLoggedSupporter["SupportMemberFirstname"]
                               .' ('.getSupportMemberStateName($DbConnection, $ArrayInfosLoggedSupporter["SupportMemberStateID"]).')';
                     $Author .= generateInputField("hidSupportMemberID", "hidden", "", "", "", $SupportMemberID);

                     // <<< ForumMessagePicture INPUTFIELD >>>
                     // We use the icons list
                     $Picture = '';
                     $ArrayPictureList = $GLOBALS['CONF_FORUM_ICONS']['TopicIcons'];

                     // We get picture of the family linked to this support member
                     if ((isset($_SESSION['FamilyID'])) && ($_SESSION['FamilyID'] > 0))
                     {
                         $FamilyRecord = getTableRecordInfos($DbConnection, "Families", $_SESSION['FamilyID']);
                         if (isset($FamilyRecord['FamilyID']))
                         {
                             // We add the pictures if set
                             if ((!empty($FamilyRecord['FamilyMainPicture']))
                                 && (file_exists($GLOBALS['CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY_HDD'].$FamilyRecord['FamilyMainPicture'])))
                             {
                                 $ArrayPictureList[] = $GLOBALS['CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY'].$FamilyRecord['FamilyMainPicture'];
                             }

                             if ((!empty($FamilyRecord['FamilySecondPicture']))
                                 && (file_exists($GLOBALS['CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY_HDD'].$FamilyRecord['FamilySecondPicture'])))
                             {
                                 $ArrayPictureList[] = $GLOBALS['CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY'].$FamilyRecord['FamilySecondPicture'];
                             }
                         }
                     }

                     foreach($ArrayPictureList as $i => $CurrentPicture)
                     {
                         $CurrentFilename = basename($CurrentPicture);
                         $bChecked = FALSE;
                         if ($CurrentFilename == $ForumMessageRecord['ForumMessagePicture'])
                         {
                             $bChecked = TRUE;
                         }

                         // Set a style if the picture is a family picture
                         $PicStyle = '';
                         if ((isset($_SESSION['FamilyID'])) && ($_SESSION['FamilyID'] > 0))
                         {
                             $Prefix = "F".$_SESSION['FamilyID']."-";
                             if (substr($CurrentFilename, 0, strlen($Prefix)) == $Prefix)
                             {
                                 $PicStyle = 'LittlePicture';
                             }
                         }

                         $Picture .= generateInputField("radMessagePicture", "radio", "1", "1", $GLOBALS["LANG_FORUM_MESSAGE_PICTURE_TIP"], $CurrentFilename,
                                                        FALSE, $bChecked).generateStyledPicture($CurrentPicture, '', $PicStyle)."&nbsp;&nbsp;";
                     }

                     // <<< ForumMessageContent area >>>
                     $ArrayEditorButtons = array(
                                                 'button' => array(
                                                                   1 => array('.ql-bold', '.ql-italic', '.ql-underline', '.ql-strike',
                                                                              '.ql-blockquote', '.ql-code-block', '.ql-direction',
                                                                              '.ql-link', '.ql-image', '.ql-video', '.ql-emoji', '.ql-clean'),
                                                                   2 => array('.ql-script', '.ql-header', '.ql-list', '.ql-indent')
                                                                  ),
                                                 'span' => array(
                                                                 1 => array('.ql-font', '.ql-color-picker', '.ql-background', '.ql-header',
                                                                            '.ql-align')
                                                                ),
                                                 '#ql-picker-options-1' => array(
                                                                                 35 => array(' .ql-picker-item')
                                                                                )
                                                );

                     $MessageContent = "<div id=\"editor\">$DefaultEditorContentMessage</div>\n";
                     $MessageContent .= generateInputField("hidTopicContent", "hidden", "", "", "", "");

                     $MessageContent .= "<script>\n
                                         const Tooltip = Quill.import('ui/tooltip');
                                         const toolbarOptions = {
                                                               container: [
                                                                           ['bold', 'italic', 'underline', 'strike', { 'script': 'sub'}, { 'script': 'super' }],
                                                                           ['blockquote', 'code-block'],
                                                                           [{ 'font': [] }, { 'color': [] }, { 'background': [] }],
                                                                           [{ 'header': 1 }, { 'header': 2 }],
                                                                           [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                                                                           [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                                                           [{ 'indent': '-1'}, { 'indent': '+1' }, { 'align': [] }, { 'direction': 'rtl' }],
                                                                           ['link', 'image', 'video', 'emoji'],
                                                                           ['clean']
                                                                          ],
                                                               handlers: {
                                                                          'emoji': function() {}
                                                                         }
                                                              };

                                       var quill = new Quill('#editor', {
                                                                         modules: {
                                                                                   'emoji-toolbar': true,
                                                                                   'emoji-textarea': false,
                                                                                   'emoji-shortname': false,
                                                                                   toolbar: toolbarOptions,
                                                                                   imageResize: {
                                                                                                 displaySize: true
                                                                                                }
                                                                                  },
                                                                         theme: 'snow'
                                                            });

                                       var toolbar = quill.container.previousSibling;\n";

                     // Display tooltips on icons of the editor
                     foreach($ArrayEditorButtons as $elt => $ArrayCurrentElts)
                     {
                         foreach($ArrayCurrentElts as $iNbElts => $ArrayCurrentEltStyles)
                         {
                             foreach($ArrayCurrentEltStyles as $CurrentEditButton)
                             {
                                 switch($iNbElts)
                                 {
                                     case 1:
                                         $sLangVarName = 'LANG_FORUM_EDITOR_'.strToUpper(str_replace(array('.ql-', '-', ' '),
                                                                                                     array('', '_', ''), $CurrentEditButton))."_TIP";

                                         if (isset($GLOBALS[$sLangVarName]))
                                         {
                                             $MessageContent .= "                 toolbar.querySelector('$elt$CurrentEditButton').setAttribute('title', '"
                                                                                                        .$GLOBALS[$sLangVarName]."');\n";
                                         }
                                         break;

                                     default:
                                         for($e = 0; $e < $iNbElts; $e++)
                                         {
                                             $sLangVarName = 'LANG_FORUM_EDITOR_'.strToUpper(str_replace(array('.ql-', '-', ' '),
                                                                                                         array('', '_', '',), $CurrentEditButton))."_".$e."_TIP";

                                             if (isset($GLOBALS[$sLangVarName]))
                                             {
                                                 $MessageContent .= "                 toolbar.querySelectorAll('$elt$CurrentEditButton')[$e].setAttribute('title', '"
                                                                                                               .$GLOBALS[$sLangVarName]."');\n";
                                             }
                                         }
                                         break;
                                 }
                             }
                         }
                     }

                     $MessageContent .= "                 quill.focus();\n
                                          quill.setSelection(quill.getLength(), 0);
                                   </script>\n";

                     // Display the form
                     echo "<table id=\"MessageDetails\" cellspacing=\"0\" cellpadding=\"0\">\n<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_CREATION_DATE"]."</td><td class=\"Value\">$CreationDate, $Author</td>\n</tr>\n";
                     echo "<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_FORUM_MESSAGE_PICTURE"]."</td><td class=\"Value\">$Picture</td>\n</tr>\n";
                     echo "<tr>\n\t<td class=\"LabelEditor\" colspan=\"2\">$MessageContent</td>\n</tr>\n";
                     echo "</table>\n";

                     insertInputField("hidForumCategoryID", "hidden", "", "", "", $ForumCategoryID);
                     insertInputField("hidForumTopicID", "hidden", "", "", "", $ForumTopicID);
                     insertInputField("hidForumMessageID", "hidden", "", "", "", $ForumMessageID);
                     insertInputField("hidForumReplyToMessageID", "hidden", "", "", "", $ReplyToMessageID);
                     closeStyledFrame();

                     // We display the buttons
                     echo "<table class=\"validation\">\n<tr>\n\t<td>";
                     insertInputField("bSubmit", "submit", "", "", $GLOBALS["LANG_SUBMIT_BUTTON_TIP"], $GLOBALS["LANG_SUBMIT_BUTTON_CAPTION"]);
                     echo "</td><td class=\"FormSpaceBetweenButtons\"></td><td>";
                     insertInputField("bReset", "reset", "", "", $GLOBALS["LANG_RESET_BUTTON_TIP"], $GLOBALS["LANG_RESET_BUTTON_CAPTION"], FALSE, FALSE, "onclick=\"ResetEditorContent();\"");
                     echo "</td>\n</tr>\n</table>\n";

                     closeForm();

                     // Display the last messages of the forum topic
                     displayBR(2);

                     $TabParams = array('ForumTopicID' => $ForumTopicID);
                     $ArrayRecords = dbSearchForumMessage($DbConnection, $TabParams, "ForumMessageDate DESC", 1, $GLOBALS["CONF_RECORDS_PER_PAGE"]);

                     // There are some forum messages found
                     if ((isset($ArrayRecords['ForumMessageID'])) && (!empty($ArrayRecords['ForumMessageID'])))
                     {
                         $ArrayCaptions = array($GLOBALS["LANG_FORUM_TOPIC_AUTHOR"], $ForumTopicRecord['ForumTopicTitle']);
                         $ArraySorts = array("", "");

                         foreach($ArrayRecords["ForumMessageID"] as $i => $CurrentValue)
                         {
                             // The author of the message
                             $sMessageAuthor = $ArrayRecords["SupportMemberLastname"][$i].' '.$ArrayRecords["SupportMemberFirstname"][$i]
                                              .generateBR(1).' ('.$ArrayRecords["SupportMemberStateName"][$i].')';

                             // We check if there is a picture to display
                             if (!empty($ArrayRecords["ForumMessagePicture"][$i]))
                             {
                                 $PicStyle = '';
                                 if (preg_match("/F[0-9]+-[0-9]_/", $ArrayRecords["ForumMessagePicture"][$i]) == 0)
                                 {
                                     // The picture is an icon : we search its path in $CONF_FORUM_ICONS['TopicIcons']
                                     $sPicPath = '';
                                     foreach($GLOBALS['CONF_FORUM_ICONS']['TopicIcons'] as $ic => $CurrentIcon)
                                     {
                                         if (basename($CurrentIcon) == $ArrayRecords["ForumMessagePicture"][$i])
                                         {
                                             $sPicPath = $CurrentIcon;
                                         }
                                     }
                                 }
                                 else
                                 {
                                     // It's a picture of the family
                                     $PicStyle = 'LittlePicture';
                                     $sPicPath = $GLOBALS['CONF_UPLOAD_FAMILY_PICTURE_FILES_DIRECTORY'].$ArrayRecords["ForumMessagePicture"][$i];
                                 }

                                 $sMessageAuthor .= generateBR(2)
                                                    .generateStyledPicture($sPicPath, '', $PicStyle);
                             }

                             $ArrayData[0][] = $sMessageAuthor;

                             // The message content
                             $MessageCellContent = "<table class=\"ForumMessageToolbar\" cellspacing=\"0\" id=\"Msg$CurrentValue\">";

                             // Message date
                             $MessageCellContent .= "<tr><td class=\"ForumMessageDate\">".$GLOBALS['LANG_FORUM_MESSAGE_POSTED_DATE'].' '
                                                    .date($GLOBALS["CONF_DATE_DISPLAY_FORMAT"].' '.$GLOBALS["CONF_TIME_DISPLAY_FORMAT"],
                                                          strtotime($ArrayRecords["ForumMessageDate"][$i]))."</td></tr>\n";

                             // We check if the forum message has uploaded files
                             $UploadedFilesList = '';
                             $ArrayUploadedFiles = getForumMessageUploadedFiles($DbConnection, $CurrentValue, array(), 'UploadedFileDate');
                             if ((isset($ArrayUploadedFiles['UploadedFileID'])) && (!empty($ArrayUploadedFiles['UploadedFileID'])))
                             {
                                 $UploadedFilesList = "<dl class=\"ForumMessageUploadedFilesList\">\n";

                                 foreach($ArrayUploadedFiles['UploadedFileID'] as $uf => $CurrentUploadedFileID)
                                 {
                                     // Url to download the file (one directory for each year)
                                     $UploadedFileUrl = $GLOBALS['CONF_UPLOAD_FORUM_MESSAGE_FILES_DIRECTORY']
                                                        .date('Y', strtotime($ArrayUploadedFiles['UploadedFileDate'][$uf]))
                                                        .'/'.$ArrayUploadedFiles['UploadedFileName'][$uf];

                                     $UploadedFilesList .= "<dt>".generateStyledPicture($GLOBALS['CONF_FORUM_ICONS']['DownloadFile'], '', '').' '
                                                                                        .generateAowIDHyperlink($ArrayUploadedFiles['UploadedFileName'][$uf],
                                                                                        $CurrentUploadedFileID, $UploadedFileUrl,
                                                                                        $GLOBALS["LANG_FORUM_MESSAGE_DOWNLOAD_FILE_TIP"],  "", "_blank");

                                     if ($bCanDeleteFiles)
                                     {
                                         // Button to delete the file
                                         $UploadedFilesList .= "&nbsp;&nbsp;".generateStyledPictureHyperlink($GLOBALS["CONF_DELETE_ICON"],
                                                                                                             "DeleteForumTopicMessageFile.php?Cr=".md5($CurrentUploadedFileID)
                                                                                                             ."&amp;Id=$CurrentUploadedFileID",
                                                                                                             $GLOBALS["LANG_DELETE_FILE_INSTRUCTIONS"], '', '',
                                                                                                             " onclick=\"return ConfirmationBox('".$GLOBALS['LANG_JS_FORUM_MESSAGE_CONFIRM_DELETE_FILE']."');\"");
                                     }

                                     $UploadedFilesList .= "</dt>\n";

                                     // We check if the file has a description
                                     if (!empty($ArrayUploadedFiles['UploadedFileDescription'][$uf]))
                                     {
                                         $UploadedFilesList .= "<dd>".$ArrayUploadedFiles['UploadedFileDescription'][$uf]."</dd>\n";
                                     }
                                 }

                                 $UploadedFilesList .= "</dl>\n";
                             }

                             // Display the forum message content
                             // Before, we must treat the message content
                             $sForumMessageContent = stripslashes($ArrayRecords["ForumMessageContent"][$i]);

                             // It's a "reply to" message ?
                             if (!empty($ArrayRecords["ForumReplyToMessageID"][$i]))
                             {
                                 // Get infos about the author of the quoted message
                                 $ArrayMsgAuthorInfos = getForumMessageAuthorInfos($DbConnection, $ArrayRecords["ForumReplyToMessageID"][$i]);
                                 $sAuthorInfos = $ArrayMsgAuthorInfos['SupportMemberLastname'].' '.$ArrayMsgAuthorInfos['SupportMemberFirstname'].' ('.
                                                 $ArrayMsgAuthorInfos['SupportMemberStateName'].') '.$GLOBALS['LANG_FORUM_MESSAGE_QUOTED_WROTE'].' : ';

                                 // We format the quoted message : we get position of all "</blockquote><blockquote>" tags;
                                 $ArrayQuotedTags = explode("</blockquote><blockquote>", $sForumMessageContent);
                                 $iNbElements = count($ArrayQuotedTags);
                                 if ($iNbElements == 1)
                                 {
                                     // Only <blockquote>...</blockquote>
                                     $sSingleQuoteContent = '';
                                     $CurrentMsgPart = $ArrayQuotedTags[0];

                                     // First part : we search the <blockquote> tag to add before a <div> tag
                                     $iPos = stripos($CurrentMsgPart, "<blockquote>");
                                     if ($iPos !== FALSE)
                                     {
                                         $iPosEnd = stripos($CurrentMsgPart, "</blockquote>", $iPos + strlen("<blockquote>"));
                                         if ($iPosEnd !== FALSE)
                                         {
                                             $sStartMsgPart = substr($CurrentMsgPart, 0, $iPos);
                                             $sQuoteMsgPart = substr($CurrentMsgPart, $iPos, $iPosEnd);
                                             $sEndMsgPart = substr($CurrentMsgPart, $iPosEnd + strlen("</blockquote>"));

                                             // We check if the quotedmessage is in this page of the topic
                                             if (in_array($ArrayRecords["ForumReplyToMessageID"][$i], $ArrayRecords["ForumMessageID"]))
                                             {
                                                 // Yes : the url is just an anchor
                                                 $sGoToMsgUrl = "#Msg".$ArrayRecords["ForumReplyToMessageID"][$i];
                                             }
                                             else
                                             {
                                                 // No : the quoted message is in another page of the topic
                                                 // We must compute the n° of the page
                                                 $iMsgPosInTopic = getForumTopicMessagePosInTopic($DbConnection, $ArrayRecords["ForumReplyToMessageID"][$i]);
                                                 $iPageNum = ceil($iMsgPosInTopic / $GLOBALS["CONF_RECORDS_PER_PAGE"]);
                                                 $sGoToMsgUrl = "$ProcessFormPage?Pg=$iPageNum&amp;Cr=".md5($ForumTopicID)
                                                                ."&amp;Id=$ForumTopicID#Msg".$ArrayRecords["ForumReplyToMessageID"][$i];
                                             }

                                             $sSingleQuoteContent = "$sStartMsgPart<div class=\"ForumReplyToMessage\">
                                                                    <p><a href=\"$sGoToMsgUrl\" title=\"\">$sAuthorInfos</a></p>$sQuoteMsgPart</div>$sEndMsgPart";

                                             $sForumMessageContent = $sSingleQuoteContent;
                                         }
                                     }
                                 }
                                 else
                                 {
                                     foreach($ArrayQuotedTags as $qt => $CurrentMsgPart)
                                     {
                                         if ($qt == 0)
                                         {
                                             // First part : we search the <blockquote> tag to add before a <div> tag
                                             $iPos = stripos($CurrentMsgPart, "<blockquote>");
                                             if ($iPos !== FALSE)
                                             {
                                                 $sStartMsgPart = substr($CurrentMsgPart, 0, $iPos);
                                                 $sEndMsgPart = substr($CurrentMsgPart, $iPos);

                                                 // We check if the quotedmessage is in this page of the topic
                                                 if (in_array($ArrayRecords["ForumReplyToMessageID"][$i], $ArrayRecords["ForumMessageID"]))
                                                 {
                                                     // Yes : the url is just an anchor
                                                     $sGoToMsgUrl = "#Msg".$ArrayRecords["ForumReplyToMessageID"][$i];
                                                 }
                                                 else
                                                 {
                                                     // No : the quoted message is in another page of the topic
                                                     // We must compute the n° of the page
                                                     $iMsgPosInTopic = getForumTopicMessagePosInTopic($DbConnection, $ArrayRecords["ForumReplyToMessageID"][$i]);
                                                     $iPageNum = ceil($iMsgPosInTopic / $GLOBALS["CONF_RECORDS_PER_PAGE"]);
                                                     $sGoToMsgUrl = "$ProcessFormPage?Pg=$iPageNum&amp;Cr=".md5($ForumTopicID)
                                                                    ."&amp;Id=$ForumTopicID#Msg".$ArrayRecords["ForumReplyToMessageID"][$i];
                                                 }

                                                 $ArrayQuotedTags[$qt] = "$sStartMsgPart<div class=\"ForumReplyToMessage\">
                                                                          <p><a href=\"$sGoToMsgUrl\" title=\"\">$sAuthorInfos</a></p>$sEndMsgPart";
                                             }
                                         }
                                         elseif ($qt == $iNbElements - 1)
                                         {
                                             // Last part : we search the </blockquote> tag to add after a </div> tag
                                             $iPos = stripos($CurrentMsgPart, "</blockquote>");
                                             if ($iPos !== FALSE)
                                             {
                                                 $sStartMsgPart = substr($CurrentMsgPart, 0, $iPos + strlen("</blockquote>"));
                                                 $sEndMsgPart = substr($CurrentMsgPart, $iPos + strlen("</blockquote>"));
                                                 $ArrayQuotedTags[$qt] = "$sStartMsgPart</div>$sEndMsgPart";
                                             }
                                         }
                                     }

                                     $sForumMessageContent = implode("</blockquote><blockquote>", $ArrayQuotedTags);
                                 }
                             }

                             // Replace smileys' codes by pictures
                             $ArraySmileysConv = array(
                                                       'Codes' => array(),
                                                       'Pictures' => array()
                                                      );
                             foreach($GLOBALS['CONF_FORUM_SMILEYS'] as $SmCode => $SmPicture)
                             {
                                 $ArraySmileysConv['Codes'][] = $SmCode;
                                 $ArraySmileysConv['SmPicture'][] = generateStyledPicture($GLOBALS['CONF_FORUM_ICONS']['SmileysDirectory'].$SmPicture,
                                                                                          $SmCode, '');
                             }

                             $sForumMessageContent = str_replace($ArraySmileysConv['Codes'], $ArraySmileysConv['SmPicture'], $sForumMessageContent);

                             // We check if the message has been updated
                             if (!empty($ArrayRecords["ForumMessageUpdateDate"][$i]))
                             {
                                 $sForumMessageContent .= "<p class=\"ForumUpdatedMessage\">".$GLOBALS['LANG_FORUM_MESSAGE_UPDATED_DATE'].' '
                                                          .date($GLOBALS["CONF_DATE_DISPLAY_FORMAT"].' '.$GLOBALS["CONF_TIME_DISPLAY_FORMAT"],
                                                                strtotime($ArrayRecords["ForumMessageUpdateDate"][$i]))."</p>";
                             }

                             $MessageCellContent .= "<tr><td class=\"ForumMessageContent\">".$UploadedFilesList.$sForumMessageContent."</td></tr>\n";
                             $MessageCellContent .= "</table>\n";
                             $ArrayData[1][] = $MessageCellContent;
                         }

                         // Display the table which contains the forum messages found
                         $ArraySortedFields = array("", "");
                         displayStyledTable($ArrayCaptions, $ArraySortedFields, '', $ArrayData, '', '', '', '',
                                            array(), '', array('ForumMessageAuthor', 'ForumMessageCell'), 'TopicMessagesList');
                     }
                 }
             }
             else
             {
                 // The supporter isn't allowed to create a new forum message
                 openParagraph('ErrorMsg');
                 echo $GLOBALS["LANG_ERROR_NOT_ALLOWED_TO_CREATE_OR_UPDATE"];
                 closeParagraph();
             }
         }
         else
         {
             // The forum topic doesn't exist
             openParagraph('ErrorMsg');
             echo $GLOBALS["LANG_ERROR_WRONG_FORUM_TOPIC_ID"];
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
 * Display the form to subscribe to a forum topic, in the current row of the table of the web page,
 * in the graphic interface in XHTML
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-22
 *
 * @param $DbConnection                 DB object             Object of the opened database connection
 * @param $ForumTopicID                 String                ID of the concerned forum topic to subscribe [0..n]
 * @param $ProcessFormPage              String                URL of the page which will process the form
 * @param $AccessRules                  Array of Integers     List used to select only some support members
 *                                                            allowed to subscribe to a forum topic
 */
 function displaySubscribeForumTopicForm($DbConnection, $ForumTopicID, $ProcessFormPage, $AccessRules = array())
 {
     // The supporter must be logged,
     if (isSet($_SESSION["SupportMemberID"]))
     {
         // The supporter must be allowed to create a new forum topic
         $cUserAccess = FCT_ACT_NO_RIGHTS;
         if (isExistingForumTopic($DbConnection, $ForumTopicID))
         {
             // Creation mode
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

             if (in_array($cUserAccess, array(FCT_ACT_CREATE, FCT_ACT_UPDATE, FCT_ACT_READ_ONLY)))
             {
                 // We get content of the tpoic and the first message
                 $ForumTopicRecord = getTableRecordInfos($DbConnection, "ForumTopics", $ForumTopicID);
                 $ForumCategoryID = $ForumTopicRecord['ForumCategoryID'];

                 // We get forum category access of the loggued supporter
                 $SupportMemberID = $_SESSION['SupportMemberID'];
                 $SupportMemberStateID = $_SESSION['SupportMemberStateID'];

                 $ArraySupportMemberAccess = getForumCategoryAccess($DbConnection, array($ForumCategoryID), array(),
                                                                    array($SupportMemberStateID), 'ForumCategoryID, SupportMemberStateID');

                 $cUserForumAccess = FORUM_ACCESS_NO_ACCESS;
                 if ((isset($ArraySupportMemberAccess['ForumCategoryID'])) && (!empty($ArraySupportMemberAccess['ForumCategoryID'])))
                 {
                     $cUserForumAccess = $ArraySupportMemberAccess['ForumCategoryAccess'][0];
                 }

                 if (in_array($cUserForumAccess, array(FORUM_ACCESS_CREATE_TOPIC, FORUM_ACCESS_WRITE_MSG, FORUM_ACCESS_READ_MSG)))
                 {
                     // The supporter is allowed to subscribe to this topic
                     // Display the category name
                     echo "[ ".generateAowIDHyperlink(getForumCategoryName($DbConnection, $ForumCategoryID), $ForumCategoryID,
                                                                           "DisplayForumCategoryTopics.php",
                                                                           $GLOBALS["LANG_VIEW_DETAILS_INSTRUCTIONS"], "", "")." ]";

                     // Open a form
                     openForm("FormSubscribeForumTopic", "post", "$ProcessFormPage?".$GLOBALS["QUERY_STRING"], "",
                              "VerificationSubscribeForumTopic('".$GLOBALS["LANG_ERROR_JS_SUBSCRIBE_FORUM_TOPIC_EMAIL"]."')");

                     // Display the table (frame) where the form will take place
                     openStyledFrame($GLOBALS["LANG_FORUM_TOPIC_SUBSCRIBTION"], "Frame", "Frame", "DetailsObjectForm");

                     // We get infos about the loggued supporter
                     $ArrayInfosLoggedSupporter = getSupportMemberInfos($DbConnection, $SupportMemberID);
                     $Author = $ArrayInfosLoggedSupporter["SupportMemberLastname"].' '.$ArrayInfosLoggedSupporter["SupportMemberFirstname"]
                               .' ('.getSupportMemberStateName($DbConnection, $ArrayInfosLoggedSupporter["SupportMemberStateID"]).')';
                     $Author .= generateInputField("hidSupportMemberID", "hidden", "", "", "", $SupportMemberID);

                     // <<< ForumTopicSubscribtionEmail INPUTFIELD >>>
                     $Email = generateInputField("sEmail", "text", "100", "50", $GLOBALS["LANG_FORUM_TOPIC_SUBSCRIBTION_EMAIL_TIP"], "");

                     // Display the form
                     echo "<table id=\"SubscribtionDetails\" cellspacing=\"0\" cellpadding=\"0\">\n<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_PROFIL"]."</td><td class=\"Value\">$Author</td>\n</tr>\n";
                     echo "<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_FORUM_TOPIC_SUBSCRIBTION_EMAIL"]."*</td><td class=\"Value\">$Email</td>\n</tr>\n";
                     echo "</table>\n";

                     insertInputField("hidForumTopicID", "hidden", "", "", "", $ForumTopicID);
                     closeStyledFrame();

                     // We display the buttons
                     echo "<table class=\"validation\">\n<tr>\n\t<td>";
                     insertInputField("bSubmit", "submit", "", "", $GLOBALS["LANG_SUBMIT_BUTTON_TIP"], $GLOBALS["LANG_SUBMIT_BUTTON_CAPTION"]);
                     echo "</td><td class=\"FormSpaceBetweenButtons\"></td><td>";
                     insertInputField("bReset", "reset", "", "", $GLOBALS["LANG_RESET_BUTTON_TIP"], $GLOBALS["LANG_RESET_BUTTON_CAPTION"]);
                     echo "</td>\n</tr>\n</table>\n";

                     closeForm();
                 }
                 else
                 {
                     // The supporter isn't allowed to create a new forum topic
                     openParagraph('ErrorMsg');
                     echo $GLOBALS["LANG_ERROR_NOT_ALLOWED_TO_CREATE_OR_UPDATE"];
                     closeParagraph();
                 }
             }
             else
             {
                 // The supporter isn't allowed to create a new forum topic
                 openParagraph('ErrorMsg');
                 echo $GLOBALS["LANG_ERROR_NOT_ALLOWED_TO_CREATE_OR_UPDATE"];
                 closeParagraph();
             }
         }
         else
         {
             // The forum category doesn't exist
             openParagraph('ErrorMsg');
             echo $GLOBALS["LANG_ERROR_WRONG_FORUM_TOPIC_ID"];
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
?>