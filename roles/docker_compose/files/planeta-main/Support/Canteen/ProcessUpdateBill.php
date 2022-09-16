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
 * Support module : process the update of a bill. The supporter must be logged.
 *
 * @author Christophe Javouhey
 * @version 3.8
 * @since 2022-06-07
 */

 // Include the graphic primitives library
  require '../../GUI/GraphicInterface.php';

 // To measure the execution script time
 initStartTime();

 // Create "supporter" session or use the opened "supporter" session
 session_start();

 // Redirect the user to the login page index.php if he isn't loggued
 setRedirectionToLoginPage();

 // To take into account the crypted and no-crypted bill ID
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
 if (!empty($_POST["bSubmit"]))
 {
     if (isSet($_SESSION["SupportMemberID"]))
     {
         // Connection to the database
         $DbCon = dbConnection();

         $ContinueProcess = TRUE; // used to check that the parameters are correct

         // We identify the bill
         if (isExistingBill($DbCon, $Id))
         {
             // The bill exists
             $BillID = $Id;

             // Get infos about the bill
             $BillRecord = getTableRecordInfos($DbCon, "Bills", $BillID);
             $BillForDate = $BillRecord['BillForDate'];
             $FamilyID = $BillRecord['FamilyID'];
             $fOldBillAmount = getBillAmount($DbCon, $BillID, WITHOUT_PREVIOUS_BALANCE);
         }
         else
         {
             // ERROR : the bill doesn't exist
             $ContinueProcess = FALSE;
         }

         // We get the values entered by the user
         $fBillMonthlyContribution = (float)trim(strip_tags($_POST["fBillMonthlyContribution"]));
         if (empty($fBillMonthlyContribution))
         {
             $fBillMonthlyContribution = 0.00;
         }
         elseif ($fBillMonthlyContribution < 0.0)
         {
             $ContinueProcess = FALSE;
         }

         // Nb of canteen registrations + amount
         $iBillNbCanteenRegistrations = trim(strip_tags($_POST["sBillNbCanteenRegistrations"]));
         if (empty($iBillNbCanteenRegistrations))
         {
             $iBillNbCanteenRegistrations = 0;
         }
         elseif ($iBillNbCanteenRegistrations < 0)
         {
             $ContinueProcess = FALSE;
         }

         $fBillCanteenAmount = (float)trim(strip_tags($_POST["fBillCanteenAmount"]));
         if (empty($fBillCanteenAmount))
         {
             $fBillCanteenAmount = 0.00;
         }
         elseif ($fBillCanteenAmount < 0.0)
         {
             $ContinueProcess = FALSE;
         }

         $fBillWithoutMealAmount = (float)trim(strip_tags($_POST["fBillWithoutMealAmount"]));
         if (empty($fBillWithoutMealAmount))
         {
             $fBillWithoutMealAmount = 0.00;
         }
         elseif ($fBillWithoutMealAmount < 0.0)
         {
             $ContinueProcess = FALSE;
         }

         // Nb of nursery registrations + amount
         $iBillNbNurseryRegistrations = trim(strip_tags($_POST["sBillNbNurseryRegistrations"]));
         if (empty($iBillNbNurseryRegistrations))
         {
             $iBillNbNurseryRegistrations = 0;
         }
         elseif ($iBillNbNurseryRegistrations < 0)
         {
             $ContinueProcess = FALSE;
         }

         $fBillNurseryAmount = (float)trim(strip_tags($_POST["fBillNurseryAmount"]));
         if (empty($fBillNurseryAmount))
         {
             $fBillNurseryAmount = 0.00;
         }
         elseif ($fBillNurseryAmount < 0.0)
         {
             $ContinueProcess = FALSE;
         }

         // Other fee
         $fBillOtherAmount = (float)trim(strip_tags($_POST["fBillOtherAmount"]));
         if (empty($fBillOtherAmount))
         {
             $fBillOtherAmount = 0.00;
         }

         // Verification that the parameters are correct
         if ($ContinueProcess)
         {
             $BillID = dbUpdateBill($DbCon, $BillID, NULL, $BillForDate, $FamilyID, NULL, NULL, $fBillMonthlyContribution, $fBillCanteenAmount,
                                    $fBillWithoutMealAmount, $fBillNurseryAmount, NULL, NULL, $iBillNbCanteenRegistrations, $iBillNbNurseryRegistrations,
                                    $fBillOtherAmount);
             if ($BillID != 0)
             {
                 $fNewBillAmount = getBillAmount($DbCon, $BillID, WITHOUT_PREVIOUS_BALANCE);
                 if ($fOldBillAmount != $fNewBillAmount)
                 {
                     // Old bill amount and new amount are different
                     $fUpdateBillAmount = $fOldBillAmount - $fNewBillAmount;

                     // We update the family balance
                     $fNewBalance = updateFamilyBalance($DbCon, $FamilyID, $fUpdateBillAmount);
                 }

                 // Log event
                 logEvent($DbCon, EVT_BILL, EVT_SERV_BILL, EVT_ACT_UPDATE, $_SESSION['SupportMemberID'], $BillID);

                 // The bill is updated
                 $ConfirmationCaption = $LANG_CONFIRMATION;
                 $ConfirmationSentence = $LANG_CONFIRM_BILL_UPDATED;
                 $ConfirmationStyle = "ConfirmationMsg";
                 $UrlParameters = "Cr=".md5($BillID)."&Id=$BillID"; // For the redirection
             }
             else
             {
                 // The bill can't be updated
                 $ConfirmationCaption = $LANG_ERROR;
                 $ConfirmationSentence = $LANG_ERROR_UPDATE_BILL;
                 $ConfirmationStyle = "ErrorMsg";
                 $UrlParameters = $QUERY_STRING; // For the redirection
             }
         }
         else
         {
             // Errors
             $ConfirmationCaption = $LANG_ERROR;

             // ERROR : some parameters are empty strings
             $ConfirmationSentence = $LANG_ERROR_WRONG_FIELDS;

             $ConfirmationStyle = "ErrorMsg";
             $UrlParameters = $QUERY_STRING; // For the redirection
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
         $UrlParameters = $QUERY_STRING; // For the redirection
     }
 }
 else
 {
     // The supporter doesn't come from the UpdateBill.php page
     $ConfirmationCaption = $LANG_ERROR;
     $ConfirmationSentence = $LANG_ERROR_COME_FORM_PAGE;
     $ConfirmationStyle = "ErrorMsg";
     $UrlParameters = $QUERY_STRING; // For the redirection
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
                      "Redirection('".$CONF_ROOT_DIRECTORY."Support/Canteen/UpdateBill.php?$UrlParameters', $CONF_TIME_LAG)"
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