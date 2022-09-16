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
 * Common module : library of database functions used for the forum tables
 *
 * @author Christophe Javouhey
 * @version 3.7
 * @since 2021-04-09
 */


/**
 * Check if a forum category exists in the ForumCategories table, thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-09
 *
 * @param $DbConnection         DB object    Object of the opened database connection
 * @param $ForumCategoryID      Integer      ID of the forum category searched [1..n]
 *
 * @return Boolean              TRUE if the forum category exists, FALSE otherwise
 */
 function isExistingForumCategory($DbConnection, $ForumCategoryID)
 {
     if ($ForumCategoryID > 0)
     {
         $DbResult = $DbConnection->query("SELECT ForumCategoryID FROM ForumCategories
                                           WHERE ForumCategoryID = $ForumCategoryID");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 1)
             {
                 // The forum category exists
                 return TRUE;
             }
         }
     }

     // The forum category doesn't exist
     return FALSE;
 }


/**
 * Give the ID of a forum category thanks to its name
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-09
 *
 * @param $DbConnection         DB object    Object of the opened database connection
 * @param $ForumCategoryName    String       Name of the forum category searched
 *
 * @return Integer              ID of the forum category, 0 otherwise
 */
 function getForumCategoryID($DbConnection, $ForumCategoryName)
 {
     $DbResult = $DbConnection->query("SELECT ForumCategoryID FROM ForumCategories
                                       WHERE ForumCategoryName = \"$ForumCategoryName\"");
     if (!DB::isError($DbResult))
     {
         if ($DbResult->numRows() != 0)
         {
             $Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC);
             return $Record["ForumCategoryID"];
         }
     }

     // ERROR
     return 0;
 }


/**
 * Give the name of a forum category thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-09
 *
 * @param $DbConnection         DB object    Object of the opened database connection
 * @param $ForumCategoryID      Integer      ID of the forum category searched
 *
 * @return String               Name of the forum category, empty string otherwise
 */
 function getForumCategoryName($DbConnection, $ForumCategoryID)
 {
     $DbResult = $DbConnection->query("SELECT ForumCategoryName FROM ForumCategories
                                       WHERE ForumCategoryID = $ForumCategoryID");
     if (!DB::isError($DbResult))
     {
         if ($DbResult->numRows() != 0)
         {
             $Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC);
             return $Record["ForumCategoryName"];
         }
     }

     // ERROR
     return "";
 }


/**
 * Give the language of a forum category thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-16
 *
 * @param $DbConnection         DB object    Object of the opened database connection
 * @param $ForumCategoryID      Integer      ID of the forum category searched
 *
 * @return String               Language of the forum category, empty string otherwise
 */
 function getForumCategoryDefaultLang($DbConnection, $ForumCategoryID)
 {
     $DbResult = $DbConnection->query("SELECT ForumCategoryDefaultLang FROM ForumCategories
                                       WHERE ForumCategoryID = $ForumCategoryID");
     if (!DB::isError($DbResult))
     {
         if ($DbResult->numRows() != 0)
         {
             $Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC);
             return $Record["ForumCategoryDefaultLang"];
         }
     }

     // ERROR
     return "";
 }


/**
 * Add a forum category in the ForumCategories table
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-09
 *
 * @param $DbConnection                  DB object    Object of the opened database connection
 * @param $ForumCategoryName             String       Name of the forum category
 * @param $ForumCategoryDefaultLang      String       Default language of the forum category (en, fr, oc...)
 * @param $ForumCategoryDescription      String       Description of the forum category
 *
 * @return Integer                       The primary key of the forum category [1..n], 0 otherwise
 */
 function dbAddForumCategory($DbConnection, $ForumCategoryName, $ForumCategoryDefaultLang = 'en', $ForumCategoryDescription = NULL)
 {
     if ((!empty($ForumCategoryName)) && (!empty($ForumCategoryDefaultLang)))
     {
         // Check if the forum category is a new forum category
         $DbResult = $DbConnection->query("SELECT ForumCategoryID FROM ForumCategories
                                           WHERE ForumCategoryName = \"$ForumCategoryName\"");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 0)
             {
                 if (empty($ForumCategoryDescription))
                 {
                     $ForumCategoryDescription = "";
                 }
                 else
                 {
                     $ForumCategoryDescription = ", ForumCategoryDescription = \"$ForumCategoryDescription\"";
                 }

                 // It's a new forum category
                 $id = getNewPrimaryKey($DbConnection, "ForumCategories", "ForumCategoryID");
                 if ($id != 0)
                 {
                     $DbResult = $DbConnection->query("INSERT INTO ForumCategories SET ForumCategoryID = $id, ForumCategoryName = \"$ForumCategoryName\",
                                                       ForumCategoryDefaultLang = \"$ForumCategoryDefaultLang\" $ForumCategoryDescription");

                     if (!DB::isError($DbResult))
                     {
                         return $id;
                     }
                 }
             }
             else
             {
                 // The forum category already exists
                 $Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC);
                 return $Record['ForumCategoryID'];
             }
         }
     }

     // ERROR
     return 0;
 }


/**
 * Update an existing forum category in the ForumCategories table
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-09
 *
 * @param $DbConnection                  DB object    Object of the opened database connection
 * @param $ForumCategoryID               Integer      ID of the forum category to update [1..n]
 * @param $ForumCategoryName             String       Name of the forum category
 * @param $ForumCategoryDefaultLang      String       Default language of the forum category (en, fr, oc...)
 * @param $ForumCategoryDescription      String       Description of the forum category
 *
 * @return Integer                       The primary key of the forum category [1..n], 0 otherwise
 */
 function dbUpdateForumCategory($DbConnection, $ForumCategoryID, $ForumCategoryName = NULL, $ForumCategoryDefaultLang = NULL, $ForumCategoryDescription = NULL)
 {
     // The parameters which are NULL will be ignored for the update
     $ArrayParamsUpdate = array();

     // Verification of the parameters
     if (($ForumCategoryID < 1) || (!isInteger($ForumCategoryID)))
     {
         // ERROR
         return 0;
     }

     // Check if the ForumCategoryName is valide
     if (!is_null($ForumCategoryName))
     {
         if (empty($ForumCategoryName))
         {
             return 0;
         }
         else
         {
             // The ForumCategoryName field will be updated
             $ArrayParamsUpdate[] = "ForumCategoryName = \"$ForumCategoryName\"";
         }
     }

     // Check if the ForumCategoryDefaultLang is valide
     if (!is_null($ForumCategoryDefaultLang))
     {
         if (empty($ForumCategoryDefaultLang))
         {
             return 0;
         }
         else
         {
             // The ForumCategoryDefaultLang field will be updated
             $ArrayParamsUpdate[] = "ForumCategoryDefaultLang = \"$ForumCategoryDefaultLang\"";
         }
     }

     if (!is_Null($ForumCategoryDescription))
     {
         // The ForumCategoryDescription field will be updated
         $ArrayParamsUpdate[] = "ForumCategoryDescription = \"$ForumCategoryDescription\"";
     }

     // Here, the parameters are correct, we check if the forum category exists
     if (isExistingForumCategory($DbConnection, $ForumCategoryID))
     {
         // We check if the forum category name is unique
         $DbResult = $DbConnection->query("SELECT ForumCategoryID FROM ForumCategories
                                           WHERE ForumCategoryName = \"$ForumCategoryName\" AND ForumCategoryID <> $ForumCategoryID");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 0)
             {
                 // The forum category exists and is unique : we can update if there is at least 1 parameter
                 if (count($ArrayParamsUpdate) > 0)
                 {
                     $DbResult = $DbConnection->query("UPDATE ForumCategories SET ".implode(", ", $ArrayParamsUpdate)
                                                      ." WHERE ForumCategoryID = $ForumCategoryID");
                     if (!DB::isError($DbResult))
                     {
                         // Forum category updated
                         return $ForumCategoryID;
                     }
                 }
                 else
                 {
                     // The update isn't usefull
                     return $ForumCategoryID;
                 }
             }
         }
     }

     // ERROR
     return 0;
 }


/**
 * Get forum categories filtered by some criterion
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-09
 *
 * @param $DbConnection             DB object              Object of the opened database connection
 * @param $ArrayParams              Mixed array            Contains the criterion used to filter the forum categories
 * @param $OrderBy                  String                 Criteria used to sort the forum categories. If < 0, DESC is used,
 *                                                         otherwise ASC is used
 * @param $Page                     Integer                Number of the page to return [1..n]
 * @param $RecordsPerPage           Integer                Number of forum categories per page to return [1..n]
 *
 * @return Array of String          List of forum categories filtered, an empty array otherwise
 */
 function dbSearchForumCategory($DbConnection, $ArrayParams, $OrderBy = "", $Page = 1, $RecordsPerPage = 10)
 {
     // SQL request to find forum categories
     $Select = "SELECT fc.ForumCategoryID, fc.ForumCategoryName, fc.ForumCategoryDescription, fc.ForumCategoryDefaultLang";
     $From = "FROM ForumCategories fc";
     $Where = "WHERE 1=1";
     $Having = "";

     if (count($ArrayParams) >= 0)
     {
         // <<< ForumCategoryID field >>>
         if ((array_key_exists("ForumCategoryID", $ArrayParams)) && (!empty($ArrayParams["ForumCategoryID"])))
         {
             if (is_array($ArrayParams["ForumCategoryID"]))
             {
                 $Where .= " AND fc.ForumCategoryID IN ".constructSQLINString($ArrayParams["ForumCategoryID"]);
             }
             else
             {
                 $Where .= " AND fc.ForumCategoryID = ".$ArrayParams["ForumCategoryID"];
             }
         }

         // <<< ForumCategoryName field >>>
         if ((array_key_exists("ForumCategoryName", $ArrayParams)) && (!empty($ArrayParams["ForumCategoryName"])))
         {
             $Where .= " AND fc.ForumCategoryName LIKE \"".$ArrayParams["ForumCategoryName"]."\"";
         }

         // <<< ForumCategoryDescription field >>>
         if ((array_key_exists("ForumCategoryDescription", $ArrayParams)) && (!empty($ArrayParams["ForumCategoryDescription"])))
         {
             $Where .= " AND fc.ForumCategoryDescription LIKE \"".$ArrayParams["ForumCategoryDescription"]."\"";
         }

         // <<< ForumCategoryDefaultLang field >>>
         if ((array_key_exists("ForumCategoryDefaultLang", $ArrayParams)) && (!empty($ArrayParams["ForumCategoryDefaultLang"])))
         {
             $Where .= " AND fc.ForumCategoryDefaultLang LIKE \"".$ArrayParams["ForumCategoryDefaultLang"]."\"";
         }

         // <<< ForumCategoryNbMessages option >>>
         $bGetNbMessages = FALSE;
         if ((array_key_exists("ForumCategoryNbMessages", $ArrayParams)) && ($ArrayParams["ForumCategoryNbMessages"]))
         {
             $bGetNbMessages = TRUE;
             $Select .= ", COUNT(fm.ForumMessageID) AS NbMessages";
             $From .= " LEFT JOIN ForumTopics ft ON (ft.ForumCategoryID = fc.ForumCategoryID)
                        LEFT JOIN ForumMessages fm ON (fm.ForumTopicID = ft.ForumTopicID)";
         }

         // <<< ForumCategoriesAccess option >>>
         $bGetAccess = FALSE;
         if ((array_key_exists("ForumCategoryAccess", $ArrayParams)) && (!empty($ArrayParams["ForumCategoryAccess"])))
         {
             $bGetAccess = TRUE;
             $From .= ", ForumCategoriesAccess fca";
             $Where .= " AND fc.ForumCategoryID = fca.ForumCategoryID";

             if ((isset($ArrayParams["ForumCategoryAccess"]["Access"])) && (!empty($ArrayParams["ForumCategoryAccess"]["Access"])))
             {
                 $Where .= " AND fca.ForumCategoryAccess IN ".constructSQLINString($ArrayParams["ForumCategoryAccess"]["Access"]);
             }

             if ((isset($ArrayParams["ForumCategoryAccess"]["SupportMemberStateID"])) && (!empty($ArrayParams["ForumCategoryAccess"]["SupportMemberStateID"])))
             {
                 $Where .= " AND fca.SupportMemberStateID IN ".constructSQLINString($ArrayParams["ForumCategoryAccess"]["SupportMemberStateID"]);
             }
         }
     }

     // We take into account the page and the number of forum categories per page
     if ($Page < 1)
     {
         $Page = 1;
     }

     if ($RecordsPerPage < 0)
     {
         $RecordsPerPage = 10;
     }

     $Limit = '';
     if ($RecordsPerPage > 0)
     {
         $StartIndex = ($Page - 1) * $RecordsPerPage;
         $Limit = "LIMIT $StartIndex, $RecordsPerPage";
     }

     // We take into account the order by
     if ($OrderBy == "")
     {
         $StrOrderBy = "";
     }
     else
     {
         $StrOrderBy = " ORDER BY $OrderBy";
     }

     // We can launch the SQL request
     $DbResult = $DbConnection->query("$Select $From $Where GROUP BY fc.ForumCategoryID $Having $StrOrderBy $Limit");

     if (!DB::isError($DbResult))
     {
         if ($DbResult->numRows() != 0)
         {
             // Creation of the result array
             $ArrayRecords = array(
                                   "ForumCategoryID" => array(),
                                   "ForumCategoryName" => array(),
                                   "ForumCategoryDescription" => array(),
                                   "ForumCategoryDefaultLang" => array()
                                  );

             if ($bGetAccess)
             {
                 $ArrayRecords['ForumCategoriesAccess'] = array();
             }

             if ($bGetNbMessages)
             {
                 $ArrayRecords['NbMessages'] = array();
             }

             while($Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC))
             {
                 $ArrayRecords["ForumCategoryID"][] = $Record["ForumCategoryID"];
                 $ArrayRecords["ForumCategoryName"][] = $Record["ForumCategoryName"];
                 $ArrayRecords["ForumCategoryDescription"][] = $Record["ForumCategoryDescription"];
                 $ArrayRecords["ForumCategoryDefaultLang"][] = $Record["ForumCategoryDefaultLang"];

                 if ($bGetAccess)
                 {
                     // We get access rights of the forum category for each SupportMemberStateID
                     $ArrayAccess = getForumCategoryAccess($DbConnection, $Record["ForumCategoryID"],
                                                           $ArrayParams["ForumCategoryAccess"]["Access"],
                                                           $ArrayParams["ForumCategoryAccess"]["SupportMemberStateID"],
                                                           'ForumCategoryID, SupportMemberStateID');

                     $ArrayRecords['ForumCategoriesAccess'][] = $ArrayAccess;
                 }

                 if ($bGetNbMessages)
                 {
                     $ArrayRecords["NbMessages"][] = $Record["NbMessages"];
                 }
             }

             // Return result
             return $ArrayRecords;
         }
     }

     // ERROR
     return array();
 }


/**
 * Get the number of forum categories filtered by some criterion
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-09
 *
 * @param $DbConnection         DB object              Object of the opened database connection
 * @param $ArrayParams          Mixed array            Contains the criterion used to filter the forum categories
 *
 * @return Integer              Number of the forum categories found, 0 otherwise
 */
 function getNbdbSearchForumCategory($DbConnection, $ArrayParams)
 {
     // SQL request to find forum categories
     $Select = "SELECT fc.ForumCategoryID";
     $From = "FROM ForumCategories fc";
     $Where = "WHERE 1=1";
     $Having = "";

     if (count($ArrayParams) >= 0)
     {
         // <<< ForumCategoryID field >>>
         if ((array_key_exists("ForumCategoryID", $ArrayParams)) && (!empty($ArrayParams["ForumCategoryID"])))
         {
             if (is_array($ArrayParams["ForumCategoryID"]))
             {
                 $Where .= " AND fc.ForumCategoryID IN ".constructSQLINString($ArrayParams["ForumCategoryID"]);
             }
             else
             {
                 $Where .= " AND fc.ForumCategoryID = ".$ArrayParams["ForumCategoryID"];
             }
         }

         // <<< ForumCategoryName field >>>
         if ((array_key_exists("ForumCategoryName", $ArrayParams)) && (!empty($ArrayParams["ForumCategoryName"])))
         {
             $Where .= " AND fc.ForumCategoryName LIKE \"".$ArrayParams["ForumCategoryName"]."\"";
         }

         // <<< ForumCategoryDescription field >>>
         if ((array_key_exists("ForumCategoryDescription", $ArrayParams)) && (!empty($ArrayParams["ForumCategoryDescription"])))
         {
             $Where .= " AND fc.ForumCategoryDescription LIKE \"".$ArrayParams["ForumCategoryDescription"]."\"";
         }

         // <<< ForumCategoryDefaultLang field >>>
         if ((array_key_exists("ForumCategoryDefaultLang", $ArrayParams)) && (!empty($ArrayParams["ForumCategoryDefaultLang"])))
         {
             $Where .= " AND fc.ForumCategoryDefaultLang LIKE \"".$ArrayParams["ForumCategoryDefaultLang"]."\"";
         }

         // <<< ForumCategoriesAccess option >>>
         if ((array_key_exists("ForumCategoryAccess", $ArrayParams)) && (!empty($ArrayParams["ForumCategoryAccess"])))
         {
             $From .= ", ForumCategoriesAccess fca";
             $Where .= " AND fc.ForumCategoryID = fca.ForumCategoryID";

             if ((isset($ArrayParams["ForumCategoryAccess"]["Access"])) && (!empty($ArrayParams["ForumCategoryAccess"]["Access"])))
             {
                 $Where .= " AND fca.ForumCategoryAccess IN ".constructSQLINString($ArrayParams["ForumCategoryAccess"]["Access"]);
             }

             if ((isset($ArrayParams["ForumCategoryAccess"]["SupportMemberStateID"])) && (!empty($ArrayParams["ForumCategoryAccess"]["SupportMemberStateID"])))
             {
                 $Where .= " AND fca.SupportMemberStateID IN ".constructSQLINString($ArrayParams["ForumCategoryAccess"]["SupportMemberStateID"]);
             }
         }
     }

     // We can launch the SQL request
     $DbResult = $DbConnection->query("$Select $From $Where GROUP BY fc.ForumCategoryID $Having");
     if (!DB::isError($DbResult))
     {
         return $DbResult->numRows();
     }

     // ERROR
     return 0;
 }


/**
 * Check if a forum category access exists in the ForumCategoriesAccess table, thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-30
 *
 * @param $DbConnection               DB object    Object of the opened database connection
 * @param $ForumCategoryAccessID      Integer      ID of the forum category access searched [1..n]
 *
 * @return Boolean                    TRUE if the forum category access exists, FALSE otherwise
 */
 function isExistingForumCategoryAccess($DbConnection, $ForumCategoryAccessID)
 {
     if ($ForumCategoryAccessID > 0)
     {
         $DbResult = $DbConnection->query("SELECT ForumCategoryAccessID FROM ForumCategoriesAccess
                                           WHERE ForumCategoryAccessID = $ForumCategoryAccessID");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 1)
             {
                 // The forum category access exists
                 return TRUE;
             }
         }
     }

     // The forum category access doesn't exist
     return FALSE;
 }


/**
 * Give the category of a forum category access thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-30
 *
 * @param $DbConnection               DB object    Object of the opened database connection
 * @param $ForumCategoryAccessID      Integer      ID of the forum category access searched [1..n]
 *
 * @return String                     Forum category of the forum category access, empty string otherwise
 */
 function getForumCategoryAccesForumCategory($DbConnection, $ForumCategoryAccessID)
 {
     $DbResult = $DbConnection->query("SELECT ForumCategoryID FROM ForumCategoriesAccess
                                       WHERE ForumCategoryAccessID = $ForumCategoryAccessID");
     if (!DB::isError($DbResult))
     {
         if ($DbResult->numRows() != 0)
         {
             $Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC);
             return $Record["ForumCategoryID"];
         }
     }

     // ERROR
     return 0;
 }


/**
 * Add a forum category access in the ForumCategoriesAccess table
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-30
 *
 * @param $DbConnection                  DB object    Object of the opened database connection
 * @param $ForumCategoryID               Integer      ID of the concerned forum category by the access [1..n]
 * @param $SupportMemberStateID          Integer      ID of the support member sate concerned by the access [1..n]
 * @param $ForumCategoryAccess           Char         Type of access (c, w, r)
 *
 * @return Integer                       The primary key of the forum category access [1..n], 0 otherwise
 */
 function dbAddForumCategoryAccess($DbConnection, $ForumCategoryID, $SupportMemberStateID, $ForumCategoryAccess)
 {
     if (($ForumCategoryID > 0) && ($SupportMemberStateID > 0) && (!empty($ForumCategoryAccess)))
     {
         // Check if the forum category access is a new forum category access
         $DbResult = $DbConnection->query("SELECT ForumCategoryAccessID FROM ForumCategoriesAccess
                                           WHERE ForumCategoryID = $ForumCategoryID AND SupportMemberStateID = $SupportMemberStateID");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 0)
             {
                 // It's a new forum category access
                 $id = getNewPrimaryKey($DbConnection, "ForumCategoriesAccess", "ForumCategoryAccessID");
                 if ($id != 0)
                 {
                     $DbResult = $DbConnection->query("INSERT INTO ForumCategoriesAccess SET ForumCategoryAccessID = $id, ForumCategoryID = $ForumCategoryID,
                                                       SupportMemberStateID = $SupportMemberStateID, ForumCategoryAccess = \"$ForumCategoryAccess\"");

                     if (!DB::isError($DbResult))
                     {
                         return $id;
                     }
                 }
             }
             else
             {
                 // The forum category access already exists
                 $Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC);
                 return $Record['ForumCategoryAccessID'];
             }
         }
     }

     // ERROR
     return 0;
 }


/**
 * Update an existing forum category access in the ForumCategoriesAccess table
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-09
 *
 * @param $DbConnection                  DB object    Object of the opened database connection
 * @param $ForumCategoryAccessID         Integer      ID of the forum category access to update [1..n]
 * @param $ForumCategoryID               Integer      ID of the concerned forum category by the access [1..n]
 * @param $SupportMemberStateID          Integer      ID of the support member sate concerned by the access [1..n]
 * @param $ForumCategoryAccess           Char         Type of access (c, w, r)
 *
 * @return Integer                       The primary key of the forum category access [1..n], 0 otherwise
 */
 function dbUpdateForumCategoryAccess($DbConnection, $ForumCategoryAccessID, $ForumCategoryID, $SupportMemberStateID, $ForumCategoryAccess = NULL)
 {
     // The parameters which are NULL will be ignored for the update
     $ArrayParamsUpdate = array();

     // Verification of the parameters
     if (($ForumCategoryAccessID < 1) || (!isInteger($ForumCategoryAccessID)))
     {
         // ERROR
         return 0;
     }

     if (!is_null($ForumCategoryID))
     {
         if (($ForumCategoryID < 1) || (!isInteger($ForumCategoryID)))
         {
             // ERROR
             return 0;
         }
         else
         {
             $ArrayParamsUpdate[] = "ForumCategoryID = $ForumCategoryID";
         }
     }

     if (!is_null($SupportMemberStateID))
     {
         if (($SupportMemberStateID < 1) || (!isInteger($SupportMemberStateID)))
         {
             // ERROR
             return 0;
         }
         else
         {
             $ArrayParamsUpdate[] = "SupportMemberStateID = $SupportMemberStateID";
         }
     }

     // Check if the ForumCategoryAccess is valide
     if (!is_null($ForumCategoryAccess))
     {
         if (empty($ForumCategoryAccess))
         {
             return 0;
         }
         else
         {
             // The ForumCategoryAccess field will be updated
             $ArrayParamsUpdate[] = "ForumCategoryAccess = \"$ForumCategoryAccess\"";
         }
     }

     // Here, the parameters are correct, we check if the forum category access exists
     if (isExistingForumCategoryAccess($DbConnection, $ForumCategoryAccessID))
     {
         // We check if the forum category access is unique
         $DbResult = $DbConnection->query("SELECT ForumCategoryAccessID FROM ForumCategoriesAccess
                                           WHERE ForumCategoryID = $ForumCategoryID AND SupportMemberStateID = $SupportMemberStateID
                                           AND ForumCategoryAccessID <> $ForumCategoryAccessID");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 0)
             {
                 // The forum category exists and is unique : we can update if there is at least 1 parameter
                 if (count($ArrayParamsUpdate) > 0)
                 {
                     $DbResult = $DbConnection->query("UPDATE ForumCategoriesAccess SET ".implode(", ", $ArrayParamsUpdate)
                                                      ." WHERE ForumCategoryAccessID = $ForumCategoryAccessID");
                     if (!DB::isError($DbResult))
                     {
                         // Forum category access updated
                         return $ForumCategoryAccessID;
                     }
                 }
                 else
                 {
                     // The update isn't usefull
                     return $ForumCategoryAccessID;
                 }
             }
         }
     }

     // ERROR
     return 0;
 }


/**
 * Delete a forum category access, thanks to its ID.
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-30
 *
 * @param $DbConnection              DB object    Object of the opened database connection
 * @param $ForumCategoryAccessID     Integer      ID of the forum category access to delete [1..n]
 *
 * @return Boolean                   TRUE if the forum category access is deleted if it exists,
 *                                   FALSE otherwise
 */
 function dbDeleteForumCategoryAccess($DbConnection, $ForumCategoryAccessID)
 {
     // The parameters are correct?
     if ($ForumCategoryAccessID > 0)
     {
         // Delete the forum category access in the table
         $DbResult = $DbConnection->query("DELETE FROM ForumCategoriesAccess WHERE ForumCategoryAccessID = $ForumCategoryAccessID");
         if (!DB::isError($DbResult))
         {
             // Forum category access deleted
             return TRUE;
         }
     }

     // ERROR
     return FALSE;
 }


/**
 * Give the access of a forum category for support member states ID, thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-11
 *
 * @param $DbConnection              DB object    Object of the opened database connection
 * @param $ForumCategoryID           Integer      ID of the forum category for which we want access [1..n]
 * @param $ForumCategoryAccess       Char         Type of access right
 * @param $SupportMemberStateID      Integer      ID of the support membre state for which we want access [1..n]
 * @param $OrderBy                   String       To order the access
 *
 * @return Mixed array               All fields values of the forum category access for given support membre states ID
 *                                   if it exists, an empty array otherwise
 */
 function getForumCategoryAccess($DbConnection, $ForumCategoryID = array(), $ForumCategoryAccess = array(), $SupportMemberStateID = array(), $OrderBy = 'ForumCategoryID, SupportMemberStateID')
 {
     // Condition about forum category (array or single value)
     $ForumCategoryCondition = '';
     if (!empty($ForumCategoryID))
     {
         if (is_array($ForumCategoryID))
         {
             $ForumCategoryCondition = " AND fca.ForumCategoryID IN ".constructSQLINString($ForumCategoryID);
         }
         elseif ($ForumCategoryID > 0)
         {
             $ForumCategoryCondition = " AND fca.ForumCategoryID = $ForumCategoryID";
         }
     }

     // Condition about forum category access (array or single value)
     $ForumCategoryAccessCondition = '';
     if (!empty($ForumCategoryAccess))
     {
         if (is_array($ForumCategoryAccess))
         {
             $ForumCategoryAccessCondition = " AND fca.ForumCategoryAccess IN ".constructSQLINString($ForumCategoryAccess);
         }
         elseif (in_array($ForumCategoryAccess, array(FORUM_ACCESS_CREATE_TOPIC, FORUM_ACCESS_WRITE_MSG, FORUM_ACCESS_READ_MSG)))
         {
             $ForumCategoryAccessCondition = " AND fca.ForumCategoryAccess = \"$ForumCategoryAccess\"";
         }
     }

     // Condition about support member state (array or single value)
     $SupportMemberStateCondition = '';
     if (!empty($SupportMemberStateID))
     {
         if (is_array($SupportMemberStateID))
         {
             $SupportMemberStateCondition = " AND fca.SupportMemberStateID IN ".constructSQLINString($SupportMemberStateID);
         }
         elseif ($SupportMemberStateID > 0)
         {
             $SupportMemberStateCondition = " AND fca.SupportMemberStateID = $SupportMemberStateID";
         }
     }

     if (empty($OrderBy))
     {
         $OrderBy = 'ForumCategoryID, SupportMemberStateID';
     }

     // We get access of forum category for support member states ID
     $DbResult = $DbConnection->query("SELECT fc.ForumCategoryID, fca.SupportMemberStateID, fca.ForumCategoryAccessID, fca.ForumCategoryAccess
                                       FROM ForumCategories fc LEFT JOIN ForumCategoriesAccess fca ON (fc.ForumCategoryID = fca.ForumCategoryID)
                                       WHERE 1=1 $ForumCategoryCondition $ForumCategoryAccessCondition $SupportMemberStateCondition
                                       ORDER BY $OrderBy");

     if (!DB::isError($DbResult))
     {
         // Creation of the result array
         $ArrayRecords = array(
                               "ForumCategoryID" => array(),
                               "SupportMemberStateID" => array(),
                               "ForumCategoryAccessID" => array(),
                               "ForumCategoryAccess" => array()
                              );

         while($Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC))
         {
             $ArrayRecords["ForumCategoryID"][] = $Record["ForumCategoryID"];
             $ArrayRecords["SupportMemberStateID"][] = $Record["SupportMemberStateID"];
             $ArrayRecords["ForumCategoryAccessID"][] = $Record["ForumCategoryAccessID"];
             $ArrayRecords["ForumCategoryAccess"][] = $Record["ForumCategoryAccess"];
         }

         // Return result
         return $ArrayRecords;
     }

     // ERROR
     return array();
 }


/**
 * Check if a forum topic exists in the ForumTopics table, thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-11
 *
 * @param $DbConnection         DB object    Object of the opened database connection
 * @param $ForumTopicID         Integer      ID of the forum topic searched [1..n]
 *
 * @return Boolean              TRUE if the forum topic exists, FALSE otherwise
 */
 function isExistingForumTopic($DbConnection, $ForumTopicID)
 {
     if ($ForumTopicID > 0)
     {
         $DbResult = $DbConnection->query("SELECT ForumTopicID FROM ForumTopics
                                           WHERE ForumTopicID = $ForumTopicID");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 1)
             {
                 // The forum topic exists
                 return TRUE;
             }
         }
     }

     // The forum topic doesn't exist
     return FALSE;
 }


/**
 * Give the category ID of a forum topic thanks to its ID.
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-19
 *
 * @param $DbConnection         DB object    Object of the opened database connection
 * @param $ForumTopicID         Integer      ID of the forum topic searched [1..n]
 *
 * @return Integer              Forum category ID of the forum topic, empty string otherwise
 */
 function getForumTopicCategoryID($DbConnection, $ForumTopicID)
 {
     $DbResult = $DbConnection->query("SELECT ForumCategoryID FROM ForumTopics WHERE ForumTopicID = $ForumTopicID");
     if (!DB::isError($DbResult))
     {
         if ($DbResult->numRows() != 0)
         {
             $Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC);
             return $Record["ForumCategoryID"];
         }
     }

     // ERROR
     return "";
 }


/**
 * Give the author ID of a forum topic thanks to its ID.
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-23
 *
 * @param $DbConnection         DB object    Object of the opened database connection
 * @param $ForumTopicID         Integer      ID of the forum topic searched [1..n]
 *
 * @return Integer              SupportMember ID author of the forum topic,
 *                              empty string otherwise
 */
 function getForumTopicAuthorID($DbConnection, $ForumTopicID)
 {
     $DbResult = $DbConnection->query("SELECT SupportMemberID FROM ForumTopics WHERE ForumTopicID = $ForumTopicID");
     if (!DB::isError($DbResult))
     {
         if ($DbResult->numRows() != 0)
         {
             $Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC);
             return $Record["SupportMemberID"];
         }
     }

     // ERROR
     return "";
 }


/**
 * Give the language of a forum topic thanks to its ID. It's the language of the forum category
 * linked to the topic
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-16
 *
 * @param $DbConnection         DB object    Object of the opened database connection
 * @param $ForumTopicID         Integer      ID of the forum topic searched [1..n]
 *
 * @return String               Language of the forum category linked to the forum topic,
 *                              empty string otherwise
 */
 function getForumTopicDefaultLang($DbConnection, $ForumTopicID)
 {
     $DbResult = $DbConnection->query("SELECT fc.ForumCategoryDefaultLang FROM ForumCategories fc, ForumTopics ft
                                       WHERE fc.ForumCategoryID = ft.ForumCategoryID AND ft.ForumTopicID = $ForumTopicID");
     if (!DB::isError($DbResult))
     {
         if ($DbResult->numRows() != 0)
         {
             $Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC);
             return $Record["ForumCategoryDefaultLang"];
         }
     }

     // ERROR
     return "";
 }


/**
 * Add a forum topic in the ForumTopics table
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-11
 *
 * @param $DbConnection                  DB object    Object of the opened database connection
 * @param $ForumTopicTitle               String       Title of the forum topic
 * @param $ForumTopicDate                String       Creation date/time of the forum topic (yyyy-mm-dd hh:mm:ss)
 * @param $ForumCategoryID               Integer      ID of the category linked in which the topic is created [1..n]
 * @param $SupportMemberID               Integer      ID of the support member author of the topic [1..n]
 * @param $ForumTopicStatus              Integer      Status of the forum topic [0..n]
 * @param $ForumTopicIcon                Integer      Icon displayed for the topic [0..n]
 * @param $ForumTopicExpirationDate      String       Expiratoin date of the forum topic (yyyy-mm-dd)
 * @param $ForumTopicRank                Integer      Rank to display the topic if always on top [1..n]
 *
 * @return Integer                       The primary key of the forum topic [1..n], 0 otherwise
 */
 function dbAddForumTopic($DbConnection, $ForumTopicTitle, $ForumTopicDate, $ForumCategoryID, $SupportMemberID, $ForumTopicStatus = 0, $ForumTopicIcon = 0, $ForumTopicExpirationDate = NULL, $ForumTopicRank = NULL)
 {
     if ((!empty($ForumTopicTitle)) && (!empty($ForumTopicDate)) && ($ForumCategoryID > 0) && ($SupportMemberID > 0)
         && ($ForumTopicStatus >= 0) && ($ForumTopicIcon >= 0))
     {
         // Check if the creation date of the topic is valide
         if (preg_match("[\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d]", $ForumTopicDate) == 0)
         {
             return 0;
         }
         else
         {
             $ForumTopicDate = ", ForumTopicDate = \"$ForumTopicDate\"";
         }

         // Check if the ForumTopicExpirationDate is valide
         if (!empty($ForumTopicExpirationDate))
         {
             if (preg_match("[\d\d\d\d-\d\d-\d\d]", $ForumTopicExpirationDate) == 0)
             {
                 return 0;
             }
             else
             {
                 $ForumTopicExpirationDate = ", ForumTopicExpirationDate = \"$ForumTopicExpirationDate\"";
             }
         }

         if (empty($ForumTopicRank))
         {
             $ForumTopicRank = "";
         }
         else
         {
             $ForumTopicRank = ", ForumTopicRank = $ForumTopicRank";
         }

         // It's a new forum topic
         $id = getNewPrimaryKey($DbConnection, "ForumTopics", "ForumTopicID");
         if ($id != 0)
         {
             $DbResult = $DbConnection->query("INSERT INTO ForumTopics SET ForumTopicID = $id, ForumTopicTitle = \"$ForumTopicTitle\",
                                               ForumCategoryID = $ForumCategoryID, SupportMemberID = $SupportMemberID,
                                               ForumTopicStatus = $ForumTopicStatus, ForumTopicIcon = $ForumTopicIcon,
                                               ForumTopicNbViews = 0, ForumTopicNbAnswers = 0 $ForumTopicDate
                                               $ForumTopicExpirationDate $ForumTopicRank");

             if (!DB::isError($DbResult))
             {
                 return $id;
             }
         }
     }

     // ERROR
     return 0;
 }


/**
 * Update an existing forum topic in the ForumTopics table
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-11
 *
 * @param $DbConnection                  DB object    Object of the opened database connection
 * @param $ForumTopicID                  Integer      ID of the forum topic to update [1..n]
 * @param $ForumTopicTitle               String       Title of the forum topic
 * @param $ForumTopicDate                String       Creation date/time of the forum topic (yyyy-mm-dd hh:mm:ss)
 * @param $ForumCategoryID               Integer      ID of the category linked in which the topic is created [1..n]
 * @param $SupportMemberID               Integer      ID of the support member author of the topic [1..n]
 * @param $ForumTopicStatus              Integer      Status of the forum topic [0..n]
 * @param $ForumTopicIcon                Integer      Icon displayed for the topic [0..n]
 * @param $ForumTopicExpirationDate      String       Expiratoin date of the forum topic (yyyy-mm-dd)
 * @param $ForumTopicRank                Integer      Rank to display the topic if always on top [1..n]
 *
 * @return Integer                       The primary key of the forum category [1..n], 0 otherwise
 */
 function dbUpdateForumTopic($DbConnection, $ForumTopicID, $ForumTopicTitle, $ForumTopicDate, $ForumCategoryID, $SupportMemberID, $ForumTopicStatus = NULL, $ForumTopicIcon = NULL, $ForumTopicExpirationDate = NULL, $ForumTopicRank = NULL)
 {
     // The parameters which are NULL will be ignored for the update
     $ArrayParamsUpdate = array();

     // Verification of the parameters
     if (($ForumTopicID < 1) || (!isInteger($ForumTopicID)))
     {
         // ERROR
         return 0;
     }

     // Check if the ForumTopicTitle is valide
     if (!is_null($ForumTopicTitle))
     {
         if (empty($ForumTopicTitle))
         {
             return 0;
         }
         else
         {
             // The ForumTopicTitle field will be updated
             $ArrayParamsUpdate[] = "ForumTopicTitle = \"$ForumTopicTitle\"";
         }
     }

     if (!is_null($ForumCategoryID))
     {
         if (($ForumCategoryID < 1) || (!isInteger($ForumCategoryID)))
         {
             // ERROR
             return 0;
         }
         else
         {
             $ArrayParamsUpdate[] = "ForumCategoryID = $ForumCategoryID";
         }
     }

     if (!is_null($SupportMemberID))
     {
         if (($SupportMemberID < 1) || (!isInteger($SupportMemberID)))
         {
             // ERROR
             return 0;
         }
         else
         {
             $ArrayParamsUpdate[] = "SupportMemberID = $SupportMemberID";
         }
     }

     if (!is_null($ForumTopicStatus))
     {
         if (($ForumTopicStatus < 0) || (!isInteger($ForumTopicStatus)))
         {
             // ERROR
             return 0;
         }
         else
         {
             $ArrayParamsUpdate[] = "ForumTopicStatus = $ForumTopicStatus";
         }
     }

     if (!is_null($ForumTopicIcon))
     {
         if (($ForumTopicIcon < 0) || (!isInteger($ForumTopicIcon)))
         {
             // ERROR
             return 0;
         }
         else
         {
             $ArrayParamsUpdate[] = "ForumTopicIcon = $ForumTopicIcon";
         }
     }

     if (!is_null($ForumTopicExpirationDate))
     {
         if (empty($ForumTopicExpirationDate))
         {
             // The ForumTopicExpirationDate field will be updated
             $ArrayParamsUpdate[] = "ForumTopicExpirationDate = NULL";
         }
         else
         {
             if (preg_match("[\d\d\d\d-\d\d-\d\d]", $ForumTopicExpirationDate) == 0)
             {
                 return 0;
             }
             else
             {
                 // The ForumTopicExpirationDate field will be updated
                 $ArrayParamsUpdate[] = "ForumTopicExpirationDate = \"$ForumTopicExpirationDate\"";
             }
         }
     }

     if (!is_null($ForumTopicRank))
     {
         if (empty($ForumTopicRank))
         {
             // The ForumTopicRank field will be updated
             $ArrayParamsUpdate[] = "ForumTopicRank = NULL";
         }
         else
         {
             if ($ForumTopicRank < 1)
             {
                 return 0;
             }
             else
             {
                 // The ForumTopicRank field will be updated
                 $ArrayParamsUpdate[] = "ForumTopicRank = $ForumTopicRank";
             }
         }
     }

     // Here, the parameters are correct, we check if the forum topic exists
     if (isExistingForumTopic($DbConnection, $ForumTopicID))
     {
         // The forum topic exists : we can update if there is at least 1 parameter
         if (count($ArrayParamsUpdate) > 0)
         {
             $DbResult = $DbConnection->query("UPDATE ForumTopics SET ".implode(", ", $ArrayParamsUpdate)
                                              ." WHERE ForumTopicID = $ForumTopicID");
             if (!DB::isError($DbResult))
             {
                 // Forum topic updated
                 return $ForumTopicID;
             }
         }
         else
         {
             // The update isn't usefull
             return $ForumTopicID;
         }
     }

     // ERROR
     return 0;
 }


/**
 * Update the nb of wiews  of a forum topic, thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-15
 *
 * @param $DbConnection         DB object    Object of the opened database connection
 * @param $ForumTopicID         Integer      ID of the forum topic to update the nb of views [1..n]
 * @param $Value                Integer      Value to add or remove to the current nb of views
 *                                           of the forum topic
 *
 * @return Integer              The new nb of views of the forum topic, FALSE otherwise
 */
 function updateForumTopicNbViews($DbConnection, $ForumTopicID, $Value)
 {
     $DbResult = $DbConnection->query("SELECT ForumTopicNbViews FROM ForumTopics WHERE ForumTopicID = $ForumTopicID");
     if (!DB::isError($DbResult))
     {
         if ($DbResult->numRows() > 0)
         {
             // Get the current nb of views
             $Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC);

             // Compute the new nb of views
             $fNewValue = $Record['ForumTopicNbViews'] + $Value;

             // Set the new nb of views
             $DbResult = $DbConnection->query("UPDATE ForumTopics SET ForumTopicNbViews = $fNewValue
                                               WHERE ForumTopicID = $ForumTopicID");
             if (!DB::isError($DbResult))
             {
                 return $fNewValue;
             }
         }
     }

     // Error
     return FALSE;
 }


/**
 * Update the nb of answers  of a forum topic, thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-15
 *
 * @param $DbConnection         DB object    Object of the opened database connection
 * @param $ForumTopicID         Integer      ID of the forum topic to update the nb of answers [1..n]
 * @param $Value                Integer      Value to add or remove to the current nb of answers
 *                                           of the forum topic
 *
 * @return Integer              The new nb of answers of the forum topic, FALSE otherwise
 */
 function updateForumTopicNbAnswers($DbConnection, $ForumTopicID, $Value)
 {
     $DbResult = $DbConnection->query("SELECT ForumTopicNbAnswers FROM ForumTopics WHERE ForumTopicID = $ForumTopicID");
     if (!DB::isError($DbResult))
     {
         if ($DbResult->numRows() > 0)
         {
             // Get the current nb of answers
             $Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC);

             // Compute the new nb of answers
             $fNewValue = $Record['ForumTopicNbAnswers'] + $Value;

             // Set the new nb of answers
             $DbResult = $DbConnection->query("UPDATE ForumTopics SET ForumTopicNbAnswers = $fNewValue WHERE ForumTopicID = $ForumTopicID");
             if (!DB::isError($DbResult))
             {
                 return $fNewValue;
             }
         }
     }

     // Error
     return FALSE;
 }


/**
 * Delete a forum topic, thanks to its ID, and linked messages, subscribtions and last read messages ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-15
 *
 * @param $DbConnection              DB object    Object of the opened database connection
 * @param $ForumTopicID              Integer      ID of the forum topic to delete [1..n]
 *
 * @return Boolean                   TRUE if the forum topic is deleted with the messages, subscribtions and
 *                                   last read messages ID if it exists, FALSE otherwise
 */
 function dbDeleteForumTopic($DbConnection, $ForumTopicID)
 {
     // The parameters are correct?
     if ($ForumTopicID > 0)
     {
         // First, we delete the messages linked to the topic to delete
         $DbResult = $DbConnection->query("DELETE FROM ForumMessages WHERE ForumTopicID = $ForumTopicID");

         // Next, we delete subscribtions to the topic
         $DbResult = $DbConnection->query("DELETE FROM ForumTopicsSubscribtions WHERE ForumTopicID = $ForumTopicID");

         // The, we delete last read messages of supporters for this topic
         $DbResult = $DbConnection->query("DELETE FROM ForumTopicsLastReads WHERE ForumTopicID = $ForumTopicID");

         // The, we delete the forum topic
         $DbResult = $DbConnection->query("DELETE FROM ForumTopics WHERE ForumTopicID = $ForumTopicID");
         if (!DB::isError($DbResult))
         {
             // Topic and linked messages deleted
             return TRUE;
         }
     }

     // ERROR
     return FALSE;
 }


/**
 * Get recipients of a forum topic
 *
 * @author Christophe Javouhey
 * @version 1.1
 *     - 2021-11-29 : v1.1. For desactivated families, we send e-mails only to allowed addresses
 *
 * @since 2021-04-23
 *
 * @param $DbConnection             DB object              Object of the opened database connection
 * @param $ForumTopicID             Integer                ID of the concerned forum topic [1..n]
 * @param $ArrayParams              Mixed array            Contains the criterion used to filter the recipients
 *
 * @return Array of String          List of the filtered recipients of the topic, an empty array otherwise
 */
 function dbGetForumTopicRecipients($DbConnection, $ForumTopicID, $ArrayParams = array())
 {
     // First, we get SupportMemberStateID allowed to read/write messages or create topics in the category of the topic
     if (isExistingForumTopic($DbConnection, $ForumTopicID))
     {
         $ForumCategoryID = getForumTopicCategoryID($DbConnection, $ForumTopicID);

         $SupportMemberStateID = array();
         if (count($ArrayParams) >= 0)
         {
             // <<< SupportMemberStateID field >>>
             if ((array_key_exists("SupportMemberStateID", $ArrayParams)) && (!empty($ArrayParams["SupportMemberStateID"])))
             {
                 if (is_array($ArrayParams["SupportMemberStateID"]))
                 {
                     $SupportMemberStateID = $ArrayParams["SupportMemberStateID"];
                 }
                 else
                 {
                     $SupportMemberStateID = array($ArrayParams["SupportMemberStateID"]);
                 }
             }
         }

         $ArrayCategoryAccess = getForumCategoryAccess($DbConnection, array($ForumCategoryID),
                                                       array(FORUM_ACCESS_CREATE_TOPIC, FORUM_ACCESS_WRITE_MSG, FORUM_ACCESS_READ_MSG),
                                                       $SupportMemberStateID, 'ForumCategoryID, SupportMemberStateID');

         if ((isset($ArrayCategoryAccess['ForumCategoryAccessID'])) && (!empty($ArrayCategoryAccess['ForumCategoryAccessID'])))
         {
             // We get e-mails of all activated supporters
             $DbResult = $DbConnection->query("SELECT sm.SupportMemberID, sm.SupportMemberEmail, f.FamilyMainEmail, f.FamilySecondEmail, f.FamilyDesactivationDate,
                                               f.FamilyMainEmailContactAllowed, f.FamilySecondEmailContactAllowed
                                               FROM SupportMembers sm LEFT JOIN Families f ON (sm.FamilyID = f.FamilyID)
                                               WHERE sm.SupportMemberActivated > 0
                                               AND sm.SupportMemberStateID IN ".constructSQLINString($ArrayCategoryAccess['SupportMemberStateID']));

             if (!DB::isError($DbResult))
             {
                 if ($DbResult->numRows() != 0)
                 {
                     // Creation of the result array
                     $ArrayRecords = array(
                                           "SupportMemberID" => array(),
                                           "Email" => array()
                                          );

                     while($Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC))
                     {
                         // We check if the family isn't desactivated (if support member is associated to a family)
                         $bKeepSupportMemberEmail = TRUE;
                         $bKeepMainEmail = TRUE;
                         $bKeepSecondEmail = TRUE;
                         $ArrayEmails = array();

                         if (!empty($Record['FamilyDesactivationDate']))
                         {
                             // The family is desactivated : we check if FamilyMainEmailContactAllowed or FamilySecondEmailContactAllowed are checked
                             if (empty($Record['FamilyMainEmailContactAllowed']))
                             {
                                 $bKeepMainEmail = FALSE;
                             }

                             if (empty($Record['FamilySecondEmailContactAllowed']))
                             {
                                 $bKeepSecondEmail = FALSE;
                             }

                             // We try to detect if the e-mail address of the supporter is allowed to be notified
                             if ($Record['SupportMemberEmail'] == $Record['FamilyMainEmail'])
                             {
                                 $bKeepSupportMemberEmail = $bKeepMainEmail;
                             }
                             elseif ($Record['SupportMemberEmail'] == $Record['FamilySecondEmail'])
                             {
                                 $bKeepSupportMemberEmail = $bKeepSecondEmail;
                             }
                         }

                         if (($bKeepSupportMemberEmail) && (!empty($Record['SupportMemberEmail'])))
                         {
                             $ArrayEmails[] = $Record['SupportMemberEmail'];
                         }

                         if (($bKeepMainEmail) && (!empty($Record['FamilyMainEmail'])) && (!in_array($Record['FamilyMainEmail'], $ArrayEmails)))
                         {
                             $ArrayEmails[] = $Record['FamilyMainEmail'];
                         }

                         if (($bKeepSecondEmail) && (!empty($Record['FamilySecondEmail'])) && (!in_array($Record['FamilySecondEmail'], $ArrayEmails)))
                         {
                             $ArrayEmails[] = $Record['FamilySecondEmail'];
                         }

                         if (($bKeepSupportMemberEmail) || ($bKeepMainEmail) || ($bKeepSecondEmail))
                         {
                             // At least one e-mail address is allowed to be contacted
                             $ArrayRecords['SupportMemberID'][] = $Record['SupportMemberID'];
                             $ArrayRecords['Email'][] = $ArrayEmails;
                         }
                     }

                     return $ArrayRecords;
                 }
             }
         }
     }

     return array();
 }


/**
 * Get forum topics filtered by some criterion
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-11
 *
 * @param $DbConnection             DB object              Object of the opened database connection
 * @param $ArrayParams              Mixed array            Contains the criterion used to filter the forum topics
 * @param $OrderBy                  String                 Criteria used to sort the forum topics. If < 0, DESC is used,
 *                                                         otherwise ASC is used
 * @param $Page                     Integer                Number of the page to return [1..n]
 * @param $RecordsPerPage           Integer                Number of forum topics per page to return [1..n]
 *
 * @return Array of String          List of forum topics filtered, an empty array otherwise
 */
 function dbSearchForumTopic($DbConnection, $ArrayParams, $OrderBy = "", $Page = 1, $RecordsPerPage = 10)
 {
     // SQL request to find forum topics
     // First, we sort topics having ranks by their rank (ASC)
     // Then, we sort other topics (without ranks) by last posted message (DESC)
     $Select = "SELECT rkt.ForumTopicID, rkt.ForumTopicTitle, rkt.ForumTopicDate, rkt.ForumTopicExpirationDate, rkt.ForumTopicStatus,
                rkt.ForumTopicIcon, rkt.ForumTopicRank, rkt.ForumTopicNbViews, rkt.ForumTopicNbAnswers, rkt.ForumCategoryID, rkt.ForumCategoryName,
                rkt.ForumMessageID, rkt.LastTopicForumMessageDate,
                rkt.SupportMemberID, rkt.SupportMemberLastname, rkt.SupportMemberFirstname, rkt.SupportMemberStateID, rkt.SupportMemberStateName,
                @rownum := @rownum + 1 AS TopicPos";

     $From = "FROM
                  (SELECT ft.ForumTopicID, ft.ForumTopicTitle, ft.ForumTopicDate, ft.ForumTopicExpirationDate, ft.ForumTopicStatus,
                   ft.ForumTopicIcon, ft.ForumTopicRank, ft.ForumTopicNbViews, ft.ForumTopicNbAnswers, fc.ForumCategoryID, fc.ForumCategoryName,
                   fm.ForumMessageID, fm.ForumMessageDate AS LastTopicForumMessageDate,
                   sm.SupportMemberID, sm.SupportMemberLastname, sm.SupportMemberFirstname, sms.SupportMemberStateID, sms.SupportMemberStateName
                   FROM ForumTopics ft, ForumCategories fc, ForumMessages fm, SupportMembers sm, SupportMembersStates sms, (select @rownum := 0) AS r,
                       (SELECT tfm.ForumTopicID, MAX(tfm.ForumMessageID) AS LastTopicMessageID
                        FROM ForumMessages tfm GROUP BY tfm.ForumTopicID) AS LastMsgs";

     $Where = "WHERE fc.ForumCategoryID = ft.ForumCategoryID AND ft.SupportMemberID = sm.SupportMemberID AND ft.ForumTopicID = fm.ForumTopicID
                     AND ft.ForumTopicID = LastMsgs.ForumTopicID AND LastMsgs.LastTopicMessageID = fm.ForumMessageID
                     AND sm.SupportMemberStateID = sms.SupportMemberStateID";
     $Having = "";

     if (count($ArrayParams) >= 0)
     {
         // <<< ForumCategoryID field >>>
         if ((array_key_exists("ForumCategoryID", $ArrayParams)) && (!empty($ArrayParams["ForumCategoryID"])))
         {
             if (is_array($ArrayParams["ForumCategoryID"]))
             {
                 $Where .= " AND ft.ForumCategoryID IN ".constructSQLINString($ArrayParams["ForumCategoryID"]);
             }
             else
             {
                 $Where .= " AND ft.ForumCategoryID = ".$ArrayParams["ForumCategoryID"];
             }
         }

         // <<< ForumTopicTitle field >>>
         if ((array_key_exists("ForumTopicTitle", $ArrayParams)) && (!empty($ArrayParams["ForumTopicTitle"])))
         {
             $Where .= " AND ft.ForumTopicTitle LIKE \"".$ArrayParams["ForumTopicTitle"]."\"";
         }

         // <<< ForumTopicStatus field >>>
         if ((array_key_exists("ForumTopicStatus", $ArrayParams)) && (!empty($ArrayParams["ForumTopicStatus"])))
         {
             if (is_array($ArrayParams["ForumTopicStatus"]))
             {
                 $Where .= " AND ft.ForumTopicStatus IN ".constructSQLINString($ArrayParams["ForumTopicStatus"]);
             }
             else
             {
                 $Where .= " AND ft.ForumTopicStatus = ".$ArrayParams["ForumTopicStatus"];
             }
         }

         // <<< Forum topics between 2 given creation dates >>>
         if ((array_key_exists("StartDate", $ArrayParams)) && (count($ArrayParams["StartDate"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $Where .= " AND DATE_FORMAT(ft.ForumTopicDate, '%Y-%m-%d') ".$ArrayParams["StartDate"][0]." \"".$ArrayParams["StartDate"][1]."\"";
         }

         if ((array_key_exists("EndDate", $ArrayParams)) && (count($ArrayParams["EndDate"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $Where .= " AND DATE_FORMAT(ft.ForumTopicDate, '%Y-%m-%d') ".$ArrayParams["EndDate"][0]." \"".$ArrayParams["EndDate"][1]."\"";
         }

         // <<< Forum topics between 2 given expiration dates >>>
         if ((array_key_exists("ExpirationStartDate", $ArrayParams)) && (count($ArrayParams["ExpirationStartDate"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $Where .= " AND ft.ForumTopicExpirationDate ".$ArrayParams["ExpirationStartDate"][0]." \"".$ArrayParams["ExpirationStartDate"][1]."\"";
         }

         if ((array_key_exists("ExpirationEndDate", $ArrayParams)) && (count($ArrayParams["ExpirationEndDate"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $Where .= " AND ft.ForumTopicExpirationDate ".$ArrayParams["ExpirationEndDate"][0]." \"".$ArrayParams["ExpirationEndDate"][1]."\"";
         }

         // <<< Option : activated forum topics (not expirated) >>>
         if (array_key_exists("Activated", $ArrayParams))
         {
             $Where .= " AND (ft.ForumTopicExpirationDate IS NULL OR ft.ForumTopicExpirationDate >= \"".date('Y-m-d')."\")";
         }

         // <<< SupportMemberID field >>>
         if ((array_key_exists("SupportMemberID", $ArrayParams)) && (!empty($ArrayParams["SupportMemberID"])))
         {
             if (is_array($ArrayParams["SupportMemberID"]))
             {
                 $Where .= " AND ft.SupportMemberID IN ".constructSQLINString($ArrayParams["SupportMemberID"]);
             }
             else
             {
                 $Where .= " AND ft.SupportMemberID = ".$ArrayParams["SupportMemberID"];
             }
         }
     }

     // We take into account the page and the number of forum topics per page
     if ($Page < 1)
     {
         $Page = 1;
     }

     if ($RecordsPerPage < 0)
     {
         $RecordsPerPage = 10;
     }

     $Limit = '';
     if ($RecordsPerPage > 0)
     {
         $StartIndex = ($Page - 1) * $RecordsPerPage;
         $Limit = "LIMIT $StartIndex, $RecordsPerPage";
     }

     // We take into account the order by
     if ($OrderBy == "")
     {
         $StrOrderBy = "";
     }
     else
     {
         $StrOrderBy = " ORDER BY $OrderBy";
     }

     // We can launch the SQL request
     $DbResult = $DbConnection->query("$Select $From $Where AND ft.ForumTopicRank IS NOT NULL
                                       ORDER BY ft.ForumTopicRank) AS rkt
                                       UNION $Select $From $Where AND ft.ForumTopicRank IS NULL
                                       ORDER BY LastTopicForumMessageDate DESC) AS rkt
                                       GROUP BY rkt.ForumTopicID $Having $StrOrderBy $Limit");

     if (!DB::isError($DbResult))
     {
         if ($DbResult->numRows() != 0)
         {
             // Creation of the result array
             $ArrayRecords = array(
                                   "ForumTopicID" => array(),
                                   "ForumTopicTitle" => array(),
                                   "ForumTopicDate" => array(),
                                   "ForumTopicExpirationDate" => array(),
                                   "ForumTopicStatus" => array(),
                                   "ForumTopicIcon" => array(),
                                   "ForumTopicRank" => array(),
                                   "ForumTopicNbViews" => array(),
                                   "ForumTopicNbAnswers" => array(),
                                   "ForumCategoryID" => array(),
                                   "ForumCategoryName" => array(),
                                   "SupportMemberID" => array(),
                                   "SupportMemberLastname" => array(),
                                   "SupportMemberFirstname" => array(),
                                   "SupportMemberStateID" => array(),
                                   "ForumMessageID" => array(),
                                   "LastTopicForumMessageDate" => array()
                                  );

             while($Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC))
             {
                 $ArrayRecords["ForumTopicID"][] = $Record["ForumTopicID"];
                 $ArrayRecords["ForumTopicTitle"][] = $Record["ForumTopicTitle"];
                 $ArrayRecords["ForumTopicDate"][] = $Record["ForumTopicDate"];
                 $ArrayRecords["ForumTopicExpirationDate"][] = $Record["ForumTopicExpirationDate"];
                 $ArrayRecords["ForumTopicStatus"][] = $Record["ForumTopicStatus"];
                 $ArrayRecords["ForumTopicIcon"][] = $Record["ForumTopicIcon"];
                 $ArrayRecords["ForumTopicRank"][] = $Record["ForumTopicRank"];
                 $ArrayRecords["ForumTopicNbViews"][] = $Record["ForumTopicNbViews"];
                 $ArrayRecords["ForumTopicNbAnswers"][] = $Record["ForumTopicNbAnswers"];
                 $ArrayRecords["ForumCategoryID"][] = $Record["ForumCategoryID"];
                 $ArrayRecords["ForumCategoryName"][] = $Record["ForumCategoryName"];
                 $ArrayRecords["SupportMemberID"][] = $Record["SupportMemberID"];
                 $ArrayRecords["SupportMemberLastname"][] = $Record["SupportMemberLastname"];
                 $ArrayRecords["SupportMemberFirstname"][] = $Record["SupportMemberFirstname"];
                 $ArrayRecords["SupportMemberStateID"][] = $Record["SupportMemberStateID"];
                 $ArrayRecords["SupportMemberStateName"][] = $Record["SupportMemberStateName"];
                 $ArrayRecords["ForumMessageID"][] = $Record["ForumMessageID"];
                 $ArrayRecords["LastTopicForumMessageDate"][] = $Record["LastTopicForumMessageDate"];
             }

             // Return result
             return $ArrayRecords;
         }
     }

     // ERROR
     return array();
 }


/**
 * Get the number of forum topics filtered by some criterion
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-11
 *
 * @param $DbConnection         DB object              Object of the opened database connection
 * @param $ArrayParams          Mixed array            Contains the criterion used to filter the forum topics
 *
 * @return Integer              Number of the forum topics found, 0 otherwise
 */
 function getNbdbSearchForumTopic($DbConnection, $ArrayParams)
 {
     // SQL request to find forum topics
     $Select = "SELECT ft.ForumTopicID";
     $From = "FROM ForumTopics ft";
     $Where = "WHERE 1=1";
     $Having = "";

     if (count($ArrayParams) >= 0)
     {
         // <<< ForumCategoryID field >>>
         if ((array_key_exists("ForumCategoryID", $ArrayParams)) && (!empty($ArrayParams["ForumCategoryID"])))
         {
             if (is_array($ArrayParams["ForumCategoryID"]))
             {
                 $Where .= " AND ft.ForumCategoryID IN ".constructSQLINString($ArrayParams["ForumCategoryID"]);
             }
             else
             {
                 $Where .= " AND ft.ForumCategoryID = ".$ArrayParams["ForumCategoryID"];
             }
         }

         // <<< ForumTopicTitle field >>>
         if ((array_key_exists("ForumTopicTitle", $ArrayParams)) && (!empty($ArrayParams["ForumTopicTitle"])))
         {
             $Where .= " AND ft.ForumTopicTitle LIKE \"".$ArrayParams["ForumTopicTitle"]."\"";
         }

         // <<< ForumTopicStatus field >>>
         if ((array_key_exists("ForumTopicStatus", $ArrayParams)) && (!empty($ArrayParams["ForumTopicStatus"])))
         {
             if (is_array($ArrayParams["ForumTopicStatus"]))
             {
                 $Where .= " AND ft.ForumTopicStatus IN ".constructSQLINString($ArrayParams["ForumTopicStatus"]);
             }
             else
             {
                 $Where .= " AND ft.ForumTopicStatus = ".$ArrayParams["ForumTopicStatus"];
             }
         }

         // <<< Forum topics between 2 given creation dates >>>
         if ((array_key_exists("StartDate", $ArrayParams)) && (count($ArrayParams["StartDate"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $Where .= " AND DATE_FORMAT(ft.ForumTopicDate, '%Y-%m-%d') ".$ArrayParams["StartDate"][0]." \"".$ArrayParams["StartDate"][1]."\"";
         }

         if ((array_key_exists("EndDate", $ArrayParams)) && (count($ArrayParams["EndDate"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $Where .= " AND DATE_FORMAT(ft.ForumTopicDate, '%Y-%m-%d') ".$ArrayParams["EndDate"][0]." \"".$ArrayParams["EndDate"][1]."\"";
         }

         // <<< Forum topics between 2 given expiration dates >>>
         if ((array_key_exists("ExpirationStartDate", $ArrayParams)) && (count($ArrayParams["ExpirationStartDate"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $Where .= " AND ft.ForumTopicExpirationDate ".$ArrayParams["ExpirationStartDate"][0]." \"".$ArrayParams["ExpirationStartDate"][1]."\"";
         }

         if ((array_key_exists("ExpirationEndDate", $ArrayParams)) && (count($ArrayParams["ExpirationEndDate"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $Where .= " AND ft.ForumTopicExpirationDate ".$ArrayParams["ExpirationEndDate"][0]." \"".$ArrayParams["ExpirationEndDate"][1]."\"";
         }

         // <<< Option : activated forum topics (not expirated) >>>
         if (array_key_exists("Activated", $ArrayParams))
         {
             $Where .= " AND (ft.ForumTopicExpirationDate IS NULL OR ft.ForumTopicExpirationDate >= \"".date('Y-m-d')."\")";
         }

         // <<< SupportMemberID field >>>
         if ((array_key_exists("SupportMemberID", $ArrayParams)) && (!empty($ArrayParams["SupportMemberID"])))
         {
             if (is_array($ArrayParams["SupportMemberID"]))
             {
                 $Where .= " AND ft.SupportMemberID IN ".constructSQLINString($ArrayParams["SupportMemberID"]);
             }
             else
             {
                 $Where .= " AND ft.SupportMemberID = ".$ArrayParams["SupportMemberID"];
             }
         }
     }

     // We can launch the SQL request
     $DbResult = $DbConnection->query("$Select $From $Where GROUP BY ft.ForumTopicID $Having");
     if (!DB::isError($DbResult))
     {
         return $DbResult->numRows();
     }

     // ERROR
     return 0;
 }


/**
 * Check if a last read entry exists in the ForumTopicsLastReads table, thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-18
 *
 * @param $DbConnection            DB object    Object of the opened database connection
 * @param $ForumTopicLastReadID    Integer      ID of the last read entry searched [1..n]
 *
 * @return Boolean                 TRUE if the last read entry exists, FALSE otherwise
 */
 function isExistingForumTopicLastRead($DbConnection, $ForumTopicLastReadID)
 {
     if ($ForumTopicLastReadID > 0)
     {
         $DbResult = $DbConnection->query("SELECT ForumTopicLastReadID FROM ForumTopicsLastReads
                                           WHERE ForumTopicLastReadID = $ForumTopicLastReadID");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 1)
             {
                 // The last read entry exists
                 return TRUE;
             }
         }
     }

     // The last read entry doesn't exist
     return FALSE;
 }


/**
 * Add a forum topic last read flag for a supporter and a topic in the ForumTopicsLastReads table
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-18
 *
 * @param $DbConnection                   DB object    Object of the opened database connection
 * @param $ForumTopicLastReadMessageID    Integer      ID of the last read message of a topic for a supporter [1..n]
 * @param $ForumTopicID                   Integer      ID of the concerned topic [1..n]
 * @param $SupportMemberID                Integer      ID of the concerned supporter [1..n]
 *
 * @return Integer                        The primary key of the entry in the ForumTopicsLastReads table [1..n],
 *                                        0 otherwise
 */
 function dbAddForumTopicLastRead($DbConnection, $ForumTopicLastReadMessageID, $ForumTopicID, $SupportMemberID)
 {
     if (($ForumTopicLastReadMessageID > 0) && ($ForumTopicID > 0) && ($SupportMemberID > 0))
     {
         // Check if the forum entry is unique for a topic and a supporter
         $DbResult = $DbConnection->query("SELECT ForumTopicLastReadID FROM ForumTopicsLastReads
                                           WHERE ForumTopicID = $ForumTopicID AND SupportMemberID = $SupportMemberID");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 0)
             {
                 // It's a new entry
                 $id = getNewPrimaryKey($DbConnection, "ForumTopicsLastReads", "ForumTopicLastReadID");
                 if ($id != 0)
                 {
                     $DbResult = $DbConnection->query("INSERT INTO ForumTopicsLastReads SET ForumTopicLastReadID = $id,
                                                       ForumTopicID = \"$ForumTopicID\", SupportMemberID = $SupportMemberID,
                                                       ForumTopicLastReadMessageID = $ForumTopicLastReadMessageID");

                     if (!DB::isError($DbResult))
                     {
                         // We update th nb of views of the topic if the supporter isn't the author of the topic
                         if ($SupportMemberID != getForumTopicAuthorID($DbConnection, $ForumTopicID))
                         {
                             updateForumTopicNbViews($DbConnection, $ForumTopicID, 1);
                         }

                         return $id;
                     }
                 }
             }
             else
             {
                 // The entry already exists
                 $Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC);
                 return $Record['ForumTopicLastReadID'];
             }
         }
     }

     // ERROR
     return 0;
 }


/**
 * Update an existing forum topic last read flag in the ForumTopicsLastReads table
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-18
 *
 * @param $DbConnection                   DB object    Object of the opened database connection
 * @param $ForumTopicLastReadID           Integer      ID of the last read entry to update [1..n]
 * @param $ForumTopicLastReadMessageID    Integer      ID of the last read message of a topic for a supporter [1..n]
 * @param $ForumTopicID                   Integer      ID of the concerned topic [1..n]
 * @param $SupportMemberID                Integer      ID of the concerned supporter [1..n]
 *
 * @return Integer                        The primary key of the entry in the ForumTopicsLastReads table [1..n],
 *                                        0 otherwise
 */
 function dbUpdateForumTopicLastRead($DbConnection, $ForumTopicLastReadID, $ForumTopicLastReadMessageID, $ForumTopicID, $SupportMemberID)
 {
     // The parameters which are NULL will be ignored for the update
     $ArrayParamsUpdate = array();

     // Verification of the parameters
     if (($ForumTopicLastReadID < 1) || (!isInteger($ForumTopicLastReadID)))
     {
         // ERROR
         return 0;
     }

     if (!is_null($ForumTopicLastReadMessageID))
     {
         if (($ForumTopicLastReadMessageID < 1) || (!isInteger($ForumTopicLastReadMessageID)))
         {
             // ERROR
             return 0;
         }
         else
         {
             $ArrayParamsUpdate[] = "ForumTopicLastReadMessageID = $ForumTopicLastReadMessageID";
         }
     }

     if (!is_null($ForumTopicID))
     {
         if (($ForumTopicID < 1) || (!isInteger($ForumTopicID)))
         {
             // ERROR
             return 0;
         }
         else
         {
             $ArrayParamsUpdate[] = "ForumTopicID = $ForumTopicID";
         }
     }

     if (!is_null($SupportMemberID))
     {
         if (($SupportMemberID < 1) || (!isInteger($SupportMemberID)))
         {
             // ERROR
             return 0;
         }
         else
         {
             $ArrayParamsUpdate[] = "SupportMemberID = $SupportMemberID";
         }
     }

     // Here, the parameters are correct, we check if the forum last read entry exists
     if (isExistingForumTopicLastRead($DbConnection, $ForumTopicLastReadID))
     {
         // We check if the entry for a topic and a supporter is unique
         $DbResult = $DbConnection->query("SELECT ForumTopicLastReadID FROM ForumTopicsLastReads
                                           WHERE ForumTopicID = $ForumTopicID AND SupportMemberID = $SupportMemberID
                                           AND ForumTopicLastReadID <> $ForumTopicLastReadID");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 0)
             {
                 // The entry exists and is unique : we can update if there is at least 1 parameter
                 if (count($ArrayParamsUpdate) > 0)
                 {
                     $DbResult = $DbConnection->query("UPDATE ForumTopicsLastReads SET ".implode(", ", $ArrayParamsUpdate)
                                                      ." WHERE ForumTopicLastReadID = $ForumTopicLastReadID");
                     if (!DB::isError($DbResult))
                     {
                         // Last read entry updated
                         updateForumTopicNbViews($DbConnection, $ForumTopicID, 1);
                         return $ForumTopicLastReadID;
                     }
                 }
                 else
                 {
                     // The update isn't usefull
                     return $ForumTopicLastReadID;
                 }
             }
         }
     }

     // ERROR
     return 0;
 }


/**
 * Get forum topics read flags by some criterion
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-18
 *
 * @param $DbConnection             DB object              Object of the opened database connection
 * @param $ArrayParams              Mixed array            Contains the criterion used to get read flags
 *                                                         of the forum topics
 *
 * @return Array of String          List of read flags of forum topics, an empty array otherwise
 */
 function getForumTopicsLastReads($DbConnection, $ArrayParams)
 {
     $Select = "SELECT ft.ForumTopicID, ft.ForumCategoryID, ftlr.ForumTopicLastReadID, ftlr.ForumTopicLastReadMessageID,
                ftlr.SupportMemberID, IF(ftlr.ForumTopicLastReadMessageID < LastMsgs.ForumTopicLastMessageID, 0, 1) AS IsRead";
     $From = "FROM ForumTopics ft, ForumTopicsLastReads ftlr,
                   (SELECT tfm.ForumTopicID, MAX(tfm.ForumMessageID) AS ForumTopicLastMessageID
                    FROM ForumMessages tfm
                    GROUP BY tfm.ForumTopicID) AS LastMsgs";
     $Where = "WHERE ft.ForumTopicID = ftlr.ForumTopicID AND LastMsgs.ForumTopicID = ft.ForumTopicID";
     $Having = "";

     if (count($ArrayParams) >= 0)
     {
         // <<< ForumCategoryID field >>>
         if ((array_key_exists("ForumCategoryID", $ArrayParams)) && (!empty($ArrayParams["ForumCategoryID"])))
         {
             if (is_array($ArrayParams["ForumCategoryID"]))
             {
                 $Where .= " AND ft.ForumCategoryID IN ".constructSQLINString($ArrayParams["ForumCategoryID"]);
             }
             else
             {
                 $Where .= " AND ft.ForumCategoryID = ".$ArrayParams["ForumCategoryID"];
             }
         }

         // <<< ForumTopicID field >>>
         if ((array_key_exists("ForumTopicID", $ArrayParams)) && (!empty($ArrayParams["ForumTopicID"])))
         {
             if (is_array($ArrayParams["ForumTopicID"]))
             {
                 $Where .= " AND ft.ForumTopicID IN ".constructSQLINString($ArrayParams["ForumTopicID"]);
             }
             else
             {
                 $Where .= " AND ft.ForumTopicID = ".$ArrayParams["ForumTopicID"];
             }
         }

         // <<< SupportMemberID field >>>
         if ((array_key_exists("SupportMemberID", $ArrayParams)) && (!empty($ArrayParams["SupportMemberID"])))
         {
             if (is_array($ArrayParams["SupportMemberID"]))
             {
                 $Where .= " AND ftlr.SupportMemberID IN ".constructSQLINString($ArrayParams["SupportMemberID"]);
             }
             else
             {
                 $Where .= " AND ftlr.SupportMemberID = ".$ArrayParams["SupportMemberID"];
             }
         }
     }

     // We can launch the SQL request
     $DbResult = $DbConnection->query("$Select $From $Where $Having");

     if (!DB::isError($DbResult))
     {
         if ($DbResult->numRows() != 0)
         {
             // Creation of the result array
             $ArrayRecords = array();

             while($Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC))
             {
                 if (!isset($ArrayRecords[$Record["ForumTopicID"]]))
                 {
                     $ArrayRecords[$Record["ForumTopicID"]] = array(
                                                                    "ForumTopicLastReadID" => array(),
                                                                    "ForumCategoryID" => array(),
                                                                    "ForumTopicLastReadMessageID" => array(),
                                                                    "SupportMemberID" => array(),
                                                                    "IsRead" => array()
                                                                   );
                 }

                 $ArrayRecords[$Record["ForumTopicID"]]['ForumTopicLastReadID'][] = $Record["ForumTopicLastReadID"];
                 $ArrayRecords[$Record["ForumTopicID"]]['ForumCategoryID'][] = $Record["ForumCategoryID"];
                 $ArrayRecords[$Record["ForumTopicID"]]['ForumTopicLastReadMessageID'][] = $Record["ForumTopicLastReadMessageID"];
                 $ArrayRecords[$Record["ForumTopicID"]]['SupportMemberID'][] = $Record["SupportMemberID"];
                 $ArrayRecords[$Record["ForumTopicID"]]['IsRead'][] = $Record["IsRead"];
             }

             // Return result
             return $ArrayRecords;
         }
     }

     // ERROR
     return array();
 }


/**
 * Check if a forum message exists in the ForumMessages table, thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-15
 *
 * @param $DbConnection         DB object    Object of the opened database connection
 * @param $ForumMessageID       Integer      ID of the forum message searched [1..n]
 *
 * @return Boolean              TRUE if the forum message exists, FALSE otherwise
 */
 function isExistingForumMessage($DbConnection, $ForumMessageID)
 {
     if ($ForumMessageID > 0)
     {
         $DbResult = $DbConnection->query("SELECT ForumMessageID FROM ForumMessages
                                           WHERE ForumMessageID = $ForumMessageID");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 1)
             {
                 // The forum message exists
                 return TRUE;
             }
         }
     }

     // The forum message doesn't exist
     return FALSE;
 }


/**
 * Give the ID of a forum topic of a given forum message, thank to ist ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-18
 *
 * @param $DbConnection         DB object    Object of the opened database connection
 * @param $ForumMessageID       Integer      ID of the forum message for which we want its topic ID [1..n]
 *
 * @return Integer              ID of the forum topic of the given forum message, 0 otherwise
 */
 function getForumMessageTopicID($DbConnection, $ForumMessageID)
 {
     if ($ForumMessageID > 0)
     {
         $DbResult = $DbConnection->query("SELECT ForumTopicID FROM ForumMessages
                                           WHERE ForumMessageID = $ForumMessageID");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() != 0)
             {
                 $Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC);
                 return $Record["ForumTopicID"];
             }
         }
     }

     // ERROR
     return 0;
 }


/**
 * Check if a forum message is the first message of a topic, thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-17
 *
 * @param $DbConnection         DB object    Object of the opened database connection
 * @param $ForumMessageID       Integer      ID of the forum message searched [1..n]
 *
 * @return Boolean              TRUE if the forum message is the first of the topic, FALSE otherwise
 */
 function isFirstForumTopicMessage($DbConnection, $ForumMessageID)
 {
     if ($ForumMessageID > 0)
     {
         $DbResult = $DbConnection->query("SELECT MIN(fm.ForumMessageID) AS FirstForumTopicMessageID
                                           FROM ForumMessages fm,
                                                (SELECT tfm.ForumTopicID FROM ForumMessages tfm
                                                 WHERE tfm.ForumMessageID = $ForumMessageID) AS tmp
                                           WHERE tmp.ForumTopicID = fm.ForumTopicID
                                           GROUP BY fm.ForumTopicID");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 1)
             {
                 $Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC);
                 if ($Record['FirstForumTopicMessageID'] == $ForumMessageID)
                 {
                     return TRUE;
                 }
             }
         }
     }

     return FALSE;
 }


/**
 * Get the position of a forum message is the topic, thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-18
 *
 * @param $DbConnection         DB object    Object of the opened database connection
 * @param $ForumMessageID       Integer      ID of the forum message searched [1..n]
 *
 * @return Integer              Position of the forum message is the topic, FALSE otherwise
 */
 function getForumTopicMessagePosInTopic($DbConnection, $ForumMessageID)
 {
     if ($ForumMessageID > 0)
     {
         $DbResult = $DbConnection->query("SELECT rtm.ForumMessageID, rtm.TopicPos
                                           FROM
                                               (SELECT fm.ForumMessageID, @rownum := @rownum + 1 AS TopicPos
                                                FROM ForumMessages fm, (select @rownum := 0) AS r,
                                                    (SELECT tfm.ForumTopicID FROM ForumMessages tfm
                                                     WHERE tfm.ForumMessageID = $ForumMessageID) AS tmp
                                                WHERE tmp.ForumTopicID = fm.ForumTopicID
                                                ORDER BY fm.ForumMessageID) AS rtm
                                           WHERE rtm.ForumMessageID = $ForumMessageID");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 1)
             {
                 $Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC);
                 return $Record['TopicPos'];
             }
         }
     }

     return FALSE;
 }


/**
 * Get the position of a forum message is the topic, thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-18
 *
 * @param $DbConnection         DB object    Object of the opened database connection
 * @param $ForumMessageID       Integer      ID of the forum message searched [1..n]
 *
 * @return Integer              Position of the forum message is the topic, FALSE otherwise
 */
 function getForumTopicMessageIDwithPosInTopic($DbConnection, $ForumTopicID, $Position)
 {
     if (($ForumTopicID > 0) && ($Position > 0))
     {
         $DbResult = $DbConnection->query("SELECT rtm.ForumMessageID, rtm.TopicPos
                                           FROM
                                               (SELECT fm.ForumMessageID, @rownum := @rownum + 1 AS TopicPos
                                                FROM ForumMessages fm, (select @rownum := 0) AS r
                                                WHERE fm.ForumTopicID = $ForumTopicID
                                                ORDER BY fm.ForumMessageID) AS rtm
                                           WHERE rtm.TopicPos = $Position");

         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 1)
             {
                 $Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC);
                 return $Record['ForumMessageID'];
             }
         }
     }

     return FALSE;
 }


/**
 * Get infos about the author of a given message, thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-18
 *
 * @param $DbConnection             DB object              Object of the opened database connection
 * @param $ForumMessageID           Integer                ID of the forum message searched [1..n]
 *
 * @return Array of String          Infos about the author of the given message, an empty array otherwise
 */
 function getForumMessageAuthorInfos($DbConnection, $ForumMessageID)
 {
     if ($ForumMessageID > 0)
     {
         // We can launch the SQL request
         $DbResult = $DbConnection->query("SELECT sm.SupportMemberID, sm.SupportMemberLastname, sm.SupportMemberFirstname,
                                           sms.SupportMemberStateID, sms.SupportMemberStateName
                                           FROM ForumMessages fm, SupportMembers sm, SupportMembersStates sms
                                           WHERE fm.ForumMessageID = $ForumMessageID AND fm.SupportMemberID = sm.SupportMemberID
                                           AND sm.SupportMemberStateID = sms.SupportMemberStateID");

         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 1)
             {
                 $Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC);
                 return $Record;
             }
         }
     }

     // ERROR
     return array();
 }


/**
 * Add a forum message to a topic in the ForumMessages table
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-15
 *
 * @param $DbConnection                  DB object    Object of the opened database connection
 * @param $ForumMessageDate              String       Creation date/time of the forum message (yyyy-mm-dd hh:mm:ss)
 * @param $ForumTopicID                  Integer      ID of the topic linked to the created message [1..n]
 * @param $SupportMemberID               Integer      ID of the support member author of the topic [1..n]
 * @param $ForumMessageContent           String       Content of the message
 * @param $ForumReplyToMessageID         Integer      ID of the message for which this message is an answer [1..n] or NULL
 * @param $ForumMessagePicture           String       Picture name linked to the message
 *
 * @return Integer                       The primary key of the forum message [1..n], 0 otherwise
 */
 function dbAddForumMessage($DbConnection, $ForumMessageDate, $ForumTopicID, $SupportMemberID, $ForumMessageContent, $ForumReplyToMessageID = NULL, $ForumMessagePicture = '')
 {
     if ((!empty($ForumMessageContent)) && (!empty($ForumMessageDate)) && ($ForumTopicID > 0) && ($SupportMemberID > 0))
     {
         // Check if the creation date of the message is valide
         if (preg_match("[\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d]", $ForumMessageDate) == 0)
         {
             return 0;
         }
         else
         {
             $ForumMessageDate = ", ForumMessageDate = \"$ForumMessageDate\"";
         }

         if (!empty($ForumReplyToMessageID))
         {
             if ($ForumReplyToMessageID > 0)
             {
                 $ForumReplyToMessageID = ", ForumReplyToMessageID = $ForumReplyToMessageID";
             }
             else
             {
                 return 0;
             }
         }

         if (!empty($ForumMessagePicture))
         {
             $ForumMessagePicture = ", ForumMessagePicture = \"$ForumMessagePicture\"";
         }

         // It's a new forum message
         $id = getNewPrimaryKey($DbConnection, "ForumMessages", "ForumMessageID");
         if ($id != 0)
         {
             $DbResult = $DbConnection->query("INSERT INTO ForumMessages SET ForumMessageID = $id, ForumMessageContent = \"$ForumMessageContent\",
                                               ForumTopicID = $ForumTopicID, SupportMemberID = $SupportMemberID, ForumMessageUpdateDate = NULL
                                               $ForumMessageDate $ForumReplyToMessageID $ForumMessagePicture");

             if (!DB::isError($DbResult))
             {
                 // Message added : we update the number of answers of the topic
                 updateForumTopicNbAnswers($DbConnection, $ForumTopicID, 1);

                 return $id;
             }
         }
     }

     // ERROR
     return 0;
 }


/**
 * Update an existing forum message in the ForumMessages table
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-15
 *
 * @param $DbConnection                  DB object    Object of the opened database connection
 * @param $ForumMessageID                Integer      ID of the forum message to update [1..n]
 * @param $ForumTopicID                  Integer      ID of the topic linked to the created message [1..n]
 * @param $SupportMemberID               Integer      ID of the support member author of the topic [1..n]
 * @param $ForumMessageContent           String       Content of the message
 * @param $ForumMessageUpdateDate        String       Update date/time of the message (yyyy-mm-dd hh:mm:ss)
 * @param $ForumMessagePicture           String       Picture name linked to the message
 *
 * @return Integer                       The primary key of the forum category [1..n], 0 otherwise
 */
 function dbUpdateForumMessage($DbConnection, $ForumMessageID, $ForumTopicID, $SupportMemberID, $ForumMessageContent = NULL, $ForumMessageUpdateDate = NULL, $ForumMessagePicture = NULL)
 {
     // The parameters which are NULL will be ignored for the update
     $ArrayParamsUpdate = array();

     // Verification of the parameters
     if (($ForumMessageID < 1) || (!isInteger($ForumMessageID)))
     {
         // ERROR
         return 0;
     }

     if (!is_null($ForumTopicID))
     {
         if (($ForumTopicID < 1) || (!isInteger($ForumTopicID)))
         {
             // ERROR
             return 0;
         }
         else
         {
             $ArrayParamsUpdate[] = "ForumTopicID = $ForumTopicID";
         }
     }

     if (!is_null($SupportMemberID))
     {
         if (($SupportMemberID < 1) || (!isInteger($SupportMemberID)))
         {
             // ERROR
             return 0;
         }
         else
         {
             $ArrayParamsUpdate[] = "SupportMemberID = $SupportMemberID";
         }
     }

     // Check if the ForumMessageContent is valide
     if (!is_null($ForumMessageContent))
     {
         if (empty($ForumMessageContent))
         {
             return 0;
         }
         else
         {
             // The ForumMessageContent field will be updated
             $ArrayParamsUpdate[] = "ForumMessageContent = \"$ForumMessageContent\"";
         }
     }

     if (!is_null($ForumMessagePicture))
     {
         // The ForumMessagePicture field will be updated
         $ArrayParamsUpdate[] = "ForumMessagePicture = \"$ForumMessagePicture\"";
     }

     if (!is_null($ForumMessageUpdateDate))
     {
         if (empty($ForumMessageUpdateDate))
         {
             // The ForumMessageUpdateDate field will be updated
             $ArrayParamsUpdate[] = "ForumMessageUpdateDate = NULL";
         }
         else
         {
             if (preg_match("[\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d]", $ForumMessageUpdateDate) == 0)
             {
                 return 0;
             }
             else
             {
                 // The ForumMessageUpdateDate field will be updated
                 $ArrayParamsUpdate[] = "ForumMessageUpdateDate = \"$ForumMessageUpdateDate\"";
             }
         }
     }

     // Here, the parameters are correct, we check if the forum message exists
     if (isExistingForumMessage($DbConnection, $ForumMessageID))
     {
         // The forum message exists : we can update if there is at least 1 parameter
         if (count($ArrayParamsUpdate) > 0)
         {
             $DbResult = $DbConnection->query("UPDATE ForumMessages SET ".implode(", ", $ArrayParamsUpdate)
                                              ." WHERE ForumMessageID = $ForumMessageID");
             if (!DB::isError($DbResult))
             {
                 // Forum message updated
                 return $ForumMessageID;
             }
         }
         else
         {
             // The update isn't usefull
             return $ForumMessageID;
         }
     }

     // ERROR
     return 0;
 }


/**
 * Give the uploaded files of a forum message, thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2017-10-05
 *
 * @param $DbConnection              DB object      Object of the opened database connection
 * @param $ForumMessageID            Integer        ID of the forum message for which we want the upoaded files [1..n]
 * @param $ArrayParams               Mixed array    Contains the criterion used to filter the uploaded files of the forum message
 * @param $OrderBy                   String         To order the uploaded files
 *
 * @return Mixed array               All infos about uploaded files of the family if it exists,
 *                                   an empty array otherwise
 */
 function getForumMessageUploadedFiles($DbConnection, $ForumMessageID, $ArrayParams = array(), $OrderBy = 'UploadedFileDate')
 {
     if ($ForumMessageID > 0)
     {
         if (empty($OrderBy))
         {
             $OrderBy = 'UploadedFileDate';
         }

         $Conditions = '';
         if (!empty($ArrayParams))
         {
             if ((isset($ArrayParams['UploadedFileDate'])) && (count($ArrayParams['UploadedFileDate']) == 2))
             {
                 // $ArrayParams['UploadedFileDate'][0] contains the operator (>, >=, =...) and
                 // $ArrayParams['UploadedFileDate'][1] contains the date in english format (YYYY-MM-DD or YYYY-MM-DD HH:MM:SS)
                 $Conditions .= " AND uf.UploadedFileDate ".$ArrayParams['UploadedFileDate'][0]." \"".$ArrayParams['UploadedFileDate'][1]."\"";
             }
         }

         // We get the uploaded files of the forum message
         $DbResult = $DbConnection->query("SELECT uf.UploadedFileID, uf.UploadedFileDate, uf.UploadedFileName,
                                           uf.UploadedFileDescription
                                           FROM UploadedFiles uf
                                           WHERE uf.ObjectID = $ForumMessageID AND UploadedFileObjectType = ".OBJ_FORUM_MESSAGE
                                           ."$Conditions ORDER BY $OrderBy");

         if (!DB::isError($DbResult))
         {
             // Creation of the result array
             $ArrayRecords = array(
                                  "UploadedFileID" => array(),
                                  "UploadedFileDate" => array(),
                                  "UploadedFileName" => array(),
                                  "UploadedFileDescription" => array()
                                 );

             while($Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC))
             {
                 $ArrayRecords["UploadedFileID"][] = $Record["UploadedFileID"];
                 $ArrayRecords["UploadedFileDate"][] = $Record["UploadedFileDate"];
                 $ArrayRecords["UploadedFileName"][] = $Record["UploadedFileName"];
                 $ArrayRecords["UploadedFileDescription"][] = $Record["UploadedFileDescription"];
             }

             // Return result
             return $ArrayRecords;
         }
     }

     // ERROR
     return array();
 }


/**
 * Get forum messages filtered by some criterion
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-16
 *
 * @param $DbConnection             DB object              Object of the opened database connection
 * @param $ArrayParams              Mixed array            Contains the criterion used to filter the forum messages
 * @param $OrderBy                  String                 Criteria used to sort the forum messages. If < 0, DESC is used, otherwise ASC is used
 * @param $Page                     Integer                Number of the page to return [1..n]
 * @param $RecordsPerPage           Integer                Number of forum messages per page to return [1..n]
 *
 * @return Array of String                                 List of forum messages filtered, an empty array otherwise
 */
 function dbSearchForumMessage($DbConnection, $ArrayParams, $OrderBy = "", $Page = 1, $RecordsPerPage = 10)
 {
     // SQL request to find forum messages
     $Select = "SELECT fm.ForumMessageID, fm.ForumMessageDate, fm.ForumMessageContent, fm.ForumMessagePicture, fm.ForumReplyToMessageID,
                fm.ForumMessageUpdateDate, ft.ForumTopicID, fc.ForumCategoryID,
                sm.SupportMemberID, sm.SupportMemberLastname, sm.SupportMemberFirstname, sms.SupportMemberStateID, sms.SupportMemberStateName";
     $From = "FROM ForumCategories fc, ForumTopics ft, ForumMessages fm, SupportMembers sm, SupportMembersStates sms";
     $Where = "WHERE fc.ForumCategoryID = ft.ForumCategoryID AND ft.ForumTopicID = fm.ForumTopicID
               AND fm.SupportMemberID = sm.SupportMemberID AND sm.SupportMemberStateID = sms.SupportMemberStateID";
     $Having = "";

     if (count($ArrayParams) >= 0)
     {
         // <<< ForumCategoryID field >>>
         if ((array_key_exists("ForumCategoryID", $ArrayParams)) && (!empty($ArrayParams["ForumCategoryID"])))
         {
             if (is_array($ArrayParams["ForumCategoryID"]))
             {
                 $Where .= " AND fc.ForumCategoryID IN ".constructSQLINString($ArrayParams["ForumCategoryID"]);
             }
             else
             {
                 $Where .= " AND fc.ForumCategoryID = ".$ArrayParams["ForumCategoryID"];
             }
         }

         // <<< ForumTopicID field >>>
         if ((array_key_exists("ForumTopicID", $ArrayParams)) && (!empty($ArrayParams["ForumTopicID"])))
         {
             if (is_array($ArrayParams["ForumTopicID"]))
             {
                 $Where .= " AND ft.ForumTopicID IN ".constructSQLINString($ArrayParams["ForumTopicID"]);
             }
             else
             {
                 $Where .= " AND ft.ForumTopicID = ".$ArrayParams["ForumTopicID"];
             }
         }

         // <<< ForumTopicTitle field >>>
         if ((array_key_exists("ForumTopicTitle", $ArrayParams)) && (!empty($ArrayParams["ForumTopicTitle"])))
         {
             $Where .= " AND ft.ForumTopicTitle LIKE \"".$ArrayParams["ForumTopicTitle"]."\"";
         }

         // <<< ForumMessageID field >>>
         if ((array_key_exists("ForumMessageID", $ArrayParams)) && (!empty($ArrayParams["ForumMessageID"])))
         {
             if (is_array($ArrayParams["ForumMessageID"]))
             {
                 $Where .= " AND fm.ForumMessageID IN ".constructSQLINString($ArrayParams["ForumMessageID"]);
             }
             else
             {
                 $Where .= " AND fm.ForumMessageID = ".$ArrayParams["ForumMessageID"];
             }
         }

         // <<< Forum messages between 2 given dates >>>
         if ((array_key_exists("StartDate", $ArrayParams)) && (count($ArrayParams["StartDate"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $Where .= " AND DATE_FORMAT(fm.ForumMessageDate, '%Y-%m-%d') ".$ArrayParams["StartDate"][0]." \"".$ArrayParams["StartDate"][1]."\"";
         }

         if ((array_key_exists("EndDate", $ArrayParams)) && (count($ArrayParams["EndDate"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $Where .= " AND DATE_FORMAT(fm.ForumMessageDate, '%Y-%m-%d') ".$ArrayParams["EndDate"][0]." \"".$ArrayParams["EndDate"][1]."\"";
         }

         // <<< ForumMessageContent field >>>
         if ((array_key_exists("ForumMessageContent", $ArrayParams)) && (!empty($ArrayParams["ForumMessageContent"])))
         {
             $Where .= " AND fm.ForumMessageContent LIKE \"".$ArrayParams["ForumMessageContent"]."\"";
         }

         // <<< SupportMemberID field >>>
         if ((array_key_exists("SupportMemberID", $ArrayParams)) && (!empty($ArrayParams["SupportMemberID"])))
         {
             if (is_array($ArrayParams["SupportMemberID"]))
             {
                 $Where .= " AND fm.SupportMemberID IN ".constructSQLINString($ArrayParams["SupportMemberID"]);
             }
             else
             {
                 $Where .= " AND fm.SupportMemberID = ".$ArrayParams["SupportMemberID"];
             }
         }

         // <<< SupportMemberStateID field >>>
         if ((array_key_exists("SupportMemberStateID", $ArrayParams)) && (!empty($ArrayParams["SupportMemberStateID"])))
         {
             if (is_array($ArrayParams["SupportMemberStateID"]))
             {
                 $Where .= " AND sm.SupportMemberStateID IN ".constructSQLINString($ArrayParams["SupportMemberStateID"]);
             }
             else
             {
                 $Where .= " AND sm.SupportMemberStateID = ".$ArrayParams["SupportMemberStateID"];
             }
         }
     }

     // We take into account the page and the number of forum messages per page
     if ($Page < 1)
     {
         $Page = 1;
     }

     if ($RecordsPerPage < 0)
     {
         $RecordsPerPage = 10;
     }

     $Limit = '';
     if ($RecordsPerPage > 0)
     {
         $StartIndex = ($Page - 1) * $RecordsPerPage;
         $Limit = "LIMIT $StartIndex, $RecordsPerPage";
     }

     // We take into account the order by
     if ($OrderBy == "")
     {
         $StrOrderBy = "";
     }
     else
     {
         $StrOrderBy = " ORDER BY $OrderBy";
     }

     // We can launch the SQL request
     $DbResult = $DbConnection->query("$Select $From $Where GROUP BY fm.ForumMessageID $Having $StrOrderBy $Limit");

     if (!DB::isError($DbResult))
     {
         if ($DbResult->numRows() != 0)
         {
             // Creation of the result array
             $ArrayRecords = array(
                                   "ForumMessageID" => array(),
                                   "ForumMessageDate" => array(),
                                   "ForumMessageContent" => array(),
                                   "ForumMessagePicture" => array(),
                                   "ForumReplyToMessageID" => array(),
                                   "ForumMessageUpdateDate" => array(),
                                   "ForumTopicID" => array(),
                                   "ForumCategoryID" => array(),
                                   "SupportMemberID" => array(),
                                   "SupportMemberLastname" => array(),
                                   "SupportMemberFirstname" => array(),
                                   "SupportMemberStateID" => array(),
                                   "SupportMemberStateName" => array()
                                  );

             while($Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC))
             {
                 $ArrayRecords["ForumMessageID"][] = $Record["ForumMessageID"];
                 $ArrayRecords["ForumMessageDate"][] = $Record["ForumMessageDate"];
                 $ArrayRecords["ForumMessageContent"][] = $Record["ForumMessageContent"];
                 $ArrayRecords["ForumMessagePicture"][] = $Record["ForumMessagePicture"];
                 $ArrayRecords["ForumReplyToMessageID"][] = $Record["ForumReplyToMessageID"];
                 $ArrayRecords["ForumMessageUpdateDate"][] = $Record["ForumMessageUpdateDate"];
                 $ArrayRecords["ForumTopicID"][] = $Record["ForumTopicID"];
                 $ArrayRecords["ForumCategoryID"][] = $Record["ForumCategoryID"];
                 $ArrayRecords["SupportMemberID"][] = $Record["SupportMemberID"];
                 $ArrayRecords["SupportMemberLastname"][] = $Record["SupportMemberLastname"];
                 $ArrayRecords["SupportMemberFirstname"][] = $Record["SupportMemberFirstname"];
                 $ArrayRecords["SupportMemberStateID"][] = $Record["SupportMemberStateID"];
                 $ArrayRecords["SupportMemberStateName"][] = $Record["SupportMemberStateName"];
             }

             // Return result
             return $ArrayRecords;
         }
     }

     // ERROR
     return array();
 }


/**
 * Get the number of forum messages filtered by some criterion
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-16
 *
 * @param $DbConnection         DB object              Object of the opened database connection
 * @param $ArrayParams          Mixed array            Contains the criterion used to filter the forum messages
 *
 * @return Integer              Number of the forum messages found, 0 otherwise
 */
 function getNbdbSearchForumMessage($DbConnection, $ArrayParams)
 {
     // SQL request to find forum messages
     $Select = "SELECT fm.ForumMessageID";
     $From = "FROM ForumCategories fc, ForumTopics ft, ForumMessages fm, SupportMembers sm";
     $Where = "WHERE fc.ForumCategoryID = ft.ForumCategoryID AND ft.ForumTopicID = fm.ForumTopicID
               AND fm.SupportMemberID = sm.SupportMemberID";
     $Having = "";

     if (count($ArrayParams) >= 0)
     {
         // <<< ForumCategoryID field >>>
         if ((array_key_exists("ForumCategoryID", $ArrayParams)) && (!empty($ArrayParams["ForumCategoryID"])))
         {
             if (is_array($ArrayParams["ForumCategoryID"]))
             {
                 $Where .= " AND fc.ForumCategoryID IN ".constructSQLINString($ArrayParams["ForumCategoryID"]);
             }
             else
             {
                 $Where .= " AND fc.ForumCategoryID = ".$ArrayParams["ForumCategoryID"];
             }
         }

         // <<< ForumTopicID field >>>
         if ((array_key_exists("ForumTopicID", $ArrayParams)) && (!empty($ArrayParams["ForumTopicID"])))
         {
             if (is_array($ArrayParams["ForumTopicID"]))
             {
                 $Where .= " AND ft.ForumTopicID IN ".constructSQLINString($ArrayParams["ForumTopicID"]);
             }
             else
             {
                 $Where .= " AND ft.ForumTopicID = ".$ArrayParams["ForumTopicID"];
             }
         }

         // <<< ForumTopicTitle field >>>
         if ((array_key_exists("ForumTopicTitle", $ArrayParams)) && (!empty($ArrayParams["ForumTopicTitle"])))
         {
             $Where .= " AND ft.ForumTopicTitle LIKE \"".$ArrayParams["ForumTopicTitle"]."\"";
         }

         // <<< ForumMessageID field >>>
         if ((array_key_exists("ForumMessageID", $ArrayParams)) && (!empty($ArrayParams["ForumMessageID"])))
         {
             if (is_array($ArrayParams["ForumMessageID"]))
             {
                 $Where .= " AND fm.ForumMessageID IN ".constructSQLINString($ArrayParams["ForumMessageID"]);
             }
             else
             {
                 $Where .= " AND fm.ForumMessageID = ".$ArrayParams["ForumMessageID"];
             }
         }

         // <<< Forum messages between 2 given dates >>>
         if ((array_key_exists("StartDate", $ArrayParams)) && (count($ArrayParams["StartDate"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $Where .= " AND DATE_FORMAT(fm.ForumMessageDate, '%Y-%m-%d') ".$ArrayParams["StartDate"][0]." \"".$ArrayParams["StartDate"][1]."\"";
         }

         if ((array_key_exists("EndDate", $ArrayParams)) && (count($ArrayParams["EndDate"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $Where .= " AND DATE_FORMAT(fm.ForumMessageDate, '%Y-%m-%d') ".$ArrayParams["EndDate"][0]." \"".$ArrayParams["EndDate"][1]."\"";
         }

         // <<< ForumMessageContent field >>>
         if ((array_key_exists("ForumMessageContent", $ArrayParams)) && (!empty($ArrayParams["ForumMessageContent"])))
         {
             $Where .= " AND fm.ForumMessageContent LIKE \"".$ArrayParams["ForumMessageContent"]."\"";
         }

         // <<< SupportMemberID field >>>
         if ((array_key_exists("SupportMemberID", $ArrayParams)) && (!empty($ArrayParams["SupportMemberID"])))
         {
             if (is_array($ArrayParams["SupportMemberID"]))
             {
                 $Where .= " AND fm.SupportMemberID IN ".constructSQLINString($ArrayParams["SupportMemberID"]);
             }
             else
             {
                 $Where .= " AND fm.SupportMemberID = ".$ArrayParams["SupportMemberID"];
             }
         }

         // <<< SupportMemberStateID field >>>
         if ((array_key_exists("SupportMemberStateID", $ArrayParams)) && (!empty($ArrayParams["SupportMemberStateID"])))
         {
             if (is_array($ArrayParams["SupportMemberStateID"]))
             {
                 $Where .= " AND sm.SupportMemberStateID IN ".constructSQLINString($ArrayParams["SupportMemberStateID"]);
             }
             else
             {
                 $Where .= " AND sm.SupportMemberStateID = ".$ArrayParams["SupportMemberStateID"];
             }
         }
     }

     // We can launch the SQL request
     $DbResult = $DbConnection->query("$Select $From $Where GROUP BY fm.ForumMessageID $Having");
     if (!DB::isError($DbResult))
     {
         return $DbResult->numRows();
     }

     // ERROR
     return 0;
 }


/**
 * Check if a forum topic subscribtion exists in the ForumTopicsSubscribtions table, thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-22
 *
 * @param $DbConnection                     DB object    Object of the opened database connection
 * @param $ForumTopicSubscribtionID         Integer      ID of the forum topic subscribtion searched [1..n]
 *
 * @return Boolean                          TRUE if the forum topic subscribtion exists, FALSE otherwise
 */
 function isExistingForumTopicSubscribtion($DbConnection, $ForumTopicSubscribtionID)
 {
     if ($ForumTopicSubscribtionID > 0)
     {
         $DbResult = $DbConnection->query("SELECT ForumTopicSubscribtionID FROM ForumTopicsSubscribtions
                                           WHERE ForumTopicSubscribtionID = $ForumTopicSubscribtionID");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 1)
             {
                 // The forum topic subscribtion exists
                 return TRUE;
             }
         }
     }

     // The forum topic subscribtion doesn't exist
     return FALSE;
 }


/**
 * Add a forum topic subscribtion in the ForumTopicsSubscribtions table
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-22
 *
 * @param $DbConnection                   DB object    Object of the opened database connection
 * @param $ForumCategoryID                Integer      ID of the forum topic concerned by the subscribtion [1..n]
 * @param $SupportMemberID                Integer      ID of the support member concerned by the subscribtion [1..n]
 * @param $ForumTopicSubscribtionEmail    String       Email to notify
 *
 * @return Integer                        The primary key of the forum topic subscribtion [1..n], 0 otherwise
 */
 function dbAddForumTopicSubscribtion($DbConnection, $ForumTopicID, $SupportMemberID, $ForumTopicSubscribtionEmail)
 {
     if ((!empty($ForumTopicSubscribtionEmail)) && ($ForumTopicID > 0) && ($SupportMemberID > 0))
     {
         // Check if the forum topic subscribtion is unique for a topic, a supporter and an e-mail
         $DbResult = $DbConnection->query("SELECT ForumTopicSubscribtionID FROM ForumTopicsSubscribtions
                                           WHERE ForumTopicID = $ForumTopicID AND SupportMemberID = $SupportMemberID
                                           AND ForumTopicSubscribtionEmail = \"$ForumTopicSubscribtionEmail\"");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 0)
             {
                 // It's a new forum topic subscribtion
                 $id = getNewPrimaryKey($DbConnection, "ForumTopicsSubscribtions", "ForumTopicSubscribtionID");
                 if ($id != 0)
                 {
                     $DbResult = $DbConnection->query("INSERT INTO ForumTopicsSubscribtions SET ForumTopicSubscribtionID = $id,
                                                       ForumTopicSubscribtionEmail = \"$ForumTopicSubscribtionEmail\",
                                                       ForumTopicID = $ForumTopicID, SupportMemberID = $SupportMemberID");

                     if (!DB::isError($DbResult))
                     {
                         return $id;
                     }
                 }
             }
             else
             {
                 // The forum topic subscribtion already exists
                 $Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC);
                 return $Record['ForumTopicSubscribtionID'];
             }
         }
     }

     // ERROR
     return 0;
 }


/**
 * Get forum topic subscribtions by some criterion
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-22
 *
 * @param $DbConnection             DB object              Object of the opened database connection
 * @param $ArrayParams              Mixed array            Contains the criterion used to get subscribtions
 *                                                         of the forum topics
 *
 * @return Array of String          List of subscribtions of forum topics, an empty array otherwise
 */
 function getForumTopicsSubscribtions($DbConnection, $ArrayParams)
 {
     $Select = "SELECT ft.ForumTopicID, ft.ForumCategoryID, fts.ForumTopicSubscribtionID, fts.ForumTopicSubscribtionEmail,
                fts.SupportMemberID";
     $From = "FROM ForumTopics ft, ForumTopicsSubscribtions fts";
     $Where = "WHERE ft.ForumTopicID = fts.ForumTopicID";
     $Having = "";

     if (count($ArrayParams) >= 0)
     {
         // <<< ForumTopicSubscribtionID field >>>
         if ((array_key_exists("ForumTopicSubscribtionID", $ArrayParams)) && (!empty($ArrayParams["ForumTopicSubscribtionID"])))
         {
             if (is_array($ArrayParams["ForumTopicSubscribtionID"]))
             {
                 $Where .= " AND fts.ForumTopicSubscribtionID IN ".constructSQLINString($ArrayParams["ForumTopicSubscribtionID"]);
             }
             else
             {
                 $Where .= " AND fts.ForumTopicSubscribtionID = ".$ArrayParams["ForumTopicSubscribtionID"];
             }
         }

         // <<< ForumCategoryID field >>>
         if ((array_key_exists("ForumCategoryID", $ArrayParams)) && (!empty($ArrayParams["ForumCategoryID"])))
         {
             if (is_array($ArrayParams["ForumCategoryID"]))
             {
                 $Where .= " AND ft.ForumCategoryID IN ".constructSQLINString($ArrayParams["ForumCategoryID"]);
             }
             else
             {
                 $Where .= " AND ft.ForumCategoryID = ".$ArrayParams["ForumCategoryID"];
             }
         }

         // <<< ForumTopicID field >>>
         if ((array_key_exists("ForumTopicID", $ArrayParams)) && (!empty($ArrayParams["ForumTopicID"])))
         {
             if (is_array($ArrayParams["ForumTopicID"]))
             {
                 $Where .= " AND ft.ForumTopicID IN ".constructSQLINString($ArrayParams["ForumTopicID"]);
             }
             else
             {
                 $Where .= " AND ft.ForumTopicID = ".$ArrayParams["ForumTopicID"];
             }
         }

         // <<< SupportMemberID field >>>
         if ((array_key_exists("SupportMemberID", $ArrayParams)) && (!empty($ArrayParams["SupportMemberID"])))
         {
             if (is_array($ArrayParams["SupportMemberID"]))
             {
                 $Where .= " AND fts.SupportMemberID IN ".constructSQLINString($ArrayParams["SupportMemberID"]);
             }
             else
             {
                 $Where .= " AND fts.SupportMemberID = ".$ArrayParams["SupportMemberID"];
             }
         }

         // <<< ForumTopicSubscribtionEmail field >>>
         if ((array_key_exists("ForumTopicSubscribtionEmail", $ArrayParams)) && (!empty($ArrayParams["ForumTopicSubscribtionEmail"])))
         {
             $Where .= " AND fts.ForumTopicSubscribtionEmail LIKE \"".$ArrayParams["ForumTopicSubscribtionEmail"]."\"";
         }
     }

     // We can launch the SQL request
     $DbResult = $DbConnection->query("$Select $From $Where $Having");

     if (!DB::isError($DbResult))
     {
         if ($DbResult->numRows() != 0)
         {
             // Creation of the result array
             $ArrayRecords = array();

             while($Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC))
             {
                 if (!isset($ArrayRecords[$Record["ForumTopicID"]]))
                 {
                     $ArrayRecords[$Record["ForumTopicID"]] = array(
                                                                    "ForumCategoryID" => array(),
                                                                    "ForumTopicSubscribtionID" => array(),
                                                                    "ForumTopicSubscribtionEmail" => array(),
                                                                    "SupportMemberID" => array()
                                                                   );
                 }

                 $ArrayRecords[$Record["ForumTopicID"]]['ForumCategoryID'][] = $Record["ForumCategoryID"];
                 $ArrayRecords[$Record["ForumTopicID"]]['ForumTopicSubscribtionID'][] = $Record["ForumTopicSubscribtionID"];
                 $ArrayRecords[$Record["ForumTopicID"]]['ForumTopicSubscribtionEmail'][] = $Record["ForumTopicSubscribtionEmail"];
                 $ArrayRecords[$Record["ForumTopicID"]]['SupportMemberID'][] = $Record["SupportMemberID"];
             }

             // Return result
             return $ArrayRecords;
         }
     }

     // ERROR
     return array();
 }


/**
 * Delete a forum topic subscribtion in the ForumTopicsSubscribtions table
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-04-23
 *
 * @param $DbConnection                   DB object      Object of the opened database connection
 * @param $ForumTopicSubscribtionID       Integer        ID of the topic subscribtion to delete [1..n]
 *
 * @return Boolean                        TRUE if the topic subscribtion is deleted, FALSE otherwise
 */
 function dbDeleteForumTopicSubscribtion($DbConnection, $ForumTopicSubscribtionID)
 {
     // The parameters are correct?
     if ($ForumTopicSubscribtionID > 0)
     {
         // Delete the topic subscribtion
         $DbResult = $DbConnection->query("DELETE FROM ForumTopicsSubscribtions
                                           WHERE ForumTopicSubscribtionID = $ForumTopicSubscribtionID");
         if (!DB::isError($DbResult))
         {
             // Topic subscribtion deleted
             return TRUE;
         }
     }

     // ERROR
     return FALSE;
 }


/**
 * Treat forum data of a support member for GDPR, thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-05-01
 *
 * @param $DbConnection                 DB object      Object of the opened database connection
 * @param $AnonymizedSupportMemberID    Integer        ID of the support member used to anonymize data [1..n]
 * @param $ArrayParams                  Mixed array    Contains other parameters to use to apply
 *                                                     GDPR treatment
 *
 * @return Boolean                      TRUE if GDPR treatment is done for the support member,
 *                                      FALSE otherwise
 */
 function dbForumGDPRTreatment($DbConnection, $AnonymizedSupportMemberID, $ArrayParams = array())
 {
     // The parameters are correct?
     if ($AnonymizedSupportMemberID > 0)
     {
         $bLastReadsDeleted = FALSE;
         $bSubscribtionsDeleted = FALSE;
         $bUploadedFilesAnonymized = FALSE;
         $bMessagesAnonymized = FALSE;
         $bTopicsAnonymized = FALSE;
         $bLogEventsAnonymized = FALSE;

         $sSupporterCondition = '';
         $sSupporterConditionFM = '';
         if ((array_key_exists("SupportMemberID", $ArrayParams)) && (!empty($ArrayParams["SupportMemberID"])))
         {
             // To limit to some given supporters
             if (is_array($ArrayParams["SupportMemberID"]))
             {
                 $sSupporterCondition = " AND SupportMemberID IN ".constructSQLINString($ArrayParams["SupportMemberID"]);
                 $sSupporterConditionFM = " AND fm.SupportMemberID IN ".constructSQLINString($ArrayParams["SupportMemberID"]);
             }
             else
             {
                 $sSupporterCondition = " AND SupportMemberID = ".$ArrayParams["SupportMemberID"];
                 $sSupporterConditionFM = " AND fm.SupportMemberID = ".$ArrayParams["SupportMemberID"];
             }
         }

         // Delete the last read messages of topic of the supporter
         $DbResult = $DbConnection->query("DELETE FROM ForumTopicsLastReads WHERE 1=1 $sSupporterCondition");
         if (!DB::isError($DbResult))
         {
             // Last read messages deleted
             $bLastReadsDeleted = TRUE;
         }

         // Delete the topic subscribtions of the supporter
         $DbResult = $DbConnection->query("DELETE FROM ForumTopicsSubscribtions WHERE 1=1 $sSupporterCondition");
         if (!DB::isError($DbResult))
         {
             // Topic subscribtions deleted
             $bSubscribtionsDeleted = TRUE;
         }

         // We get uploaded files of messages
         $DbResult = $DbConnection->query("SELECT uf.UploadedFileID
                                           FROM ForumMessages fm, UploadedFiles uf
                                           WHERE fm.ForumMessageID = uf.ObjectID $sSupporterConditionFM
                                           AND uf.UploadedFileObjectType = ".OBJ_FORUM_MESSAGE);
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() >= 0)
             {
                 // Creation of the result array
                 $ArrayUploadedFileID = array();

                 while($Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC))
                 {
                     $ArrayUploadedFileID[] = $Record['UploadedFileID'];
                 }

                 if (empty($ArrayUploadedFileID))
                 {
                     // No uploaded file linked to messages of the forum
                     $bUploadedFilesAnonymized = TRUE;
                 }
                 else
                 {
                     // There are some uploaded files linked to messages of the forum : we delete them
                     $DbResult2 = $DbConnection->query("DELETE FROM LogEvents WHERE LogEventItemType = \"".EVT_UPLOADED_FILE."\"
                                                        $sSupporterCondition AND LogEventItemID IN ".constructSQLINString($ArrayUploadedFileID));
                     if (!DB::isError($DbResult2))
                     {
                         $bUploadedFilesAnonymized = TRUE;
                     }
                 }
             }
         }

         // We remove the content of messages and anonymize the messages
         $ForumMessageContent = '';
         if ((array_key_exists("ForumMessageContent", $ArrayParams)) && (!empty($ArrayParams["ForumMessageContent"])))
         {
             $ForumMessageContent = $ArrayParams["ForumMessageContent"];
         }

         $DbResult = $DbConnection->query("UPDATE ForumMessages SET ForumMessageContent = \"$ForumMessageContent\"
                                           WHERE 1=1 $sSupporterCondition");

         $DbResult = $DbConnection->query("UPDATE ForumMessages SET SupportMemberID = $AnonymizedSupportMemberID
                                           WHERE 1=1 $sSupporterCondition");
         if (!DB::isError($DbResult))
         {
             // Forum messages anonymized
             $bMessagesAnonymized = TRUE;
         }

         // We remove family pictures of messages and set a default icon
         $sDefaultIcon = basename($GLOBALS['CONF_FORUM_ICONS']['TopicIcons'][0]);
         $DbResult = $DbConnection->query("UPDATE ForumMessages SET ForumMessagePicture = \"$sDefaultIcon\"
                                           WHERE ForumMessagePicture REGEXP 'F[0-9]+-[0-9]_' <> 0 $sSupporterCondition");

         // We anonymize the topics
         $DbResult = $DbConnection->query("UPDATE ForumTopics SET SupportMemberID = $AnonymizedSupportMemberID
                                           WHERE 1=1 $sSupporterCondition");
         if (!DB::isError($DbResult))
         {
             // Forum topics anonymized
             $bTopicsAnonymized = TRUE;
         }

         // We delete log events about the forum and the supporter
         $DbResult = $DbConnection->query("DELETE FROM LogEvents WHERE LogEventItemType = \"".EVT_FORUM."\" $sSupporterCondition");
         if (!DB::isError($DbResult))
         {
             // Log events about the forum anonymized
             $bLogEventsAnonymized = TRUE;
         }

         return (($bLastReadsDeleted) && ($bSubscribtionsDeleted) && ($bUploadedFilesAnonymized) && ($bMessagesAnonymized)
                 && ($bTopicsAnonymized) && ($bLogEventsAnonymized));
     }

     // ERROR
     return FALSE;
 }
?>