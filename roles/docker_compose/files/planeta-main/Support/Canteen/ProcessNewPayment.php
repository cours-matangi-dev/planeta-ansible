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
 * Support module : process the creation of a new payment, for a bill or not.
 * The supporter must be logged.
 *
 * @author Christophe Javouhey
 * @version 3.8
 *     - 2013-01-25 : check if a payment is unique for a family and a bank and taken into account
 *                    the new field "PaymentReceiptDate"
 *     - 2016-10-12 : taken into account to enter manualy part amount of a payment allocated to a bill
 *                    ($CONF_PAYMENTS_MANUAL_BILL_PART_AMOUNT) and load some configuration variables
 *                    from database
 *     - 2019-07-16 : v3.3. Taken into account the FamilyAnnualContributionBalance field to split payments for bills
 *                    and payments for annual contributions
 *     - 2021-11-29 : v3.7. Taken into account $CONF_PAYMENTS_MODES_BANK_REQUIRED before to use isUniquePayment() function
 *     - 2022-06-07 : v3.8. Taken into account BillOtherAmount field
 *
 * @since 2012-01-27
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
     if (isSet($_SESSION["SupportMemberID"]))
     {
         // Connection to the database
         $DbCon = dbConnection();

         // Load all configuration variables from database
         loadDbConfigParameters($DbCon, array('CONF_SCHOOL_YEAR_START_DATES',
                                              'CONF_CLASSROOMS',
                                              'CONF_CONTRIBUTIONS_ANNUAL_AMOUNTS',
                                              'CONF_CONTRIBUTIONS_MONTHLY_AMOUNTS',
                                              'CONF_CANTEEN_PRICES',
                                              'CONF_NURSERY_PRICES',
                                              'CONF_NURSERY_DELAYS_PRICES'));

         $ContinueProcess = TRUE; // used to check that the parameters are correct

         // We get the ID of the family of the payment
         $FamilyID = $_POST["hidFamilyID"];
         if ($FamilyID == 0)
         {
             // Error
             $ContinueProcess = FALSE;
         }

         // If the new payment is linked to a bill
         $BillID = existedPOSTFieldValue("hidBillID", NULL);
         if (!is_null($BillID))
         {
             // BillID can be an array (payment for several bills)
             $BillID = explode(',', $BillID);
             if (count($BillID) == 1)
             {
                 $BillID = $BillID[0];
             }
         }

         // We get the values entered by the user
         $PaymentType = strip_tags($_POST["lPaymentType"]);
         $PaymentMode = strip_tags($_POST["lPaymentMode"]);
         $BankID = strip_tags($_POST["lBankID"]);

         $sCheckNb = trim(strip_tags($_POST["sCheckNb"]));
         if (in_array($PaymentMode, $CONF_PAYMENTS_MODES_BANK_REQUIRED))
         {
             if (($BankID <= 0) || (empty($sCheckNb)))
             {
                 $ContinueProcess = FALSE;
             }
         }

         $fAmount = (float)trim(strip_tags($_POST["fAmount"]));
         if ((empty($fAmount)) || ($fAmount <= 0.0))
         {
             $ContinueProcess = FALSE;
         }

         // We have to convert the payment receipt date in english format (format used in the database)
         $PaymentReceiptDate = nullFormatText(formatedDate2EngDate($_POST["paymentDate"]), "NULL");
         if (empty($PaymentReceiptDate))
         {
             $ContinueProcess = FALSE;
         }

         $ManualBillPartAmount = NULL;
         if ((!empty($BillID)) && ($CONF_PAYMENTS_MANUAL_BILL_PART_AMOUNT))
         {
             // Get the part amount of the payment to allocate to the selected bill
             if (is_array($BillID))
             {
                 // There is several linked bills
                 $CurrentAllocatedAmount = 0.0;
                 foreach($_POST as $Fname => $CurrentFieldValue)
                 {
                     if (stripos($Fname, "fBillPartAmount") !== FALSE)
                     {
                         $ArrayTmp = explode('_', $Fname);  // Fieldname_BillID
                         $TmpBillID = $ArrayTmp[1];
                         unset($ArrayTmp);

                         $ManualBillPartAmount[$TmpBillID] = (float)trim(strip_tags($_POST[$Fname]));

                         // Get infos about the bill
                         $RecordBill = getTableRecordInfos($DbCon, "Bills", $TmpBillID);
                         $fBillAmountToPay = 0.00;
                         if (!empty($RecordBill))
                         {
                             $fBillAmount = $RecordBill['BillMonthlyContribution'] + $RecordBill['BillCanteenAmount']
                                            + $RecordBill['BillWithoutMealAmount'] + $RecordBill['BillNurseryAmount']
                                            - $RecordBill['BillDeposit'] + $RecordBill['BillOtherAmount'];

                             $fBillAmountToPay = $fBillAmount - $RecordBill['BillPaidAmount'];
                         }

                         unset($RecordBill);

                         $CurrentAllocatedAmount += $ManualBillPartAmount[$TmpBillID];

                         // Part amount can't be empty or = 0.0, can't be > amount to pay on the bill,
                         // can't be > total amount of the payment
                         if ((empty($ManualBillPartAmount[$TmpBillID])) || ($ManualBillPartAmount[$TmpBillID] <= 0.0)
                              || ($ManualBillPartAmount[$TmpBillID] > $fBillAmountToPay) || ($CurrentAllocatedAmount > $fAmount))
                         {
                             $ContinueProcess = FALSE;
                         }
                     }
                 }
             }
             else
             {
                 // There is just one linked bill
                 $RecordBill = getTableRecordInfos($DbCon, "Bills", $BillID);
                 $fBillAmountToPay = 0.00;
                 if (!empty($RecordBill))
                 {
                     $fBillAmount = $RecordBill['BillMonthlyContribution'] + $RecordBill['BillCanteenAmount']
                                    + $RecordBill['BillWithoutMealAmount'] + $RecordBill['BillNurseryAmount']
                                    - $RecordBill['BillDeposit'] + $RecordBill['BillOtherAmount'];

                     $fBillAmountToPay = $fBillAmount - $RecordBill['BillPaidAmount'];
                 }

                 foreach($_POST as $Fname => $CurrentFieldValue)
                 {
                     if (stripos($Fname, "fBillPartAmount") !== FALSE)
                     {
                         $ManualBillPartAmount = (float)trim(strip_tags($_POST[$Fname]));

                         // If empty, auto-compute of the part amount allocated to the bill
                         if (!empty($ManualBillPartAmount))
                         {
                             if (($ManualBillPartAmount <= 0.0) || ($ManualBillPartAmount > $fAmount)
                                 || ($ManualBillPartAmount > $fBillAmountToPay))
                             {
                                 $ContinueProcess = FALSE;
                             }
                         }
                         else
                         {
                             // No entered value : NULL (for dbSetBillsPaid())
                             $ManualBillPartAmount = NULL;
                         }

                         break;
                     }
                 }

                 unset($RecordBill);
             }
         }

         // Verification that the parameters are correct
         if ($ContinueProcess)
         {
             // Check if the payment is unique
             if ((!in_array($PaymentMode, $CONF_PAYMENTS_MODES_BANK_REQUIRED)) || (isUniquePayment($DbCon, $FamilyID, $BankID, $sCheckNb)))
             {
                 $PaymentID = dbAddPayment($DbCon, date('Y-m-d H:i:s'), $PaymentReceiptDate, $FamilyID, $fAmount, $PaymentType,
                                           $PaymentMode, $sCheckNb, $BankID, $BillID, $ManualBillPartAmount);

                 if ($PaymentID != 0)
                 {
                     // Log event
                     logEvent($DbCon, EVT_PAYMENT, EVT_SERV_PAYMENT, EVT_ACT_ADD, $_SESSION['SupportMemberID'], $PaymentID);

                     // Change the balance of the family
                     switch($PaymentType)
                     {
                         case 0:
                             // Payment for annual contribution
                             $fNewBalance = updateFamilyAnnualContributionBalance($DbCon, $FamilyID, $fAmount);
                             break;

                         case 1:
                         default:
                             $fNewBalance = updateFamilyBalance($DbCon, $FamilyID, $fAmount);
                             break;
                     }

                     // Set the "Paid" flag of the bill(s) to 1 if the payment is for a bill
                     if (!empty($BillID))
                     {
                         dbSetBillsPaid($DbCon, $PaymentID, 0.00, $ManualBillPartAmount);
                     }

                     // The payment is added
                     $ConfirmationCaption = $LANG_CONFIRMATION;
                     $ConfirmationSentence = $LANG_CONFIRM_PAYMENT_ADDED;
                     $ConfirmationStyle = "ConfirmationMsg";
                     $UrlParameters = "UpdatePayment.php?Cr=".md5($PaymentID)."&Id=$PaymentID"; // For the redirection
                 }
                 else
                 {
                     // The payment can't be added
                     $ConfirmationCaption = $LANG_ERROR;
                     $ConfirmationSentence = $LANG_ERROR_ADD_PAYMENT;
                     $ConfirmationStyle = "ErrorMsg";
                     $UrlParameters = "AddPayment.php?".$QUERY_STRING; // For the redirection
                 }
             }
             else
             {
                 // The payment can't be added because it already exists (not unique)
                 $ConfirmationCaption = $LANG_ERROR;
                 $ConfirmationSentence = $LANG_ERROR_PAYMENT_NOT_UNIQUE;
                 $ConfirmationStyle = "ErrorMsg";
                 $UrlParameters = "AddPayment.php?".$QUERY_STRING; // For the redirection
             }
         }
         else
         {
             // Errors
             $ConfirmationCaption = $LANG_ERROR;

             if (empty($PaymentReceiptDate))
             {
                 // The date is empty
                 $ConfirmationSentence = $LANG_ERROR_PAYMENT_DATE;
             }
             elseif ((empty($fAmount)) || ($fAmount <= 0))
             {
                 // Wrong amount
                 $ConfirmationSentence = $LANG_ERROR_PAYMENT_AMOUNT;
             }
             elseif ((in_array($PaymentMode, $CONF_PAYMENTS_MODES_BANK_REQUIRED)) && ($BankID <= 0))
             {
                 // No selected bank
                 $ConfirmationSentence = $LANG_ERROR_PAYMENT_BANK;
             }
             elseif ((in_array($PaymentMode, $CONF_PAYMENTS_MODES_BANK_REQUIRED)) && (empty($sCheckNb)))
             {
                 // No check number
                 $ConfirmationSentence = $LANG_ERROR_PAYMENT_CHECK_NB;
             }
             elseif ($CONF_PAYMENTS_MANUAL_BILL_PART_AMOUNT)
             {
                 // There is a pb with the reparition of the part amounts of a payment linked to bills
                 $ConfirmationSentence = $LANG_ERROR_PAYMENT_BILLS_PART_AMOUNTS;
             }
             else
             {
                 // ERROR : some parameters are empty strings
                 $ConfirmationSentence = $LANG_ERROR_WRONG_FIELDS;
             }

             $ConfirmationStyle = "ErrorMsg";
             $UrlParameters = "AddPayment.php?".$QUERY_STRING; // For the redirection
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
         $UrlParameters = "AddPayment.php?".$QUERY_STRING; // For the redirection
     }
 }
 else
 {
     // The supporter doesn't come from the AddPayment.php page
     $ConfirmationCaption = $LANG_ERROR;
     $ConfirmationSentence = $LANG_ERROR_COME_FORM_PAGE;
     $ConfirmationStyle = "ErrorMsg";
     $UrlParameters = "AddPayment.php?".$QUERY_STRING; // For the redirection
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
                      "Redirection('".$CONF_ROOT_DIRECTORY."Support/Canteen/$UrlParameters', $CONF_TIME_LAG)"
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