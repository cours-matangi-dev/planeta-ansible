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
 * Common module : library of database functions used for the DocumentsApprovals and DocumentsFamiliesApprovals tables
 *
 * @author Christophe Javouhey
 * @version 3.6
 * @since 2019-05-07
 */


/**
 * Check if a document approval exists in the DocumentsApprovals table, thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2019-05-07
 *
 * @param $DbConnection          DB object    Object of the opened database connection
 * @param $DocumentApprovalID    Integer      ID of the document approval searched [1..n]
 *
 * @return Boolean               TRUE if the document approval exists, FALSE otherwise
 */
 function isExistingDocumentApproval($DbConnection, $DocumentApprovalID)
 {
     $DbResult = $DbConnection->query("SELECT DocumentApprovalID FROM DocumentsApprovals WHERE DocumentApprovalID = $DocumentApprovalID");
     if (!DB::isError($DbResult))
     {
         if ($DbResult->numRows() == 1)
         {
             // The document approval exists
             return TRUE;
         }
     }

     // The document approval doesn't exist
     return FALSE;
 }


/**
 * Add a document approval in the DocumentsApprovals table
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2019-05-07
 *
 * @param $DbConnection                  DB object    Object of the opened database connection
 * @param $DocumentApprovalDate          Datetime     Creation date of the document approval (yyyy-mm-dd hh:mm:ss)
 * @param $DocumentApprovalName          String       Name of the document approval
 * @param $DocumentApprovalFile          String       Filename of the document approval
 * @param $DocumentApprovalType          Integer      Type of the document approval [0..n]
 *
 * @return Integer                       The primary key of the document approval [1..n], 0 otherwise
 */
 function dbAddDocumentApproval($DbConnection, $DocumentApprovalDate, $DocumentApprovalName, $DocumentApprovalFile, $DocumentApprovalType = 0)
 {
     if ((!empty($DocumentApprovalName)) && (!empty($DocumentApprovalFile)) && ($DocumentApprovalType >= 0))
     {
         // Check if the document approval is a new document approval
         $DbResult = $DbConnection->query("SELECT DocumentApprovalID FROM DocumentsApprovals WHERE DocumentApprovalName = \"$DocumentApprovalName\"
                                           AND DocumentApprovalType = $DocumentApprovalType");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 0)
             {
                 // Check if the DocumentApprovalDate is valide
                 if (preg_match("[\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d]", $DocumentApprovalDate) == 0)
                 {
                     return 0;
                 }
                 else
                 {
                     $DocumentApprovalDate = ", DocumentApprovalDate = \"$DocumentApprovalDate\"";
                 }

                 // It's a new document approval
                 $id = getNewPrimaryKey($DbConnection, "DocumentsApprovals", "DocumentApprovalID");
                 if ($id != 0)
                 {
                     $DbResult = $DbConnection->query("INSERT INTO DocumentsApprovals SET DocumentApprovalID = $id, DocumentApprovalName = \"$DocumentApprovalName\",
                                                       DocumentApprovalFile = \"$DocumentApprovalFile\", DocumentApprovalType = $DocumentApprovalType
                                                       $DocumentApprovalDate");
                     if (!DB::isError($DbResult))
                     {
                         return $id;
                     }
                 }
             }
             else
             {
                 // The document approval already exists
                 $Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC);
                 return $Record['DocumentApprovalID'];
             }
         }
     }

     // ERROR
     return 0;
 }


/**
 * Update an existing document approval in the DocumentsApprovals table
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2019-05-07
 *
 * @param $DbConnection                  DB object    Object of the opened database connection
 * @param $DocumentApprovalID            Integer      ID of the document approval to update [1..n]
 * @param $DocumentApprovalDate          Datetime     Creation date of the document approval (yyyy-mm-dd hh:mm:ss)
 * @param $DocumentApprovalName          String       Name of the document approval
 * @param $DocumentApprovalFile          String       Filename of the document approval
 * @param $DocumentApprovalType          Integer      Type of the document approval [0..n]
 *
 * @return Integer                       The primary key of the document approval [1..n], 0 otherwise
 */
 function dbUpdateDocumentApproval($DbConnection, $DocumentApprovalID, $DocumentApprovalDate, $DocumentApprovalName, $DocumentApprovalFile = NULL, $DocumentApprovalType = NULL)
 {
     // The parameters which are NULL will be ignored for the update
     $ArrayParamsUpdate = array();

     // Verification of the parameters
     if (($DocumentApprovalID < 1) || (!isInteger($DocumentApprovalID)))
     {
         // ERROR
         return 0;
     }

     // Check if the DocumentApprovalDate is valide
     if (!is_null($DocumentApprovalDate))
     {
         if (preg_match("[\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d]", $DocumentApprovalDate) == 0)
         {
             return 0;
         }
         else
         {
             // The DocumentApprovalDate field will be updated
             $ArrayParamsUpdate[] = "DocumentApprovalDate = \"$DocumentApprovalDate\"";
         }
     }

     if (!is_Null($DocumentApprovalName))
     {
         if (empty($DocumentApprovalName))
         {
             // ERROR
             return 0;
         }
         else
         {
             // The DocumentApprovalName field will be updated
             $ArrayParamsUpdate[] = "DocumentApprovalName = \"$DocumentApprovalName\"";
         }
     }

     if (!is_Null($DocumentApprovalFile))
     {
         if (empty($DocumentApprovalFile))
         {
             // ERROR
             return 0;
         }
         else
         {
             // The DocumentApprovalFile field will be updated
             $ArrayParamsUpdate[] = "DocumentApprovalFile = \"$DocumentApprovalFile\"";
         }
     }

     if (!is_Null($DocumentApprovalType))
     {
         if (($DocumentApprovalType < 0) || (!isInteger($DocumentApprovalType)))
         {
             // ERROR
             return 0;
         }
         else
         {
             // The DocumentApprovalType field will be updated
             $ArrayParamsUpdate[] = "DocumentApprovalType = $DocumentApprovalType";
         }
     }

     // Here, the parameters are correct, we check if the document approval exists
     if (isExistingDocumentApproval($DbConnection, $DocumentApprovalID))
     {
         // We check if the document approval name is unique
         $DbResult = $DbConnection->query("SELECT DocumentApprovalID FROM DocumentsApprovals WHERE DocumentApprovalName = \"$DocumentApprovalName\"
                                           AND DocumentApprovalType = $DocumentApprovalType AND DocumentApprovalID <> $DocumentApprovalID");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 0)
             {
                 // The document approval exists and is unique : we can update if there is at least 1 parameter
                 if (count($ArrayParamsUpdate) > 0)
                 {
                     $DbResult = $DbConnection->query("UPDATE DocumentsApprovals SET ".implode(", ", $ArrayParamsUpdate)
                                                      ." WHERE DocumentApprovalID = $DocumentApprovalID");
                     if (!DB::isError($DbResult))
                     {
                         // Document approval updated
                         return $DocumentApprovalID;
                     }
                 }
                 else
                 {
                     // The update isn't usefull
                     return $DocumentApprovalID;
                 }
             }
         }
     }

     // ERROR
     return 0;
 }


/**
 * Give the families' approvals for a document approval, thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2019-05-07
 *
 * @param $DbConnection              DB object    Object of the opened database connection
 * @param $DocumentApprovalID        Integer      ID of the document approval for which we want the families' approvals [1..n]
 * @param $OrderBy                   String       To order the families' approvals
 *
 * @return Mixed array               All fields values of families' approvals of the document approval if it exists,
 *                                   an empty array otherwise
 */
 function getFamiliesApprovalsOfDocumentApproval($DbConnection, $DocumentApprovalID, $OrderBy = 'FamilyLastname')
 {
     if ($DocumentApprovalID > 0)
     {
         if (empty($OrderBy))
         {
             $OrderBy = 'FamilyLastname';
         }

         // We get the families' approvals of the document approval
         $DbResult = $DbConnection->query("SELECT dfa.DocumentFamilyApprovalID, dfa.DocumentFamilyApprovalDate, dfa.DocumentFamilyApprovalComment,
                                           f.FamilyID, f.FamilyLastname, sm.SupportMemberID, sm.SupportMemberLastname, sm.SupportMemberFirstname
                                           FROM DocumentsFamiliesApprovals dfa, SupportMembers sm LEFT JOIN Families f ON (sm.FamilyID = f.FamilyID)
                                           WHERE dfa.DocumentApprovalID = $DocumentApprovalID AND dfa.SupportMemberID = sm.SupportMemberID
                                           ORDER BY $OrderBy");
         if (!DB::isError($DbResult))
         {
             // Creation of the result array
             $ArrayRecords = array(
                                  "DocumentFamilyApprovalID" => array(),
                                  "DocumentFamilyApprovalDate" => array(),
                                  "DocumentFamilyApprovalComment" => array(),
                                  "FamilyID" => array(),
                                  "FamilyLastname" => array(),
                                  "SupportMemberID" => array(),
                                  "SupportMemberLastname" => array(),
                                  "SupportMemberFirstname" => array()
                                 );

             while($Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC))
             {
                 $ArrayRecords["DocumentFamilyApprovalID"][] = $Record["DocumentFamilyApprovalID"];
                 $ArrayRecords["DocumentFamilyApprovalDate"][] = $Record["DocumentFamilyApprovalDate"];
                 $ArrayRecords["DocumentFamilyApprovalComment"][] = $Record["DocumentFamilyApprovalComment"];
                 $ArrayRecords["FamilyID"][] = $Record["FamilyID"];
                 $ArrayRecords["FamilyLastname"][] = $Record["FamilyLastname"];
                 $ArrayRecords["SupportMemberID"][] = $Record["SupportMemberID"];
                 $ArrayRecords["SupportMemberLastname"][] = $Record["SupportMemberLastname"];
                 $ArrayRecords["SupportMemberFirstname"][] = $Record["SupportMemberFirstname"];
             }

             // Return result
             return $ArrayRecords;
         }
     }

     // ERROR
     return array();
 }


/**
 * Get children filtered by some criterion
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2019-05-07
 *
 * @param $DbConnection             DB object              Object of the opened database connection
 * @param $ArrayParams              Mixed array            Contains the criterion used to filter the documents approvals
 * @param $OrderBy                  String                 Criteria used to sort the documents approvals. If < 0, DESC is used, otherwise ASC is used
 * @param $Page                     Integer                Number of the page to return [1..n]
 * @param $RecordsPerPage           Integer                Number of documents approvals per page to return [1..n]
 *
 * @return Array of String                                 List of documents approvals filtered, an empty array otherwise
 */
 function dbSearchDocumentApproval($DbConnection, $ArrayParams, $OrderBy = "", $Page = 1, $RecordsPerPage = 10)
 {
     // SQL request to find documents approvals
     $Select = "SELECT da.DocumentApprovalID, da.DocumentApprovalDate, da.DocumentApprovalName, da.DocumentApprovalFile, da.DocumentApprovalType,
                COUNT(DocumentFamilyApprovalID) AS NbApprovals";
     $From = "FROM DocumentsApprovals da LEFT JOIN DocumentsFamiliesApprovals dfa ON (da.DocumentApprovalID = dfa.DocumentApprovalID)
              LEFT JOIN SupportMembers sm ON (dfa.SupportMemberID = sm.SupportMemberID) LEFT JOIN Families f ON (sm.FamilyID = f.FamilyID)";
     $Where = " WHERE 1=1";
     $Having = "";

     if (count($ArrayParams) >= 0)
     {
         // <<< DocumentApprovalID field >>>
         if ((array_key_exists("DocumentApprovalID", $ArrayParams)) && (!empty($ArrayParams["DocumentApprovalID"])))
         {
             if (is_array($ArrayParams["DocumentApprovalID"]))
             {
                 $Where .= " AND da.DocumentApprovalID IN ".constructSQLINString($ArrayParams["DocumentApprovalID"]);
             }
             else
             {
                 $Where .= " AND da.DocumentApprovalID = ".$ArrayParams["DocumentApprovalID"];
             }
         }

         // <<< DocumentApprovalName field >>>
         if ((array_key_exists("DocumentApprovalName", $ArrayParams)) && (!empty($ArrayParams["DocumentApprovalName"])))
         {
             $Where .= " AND da.DocumentApprovalName LIKE \"".$ArrayParams["DocumentApprovalName"]."\"";
         }

         // <<< DocumentApprovalType >>>
         if ((array_key_exists("DocumentApprovalType", $ArrayParams)) && (count($ArrayParams["DocumentApprovalType"]) > 0))
         {
             $Where .= " AND da.DocumentApprovalType IN ".constructSQLINString($ArrayParams["DocumentApprovalType"]);
         }

         // <<< Documents approvals between 2 given dates >>>
         if ((array_key_exists("StartDate", $ArrayParams)) && (count($ArrayParams["StartDate"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $Where .= " AND da.DocumentApprovalDate ".$ArrayParams["StartDate"][0]." \"".$ArrayParams["StartDate"][1]."\"";
         }

         if ((array_key_exists("EndDate", $ArrayParams)) && (count($ArrayParams["EndDate"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $Where .= " AND da.DocumentApprovalDate ".$ArrayParams["EndDate"][0]." \"".$ArrayParams["EndDate"][1]."\"";
         }

         // <<< FamilyID field >>>
         if ((array_key_exists("FamilyID", $ArrayParams)) && (!empty($ArrayParams["FamilyID"])))
         {
             if (is_array($ArrayParams["FamilyID"]))
             {
                 $Where .= " AND f.FamilyID IN ".constructSQLINString($ArrayParams["FamilyID"]);
             }
             else
             {
                 $Where .= " AND f.FamilyID = ".$ArrayParams["FamilyID"];
             }
         }

         // <<< Lastname field >>>
         if ((array_key_exists("FamilyLastname", $ArrayParams)) && (!empty($ArrayParams["FamilyLastname"])))
         {
             $Where .= " AND f.FamilyLastname LIKE \"".$ArrayParams["FamilyLastname"]."\"";
         }

         // <<< SupportMemberID field >>>
         if ((array_key_exists("SupportMemberID", $ArrayParams)) && (!empty($ArrayParams["SupportMemberID"])))
         {
             if (is_array($ArrayParams["SupportMemberID"]))
             {
                 $Where .= " AND sm.SupportMemberID IN ".constructSQLINString($ArrayParams["SupportMemberID"]);
             }
             else
             {
                 $Where .= " AND sm.SupportMemberID = ".$ArrayParams["SupportMemberID"];
             }
         }

         // <<< SupportMemberLastname field >>>
         if ((array_key_exists("SupportMemberLastname", $ArrayParams)) && (!empty($ArrayParams["SupportMemberLastname"])))
         {
             $Where .= " AND sm.SupportMemberLastname LIKE \"".$ArrayParams["SupportMemberLastname"]."\"";
         }

         // <<< Option : get activated documents approvals >>>
         if (array_key_exists("Activated", $ArrayParams))
         {
             if ((array_key_exists("SchoolYear", $ArrayParams)) && (count($ArrayParams["SchoolYear"]) > 0))
             {
                 // We search documents approvals activated for a given school year
                 $SchoolYearStartDate = $GLOBALS['CONF_SCHOOL_YEAR_START_DATES'][$ArrayParams["SchoolYear"][0]];
                 $SchoolYearEndDate = date('Y-m-05',
                                           strtotime($ArrayParams["SchoolYear"][0].'-'.$GLOBALS['CONF_SCHOOL_YEAR_LAST_MONTH'].'-01'));

                 $Where .= " AND (((da.DocumentApprovalDate <= \"$SchoolYearStartDate\") OR (da.DocumentApprovalDate BETWEEN \"$SchoolYearStartDate\" AND \"$SchoolYearEndDate\"))"
                          ." AND (da.DocumentApprovalDate <= \"$SchoolYearEndDate\"))";
             }
         }
     }

     // We take into account the page and the number of documents approvals per page
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
     $DbResult = $DbConnection->query("$Select $From $Where GROUP BY DocumentApprovalID $Having $StrOrderBy $Limit");
     if (!DB::isError($DbResult))
     {
         // Creation of the result array
         $ArrayRecords = array(
                               "DocumentApprovalID" => array(),
                               "DocumentApprovalDate" => array(),
                               "DocumentApprovalName" => array(),
                               "DocumentApprovalFile" => array(),
                               "DocumentApprovalType" => array(),
                               "NbApprovals" => array()
                               );

         if ($DbResult->numRows() != 0)
         {
             while($Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC))
             {
                 $ArrayRecords["DocumentApprovalID"][] = $Record["DocumentApprovalID"];
                 $ArrayRecords["DocumentApprovalDate"][] = $Record["DocumentApprovalDate"];
                 $ArrayRecords["DocumentApprovalName"][] = $Record["DocumentApprovalName"];
                 $ArrayRecords["DocumentApprovalFile"][] = $Record["DocumentApprovalFile"];
                 $ArrayRecords["DocumentApprovalType"][] = $Record["DocumentApprovalType"];
                 $ArrayRecords["NbApprovals"][] = $Record["NbApprovals"];
             }
         }

         // Return result
         return $ArrayRecords;
     }

     // ERROR
     return array();
 }


/**
 * Get the number of documents approvals filtered by some criterion
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2019-05-07
 *
 * @param $DbConnection         DB object              Object of the opened database connection
 * @param $ArrayParams          Mixed array            Contains the criterion used to filter the documents approvals
 *
 * @return Integer              Number of the documents approvals found, 0 otherwise
 */
 function getNbdbSearchDocumentApproval($DbConnection, $ArrayParams)
 {
     // SQL request to find documents approvals
     $Select = "SELECT da.DocumentApprovalID";
     $From = "FROM DocumentsApprovals da LEFT JOIN DocumentsFamiliesApprovals dfa ON (da.DocumentApprovalID = dfa.DocumentApprovalID)
              LEFT JOIN SupportMembers sm ON (dfa.SupportMemberID = sm.SupportMemberID) LEFT JOIN Families f ON (sm.FamilyID = f.FamilyID)";
     $Where = " WHERE 1=1";
     $Having = "";

     if (count($ArrayParams) >= 0)
     {
         // <<< DocumentApprovalID field >>>
         if ((array_key_exists("DocumentApprovalID", $ArrayParams)) && (!empty($ArrayParams["DocumentApprovalID"])))
         {
             if (is_array($ArrayParams["DocumentApprovalID"]))
             {
                 $Where .= " AND da.DocumentApprovalID IN ".constructSQLINString($ArrayParams["DocumentApprovalID"]);
             }
             else
             {
                 $Where .= " AND da.DocumentApprovalID = ".$ArrayParams["DocumentApprovalID"];
             }
         }

         // <<< DocumentApprovalName field >>>
         if ((array_key_exists("DocumentApprovalName", $ArrayParams)) && (!empty($ArrayParams["DocumentApprovalName"])))
         {
             $Where .= " AND da.DocumentApprovalName LIKE \"".$ArrayParams["DocumentApprovalName"]."\"";
         }

         // <<< DocumentApprovalType >>>
         if ((array_key_exists("DocumentApprovalType", $ArrayParams)) && (count($ArrayParams["DocumentApprovalType"]) > 0))
         {
             $Where .= " AND da.DocumentApprovalType IN ".constructSQLINString($ArrayParams["DocumentApprovalType"]);
         }

         // <<< Documents approvals between 2 given dates >>>
         if ((array_key_exists("StartDate", $ArrayParams)) && (count($ArrayParams["StartDate"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $Where .= " AND da.DocumentApprovalDate ".$ArrayParams["StartDate"][0]." \"".$ArrayParams["StartDate"][1]."\"";
         }

         if ((array_key_exists("EndDate", $ArrayParams)) && (count($ArrayParams["EndDate"]) == 2))
         {
             // [0] -> operator (>, <, >=...), [1] -> date
             $Where .= " AND da.DocumentApprovalDate ".$ArrayParams["EndDate"][0]." \"".$ArrayParams["EndDate"][1]."\"";
         }

         // <<< FamilyID field >>>
         if ((array_key_exists("FamilyID", $ArrayParams)) && (!empty($ArrayParams["FamilyID"])))
         {
             if (is_array($ArrayParams["FamilyID"]))
             {
                 $Where .= " AND f.FamilyID IN ".constructSQLINString($ArrayParams["FamilyID"]);
             }
             else
             {
                 $Where .= " AND f.FamilyID = ".$ArrayParams["FamilyID"];
             }
         }

         // <<< Lastname field >>>
         if ((array_key_exists("FamilyLastname", $ArrayParams)) && (!empty($ArrayParams["FamilyLastname"])))
         {
             $Where .= " AND f.FamilyLastname LIKE \"".$ArrayParams["FamilyLastname"]."\"";
         }

         // <<< SupportMemberID field >>>
         if ((array_key_exists("SupportMemberID", $ArrayParams)) && (!empty($ArrayParams["SupportMemberID"])))
         {
             if (is_array($ArrayParams["SupportMemberID"]))
             {
                 $Where .= " AND sm.SupportMemberID IN ".constructSQLINString($ArrayParams["SupportMemberID"]);
             }
             else
             {
                 $Where .= " AND sm.SupportMemberID = ".$ArrayParams["SupportMemberID"];
             }
         }

         // <<< SupportMemberLastname field >>>
         if ((array_key_exists("SupportMemberLastname", $ArrayParams)) && (!empty($ArrayParams["SupportMemberLastname"])))
         {
             $Where .= " AND sm.SupportMemberLastname LIKE \"".$ArrayParams["SupportMemberLastname"]."\"";
         }

         // <<< Option : get activated documents approvals >>>
         if (array_key_exists("Activated", $ArrayParams))
         {
             if ((array_key_exists("SchoolYear", $ArrayParams)) && (count($ArrayParams["SchoolYear"]) > 0))
             {
                 // We search documents approvals activated for a given school year
                 $SchoolYearStartDate = $GLOBALS['CONF_SCHOOL_YEAR_START_DATES'][$ArrayParams["SchoolYear"][0]];
                 $SchoolYearEndDate = date('Y-m-05',
                                           strtotime($ArrayParams["SchoolYear"][0].'-'.$GLOBALS['CONF_SCHOOL_YEAR_LAST_MONTH'].'-01'));

                 $Where .= " AND (((da.DocumentApprovalDate <= \"$SchoolYearStartDate\") OR (da.DocumentApprovalDate BETWEEN \"$SchoolYearStartDate\" AND \"$SchoolYearEndDate\"))"
                          ." AND (da.DocumentApprovalDate <= \"$SchoolYearEndDate\"))";
             }
         }
     }

     // We can launch the SQL request
     $DbResult = $DbConnection->query("$Select $From $Where GROUP BY DocumentApprovalID $Having");

     if (!DB::isError($DbResult))
     {
         return $DbResult->numRows();
     }

     // ERROR
     return 0;
 }


/**
 * Delete a document approval (and linked families' approvals), thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2019-05-07
 *
 * @param $DbConnection              DB object    Object of the opened database connection
 * @param $DocumentApprovalID        Integer      ID of the document approval to delete [1..n]
 *
 * @return Boolean                   TRUE if the document approval is deleted if it exists,
 *                                   FALSE otherwise
 */
 function dbDeleteDocumentApproval($DbConnection, $DocumentApprovalID)
 {
     // The parameters are correct?
     if ($DocumentApprovalID > 0)
     {
         // First, delete families' approvals
         $DbResult = $DbConnection->query("DELETE FROM DocumentsFamiliesApprovals WHERE DocumentApprovalID = $DocumentApprovalID");
         if (!DB::isError($DbResult))
         {
             // Next, we delete the document approval
             $DbResult = $DbConnection->query("DELETE FROM DocumentsApprovals WHERE DocumentApprovalID = $DocumentApprovalID");
             if (!DB::isError($DbResult))
             {
                 // Document approval deleted
                 return TRUE;
             }
         }
     }

     // ERROR
     return FALSE;
 }


/**
 * Check if a family approval for a documet approval exists in the DocumentsFamiliesApprovals table, thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2019-05-07
 *
 * @param $DbConnection                DB object    Object of the opened database connection
 * @param $DocumentFamilyApprovalID    Integer      ID of the family approval searched [1..n]
 *
 * @return Boolean                     TRUE if the family approval for a document approval exists, FALSE otherwise
 */
 function isExistingDocumentFamilyApproval($DbConnection, $DocumentFamilyApprovalID)
 {
     if ($DocumentFamilyApprovalID > 0)
     {
         $DbResult = $DbConnection->query("SELECT DocumentFamilyApprovalID FROM DocumentsFamiliesApprovals
                                           WHERE DocumentFamilyApprovalID = $DocumentFamilyApprovalID");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 1)
             {
                 // The entry exists
                 return TRUE;
             }
         }
     }

     // The entry doesn't exist
     return FALSE;
 }


/**
 * Add a family approval of a document approval in the DocumentsFamiliesApprovals table
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2019-05-07
 *
 * @param $DbConnection                     DB object    Object of the opened database connection
 * @param $DocumentApprovalID               Integer      ID of the document approval concerned by the family approval [1..n]
 * @param $SupportMemberID                  Integer      ID of the support member who approves the document [1..n]
 * @param $DocumentFamilyApprovalDate       Datetime     Date of the family approval of the document approval (yyyy-mm-dd hh:mm:ss)
 * @param $DocumentFamilyApprovalComment    String       Comment of the family about the document approval
 *
 * @return Integer                          The primary key of the family approval for a document approval [1..n],
 *                                          0 otherwise
 */
 function dbAddDocumentFamilyApproval($DbConnection, $DocumentApprovalID, $SupportMemberID, $DocumentFamilyApprovalDate, $DocumentFamilyApprovalComment = '')
 {
     if (($DocumentApprovalID > 0) && ($SupportMemberID > 0))
     {
         // Check if the DocumentFamilyApprovalDate is valide
         if (preg_match("[\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d]", $DocumentFamilyApprovalDate) == 0)
         {
             return 0;
         }
         else
         {
             $DocumentFamilyApprovalDate = ", DocumentFamilyApprovalDate = \"$DocumentFamilyApprovalDate\"";
         }

         // Check if the family approval already exists for the document approval
         $DbResult = $DbConnection->query("SELECT DocumentFamilyApprovalID FROM DocumentsFamiliesApprovals WHERE SupportMemberID = $SupportMemberID
                                           AND DocumentApprovalID = $DocumentApprovalID");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 0)
             {
                 // It's a new entry
                 $id = getNewPrimaryKey($DbConnection, "DocumentsFamiliesApprovals", "DocumentFamilyApprovalID");
                 if ($id != 0)
                 {
                     $DbResult = $DbConnection->query("INSERT INTO DocumentsFamiliesApprovals SET DocumentFamilyApprovalID = $id, SupportMemberID = $SupportMemberID,
                                                      DocumentApprovalID = $DocumentApprovalID, DocumentFamilyApprovalComment = \"$DocumentFamilyApprovalComment\"
                                                      $DocumentFamilyApprovalDate");
                     if (!DB::isError($DbResult))
                     {
                         return $id;
                     }
                 }
             }
         }
     }

     // ERROR
     return 0;
 }


/**
 * Update an existing family approval of a document approval in the DocumentsFamiliesApprovals table
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2019-05-07
 *
 * @param $DbConnection                     DB object    Object of the opened database connection
 * @param $DocumentFamilyApprovalID         Integer      ID of the family approval to update [1..n]
 * @param $DocumentApprovalID               Integer      ID of the document approval concerned by the family approval [1..n]
 * @param $SupportMemberID                  Integer      ID of the support member who approves the document [1..n]
 * @param $DocumentFamilyApprovalDate       Datetime     Date of the family approval of the document approval (yyyy-mm-dd hh:mm:ss)
 * @param $DocumentFamilyApprovalComment    String       Comment of the family about the document approval
 *
 * @return Integer                          The primary key of the family approval of a document approval [1..n], 0 otherwise
 */
 function dbUpdateDocumentFamilyApproval($DbConnection, $DocumentFamilyApprovalID, $DocumentApprovalID, $SupportMemberID, $DocumentFamilyApprovalDate = NULL, $DocumentFamilyApprovalComment = NULL)
 {
     // The parameters which are NULL will be ignored for the update
     $ArrayParamsUpdate = array();

     // Verification of the parameters
     if (($DocumentFamilyApprovalID < 1) || (!isInteger($DocumentFamilyApprovalID)))
     {
         // ERROR
         return 0;
     }

     if (!is_null($DocumentApprovalID))
     {
         if (($DocumentApprovalID < 1) || (!isInteger($DocumentApprovalID)))
         {
             // ERROR
             return 0;
         }
         else
         {
             $ArrayParamsUpdate[] = "DocumentApprovalID = $DocumentApprovalID";
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

     // Check if the DocumentFamilyApprovalDate is valide
     if (!is_null($DocumentFamilyApprovalDate))
     {
         if (preg_match("[\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d]", $DocumentFamilyApprovalDate) == 0)
         {
             return 0;
         }
         else
         {
             // The DocumentFamilyApprovalDate field will be updated
             $ArrayParamsUpdate[] = "DocumentFamilyApprovalDate = \"$DocumentFamilyApprovalDate\"";
         }
     }

     if (!is_Null($DocumentFamilyApprovalComment))
     {
         // The DocumentFamilyApprovalComment field will be updated
         $ArrayParamsUpdate[] = "DocumentFamilyApprovalComment = \"$DocumentFamilyApprovalComment\"";
     }

     // Here, the parameters are correct, we check if the family approval already exists for the document approval
     if (isExistingDocumentFamilyApproval($DbConnection, $DocumentFamilyApprovalID))
     {
         // We check if the entry is unique
         $DbResult = $DbConnection->query("SELECT DocumentFamilyApprovalID FROM DocumentsFamiliesApprovals WHERE DocumentApprovalID = $DocumentApprovalID
                                           AND SupportMemberID = $SupportMemberID AND DocumentFamilyApprovalID <> $DocumentFamilyApprovalID");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() == 0)
             {
                 // The document approval exists and is unique : we can update if there is at least 1 parameter
                 if (count($ArrayParamsUpdate) > 0)
                 {
                     $DbResult = $DbConnection->query("UPDATE DocumentsFamiliesApprovals SET ".implode(", ", $ArrayParamsUpdate)
                                                      ." WHERE DocumentFamilyApprovalID = $DocumentFamilyApprovalID");
                     if (!DB::isError($DbResult))
                     {
                         // Family approval updated
                         return $DocumentFamilyApprovalID;
                     }
                 }
                 else
                 {
                     // The update isn't usefull
                     return $DocumentFamilyApprovalID;
                 }
             }
         }
     }

     // ERROR
     return 0;
 }


/**
 * Give the documents approvals of a family, thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2019-05-07
 *
 * @param $DbConnection              DB object    Object of the opened database connection
 * @param $FamilyID                  Integer      ID of the family for which we want the documents approvals [1..n]
 * @param $OrderBy                   String       To order the documents approvals
 *
 * @return Mixed array               All fields values of the documents approvals of the family if it exists,
 *                                   an empty array otherwise
 */
 function getDocumentsApprovalsOfFamily($DbConnection, $FamilyID, $OrderBy = 'DocumentFamilyApprovalDate DESC')
 {
     if ($FamilyID > 0)
     {
         if (empty($OrderBy))
         {
             $OrderBy = 'DocumentFamilyApprovalDate DESC';
         }

         // We get documents approvals of the family
         $DbResult = $DbConnection->query("SELECT dfa.DocumentFamilyApprovalID, dfa.DocumentFamilyApprovalDate, dfa.DocumentFamilyApprovalComment, da.DocumentApprovalID,
                                          da.DocumentApprovalDate, da.DocumentApprovalName, da.DocumentApprovalFile, da.DocumentApprovalType, sm.SupportMemberID,
                                          sm.SupportMemberLastname, sm.SupportMemberFirstname
                                          FROM DocumentsApprovals da INNER JOIN DocumentsFamiliesApprovals dfa ON (da.DocumentApprovalID = dfa.DocumentApprovalID)
                                          INNER JOIN SupportMembers sm ON (dfa.SupportMemberID = sm.SupportMemberID) INNER JOIN Families f ON (sm.FamilyID = f.FamilyID)
                                          WHERE f.FamilyID = $FamilyID ORDER BY $OrderBy");

         if (!DB::isError($DbResult))
         {
             // Creation of the result array
             $ArrayRecords = array(
                                  "DocumentFamilyApprovalID" => array(),
                                  "DocumentFamilyApprovalDate" => array(),
                                  "DocumentFamilyApprovalComment" => array(),
                                  "DocumentApprovalID" => array(),
                                  "DocumentApprovalDate" => array(),
                                  "DocumentApprovalName" => array(),
                                  "DocumentApprovalFile" => array(),
                                  "DocumentApprovalType" => array(),
                                  "SupportMemberID" => array(),
                                  "SupportMemberLastname" => array(),
                                  "SupportMemberFirstname" => array()
                                 );

             while($Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC))
             {
                 $ArrayRecords["DocumentFamilyApprovalID"][] = $Record["DocumentFamilyApprovalID"];
                 $ArrayRecords["DocumentFamilyApprovalDate"][] = $Record["DocumentFamilyApprovalDate"];
                 $ArrayRecords["DocumentFamilyApprovalComment"][] = $Record["DocumentFamilyApprovalComment"];
                 $ArrayRecords["DocumentApprovalID"][] = $Record["DocumentApprovalID"];
                 $ArrayRecords["DocumentApprovalDate"][] = $Record["DocumentApprovalDate"];
                 $ArrayRecords["DocumentApprovalName"][] = $Record["DocumentApprovalName"];
                 $ArrayRecords["DocumentApprovalFile"][] = $Record["DocumentApprovalFile"];
                 $ArrayRecords["DocumentApprovalType"][] = $Record["DocumentApprovalType"];
                 $ArrayRecords["SupportMemberID"][] = $Record["SupportMemberID"];
                 $ArrayRecords["SupportMemberLastname"][] = $Record["SupportMemberLastname"];
                 $ArrayRecords["SupportMemberFirstname"][] = $Record["SupportMemberFirstname"];
             }

             // Return result
             return $ArrayRecords;
         }
     }

     // ERROR
     return array();
 }


/**
 * Delete a document family approval, thanks to its ID
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2019-05-10
 *
 * @param $DbConnection                    DB object    Object of the opened database connection
 * @param $DocumentFamilyApprovalID        Integer      ID of the document family approval to delete [1..n]
 *
 * @return Boolean                         TRUE if the document family approval is deleted if it exists,
 *                                         FALSE otherwise
 */
 function dbDeleteDocumentFamilyApproval($DbConnection, $DocumentFamilyApprovalID)
 {
     // The parameters are correct?
     if ($DocumentFamilyApprovalID > 0)
     {
         // Delete the document family approval in the table
         $DbResult = $DbConnection->query("DELETE FROM DocumentsFamiliesApprovals WHERE DocumentFamilyApprovalID = $DocumentFamilyApprovalID");
         if (!DB::isError($DbResult))
         {
             // Document family approval deleted
             return TRUE;
         }
     }

     // ERROR
     return FALSE;
 }


/**
 * Treat documents to approve data for GDPR
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-05-07
 *
 * @param $DbConnection                 DB object            Object of the opened database connection
 * @param $AnonymizedSupportMemberID    Integer              ID of the support member used to anonymize data [1..n]
 * @param $ArrayParams                  Mixed array          Contains other parameters to use to apply
 *                                                           GDPR treatment
 * @param $ArrayConcernedRecords        Mixed array          Variable used to return concerned records to trad after
 *                                                           this function
 *
 * @return Boolean                      TRUE if GDPR treatment is done, FALSE otherwise
 */
 function dbDocumentsApprovalsGDPRTreatment($DbConnection, $AnonymizedSupportMemberID, $ArrayParams = array(), &$ArrayConcernedRecords = array())
 {
     if ($AnonymizedSupportMemberID > 0)
     {
         $bDocumentsDeleted = FALSE;
         $bLogsDeleted = FALSE;

         // First, we select concerned documents to approve
         $sDateCondition = " DocumentApprovalDate < ".date('Y-m-d', strtotime("5 years ago"));
         $sLogDateCondition = " AND LogEventDate < ".date('Y-m-d', strtotime("5 years ago"));
         if ((array_key_exists("DocumentApprovalDate", $ArrayParams)) && (count($ArrayParams["DocumentApprovalDate"]) == 2))
         {
             $sDateCondition = " DocumentApprovalDate ".$ArrayParams["DocumentApprovalDate"][0]
                               ." \"".$ArrayParams["DocumentApprovalDate"][1]."\"";

             $sLogDateCondition = " AND LogEventDate ".$ArrayParams["DocumentApprovalDate"][0]
                                  ." \"".$ArrayParams["DocumentApprovalDate"][1]."\"";
         }

         // We get the filename of each concerned document to approve too lod
         $DbResult = $DbConnection->query("SELECT DocumentApprovalFile FROM DocumentsApprovals WHERE $sDateCondition");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() >= 0)
             {
                 $ArrayConcernedRecords = array();
                 while($Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC))
                 {
                     $ArrayConcernedRecords[] = $Record['DocumentApprovalFile'];
                 }
             }
         }

         // Then, we delete the selected documents to approve too old
         $DbResult = $DbConnection->query("DELETE FROM DocumentsApprovals WHERE $sDateCondition");
         if (!DB::isError($DbResult))
         {
             // Document to approve deleted
             $bDocumentsDeleted = TRUE;
         }

         // Then, we delete event logs
         $DbResult = $DbConnection->query("DELETE FROM LogEvents WHERE LogEventItemType = \"".EVT_DOCUMENT_APPROVAL."\"
                                           AND LogEventService = \"".EVT_SERV_DOCUMENT_APPROVAL."\" $sLogDateCondition");
         if (!DB::isError($DbResult))
         {
             // Event logs about documents to approve deleted
             $bLogsDeleted = TRUE;
         }

         return (($bDocumentsDeleted) && ($bLogsDeleted));
     }

     // ERROR
     return FALSE;
 }


/**
 * Treat approvals of documents by families data for GDPR
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-05-07
 *
 * @param $DbConnection                 DB object      Object of the opened database connection
 * @param $AnonymizedSupportMemberID    Integer        ID of the support member used to anonymize data [1..n]
 * @param $ArrayParams                  Mixed array    Contains other parameters to use to apply
 *                                                     GDPR treatment
 *
 * @return Boolean                      TRUE if GDPR treatment is done, FALSE otherwise
 */
 function dbDocumentsFamiliesApprovalsGDPRTreatment($DbConnection, $AnonymizedSupportMemberID, $ArrayParams = array())
 {
     if ($AnonymizedSupportMemberID > 0)
     {
         $ArrayConcernedID = array();
         $bStatsDone = FALSE;
         $bApprovalsDeleted = FALSE;
         $bLogsDeleted = FALSE;

         // First, we store in the Stats table the too old approvals of documents by desactivated families
         $sDateCondition = " AND f.FamilyDesactivationDate IS NOT NULL AND f.FamilyDesactivationDate < ".date('Y-m-d', strtotime("5 years ago"));
         if ((array_key_exists("FamilyDesactivationDate", $ArrayParams)) && (count($ArrayParams["FamilyDesactivationDate"]) == 2))
         {
             $sDateCondition = " AND f.FamilyDesactivationDate IS NOT NULL AND f.FamilyDesactivationDate "
                               .$ArrayParams["FamilyDesactivationDate"][0]." \"".$ArrayParams["FamilyDesactivationDate"][1]."\"";
         }

         $sSupporterCondition = '';
         if ((array_key_exists("SupportMemberID", $ArrayParams)) && (!empty($ArrayParams["SupportMemberID"])))
         {
             // To limit to some given supporters
             if (is_array($ArrayParams["SupportMemberID"]))
             {
                 $sSupporterCondition = " AND sm.SupportMemberID IN ".constructSQLINString($ArrayParams["SupportMemberID"]);
             }
             else
             {
                 $sSupporterCondition = " AND sm.SupportMemberID = ".$ArrayParams["SupportMemberID"];
             }
         }

         $DbResult = $DbConnection->query("SELECT DATE_FORMAT(da.DocumentApprovalDate, '%Y-%m') AS StatPeriod,
                                           '".STAT_TYPE_NB_DOC_APPROVALS."' AS StatType,
                                           CONCAT(da.DocumentApprovalID, '#', da.DocumentApprovalType) AS StatSubType,
                                           COUNT(dfa.DocumentFamilyApprovalID) AS StatValue
                                           FROM Families f, SupportMembers sm, DocumentsApprovals da, DocumentsFamiliesApprovals dfa
                                           WHERE f.FamilyID = sm.FamilyID AND sm.SupportMemberID = dfa.SupportMemberID
                                           AND da.DocumentApprovalID = dfa.DocumentApprovalID $sDateCondition $sSupporterCondition
                                           GROUP BY StatPeriod, StatType, StatSubType
                                           ORDER BY StatPeriod");

         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() >= 0)
             {
                 while($Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC))
                 {
                     dbUpdateStat($DbConnection, $Record['StatPeriod'], $Record['StatType'], $Record['StatValue'],
                                  $Record['StatSubType']);
                 }

                 $bStatsDone = TRUE;
             }
         }

         // Then, we get ID of concerned approvals of documents
         $DbResult = $DbConnection->query("SELECT dfa.DocumentFamilyApprovalID
                                           FROM Families f, SupportMembers sm, DocumentsFamiliesApprovals dfa
                                           WHERE f.FamilyID = sm.FamilyID AND sm.SupportMemberID = dfa.SupportMemberID
                                           $sDateCondition $sSupporterCondition");
         if (!DB::isError($DbResult))
         {
             if ($DbResult->numRows() >= 0)
             {
                 while($Record = $DbResult->fetchRow(DB_FETCHMODE_ASSOC))
                 {
                     $ArrayConcernedID[] = $Record['DocumentFamilyApprovalID'];
                 }
             }
         }

         if (empty($ArrayConcernedID))
         {
             $bApprovalsDeleted = TRUE;
             $bLogsDeleted = TRUE;
         }
         else
         {
             // Now, we delete the selected approval of documents by families too old
             $DbResult = $DbConnection->query("DELETE FROM DocumentsFamiliesApprovals
                                               WHERE DocumentFamilyApprovalID IN ".constructSQLINString($ArrayConcernedID));
             if (!DB::isError($DbResult))
             {
                 // Approvals deleted
                 $bApprovalsDeleted = TRUE;
             }

             // Then, we delete event logs
             $DbResult = $DbConnection->query("DELETE FROM LogEvents WHERE LogEventItemType = \"".EVT_DOCUMENT_APPROVAL."\"
                                               AND LogEventService = \"".EVT_SERV_DOCUMENT_FAMILY_APPROVAL."\"
                                               AND LogEventItemID IN ".constructSQLINString($ArrayConcernedID));
             if (!DB::isError($DbResult))
             {
                 // Event logs about approvals of documents by families deleted
                 $bLogsDeleted = TRUE;
             }
         }

         return (($bStatsDone) && ($bApprovalsDeleted) && ($bLogsDeleted));
     }

     // ERROR
     return FALSE;
 }
?>