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
 * Support module : allow a supporter to update a document approval. The supporter must be logged
 * to update the document.
 *
 * @author Christophe Javouhey
 * @version 3.3
 * @since 2019-05-07
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

 // Load all configuration variables from database
 loadDbConfigParameters($DbCon, array('CONF_SCHOOL_YEAR_START_DATES'));

 // To take into account the crypted and no-crypted document approval ID
 // Crypted ID
 if (!empty($_GET["Cr"]))
 {
     $CryptedID = (string)strip_tags($_GET["Cr"]);
 }
 else
 {
     $CryptedID = '';
 }

 // No-crypted ID
 if (!empty($_GET["Id"]))
 {
     $Id = (string)strip_tags($_GET["Id"]);
 }
 else
 {
     $Id = '';
 }

 initGraphicInterface(
                      $LANG_INTRANET_NAME,
                      array(
                            '../../GUI/Styles/styles.css' => 'screen',
                            '../Styles_Support.css' => 'screen'
                           ),
                      array(
                            '../Verifications.js'
                           ),
                      'WhitePage'
                     );

 // Content of the web page
 openArea('id="content"');

 // The ID and the md5 crypted ID must be equal
 if (md5($Id) == $CryptedID)
 {
      displayDetailsDocumentApprovalForm($DbCon, $Id, "ProcessUpdateDocumentApproval.php", $CONF_ACCESS_APPL_PAGES[FCT_DOCUMENT_APPROVAL]);

      // Display hyperlink to return on the list of documents approvals
      displayBR(2);
      openParagraph('InfoMsg');
      displayStyledLinkText($LANG_CONTEXTUAL_MENU_SUPPORT_CANTEEN_DOCUMENTS_APPROVALS_LIST, 'DocumentsApprovalsList.php', '',
                            $LANG_CONTEXTUAL_MENU_SUPPORT_CANTEEN_DOCUMENTS_APPROVALS_LIST_TIP);
      closeParagraph();
 }
 else
 {
     openFrame($LANG_ERROR);
     displayStyledText($LANG_ERROR_NOT_VIEW_DOCUMENT_APPROVAL, 'ErrorMsg');
     closeFrame();
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

 // Close the <div> "content"
 closeArea();

 closeGraphicInterface();
?>