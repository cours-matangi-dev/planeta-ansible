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
 * Interface module : XHTML Graphic high level install forms library
 *
 * @author Christophe Javouhey
 * @version 3.8
 * @since 2022-06-27
 */


/**
 * Display the "next" button to start the install in the current row of the table of the web page,
 * in the graphic interface in XHTML
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2022-06-27
 */
 function displayStartInstallStepForm()
 {
     // Open the form
     openForm('FormInstall', 'post', 'index.php', '', "");
     displayBR(1);

     // Display the buttons
     echo "<table class=\"validation\">\n<tr>\n\t<td>";
     insertInputField('bCreateDBConStep', 'submit', '', '', $GLOBALS['LANG_INSTALL_NEXT_STEP_BUTTON_TIP'], $GLOBALS['LANG_INSTALL_NEXT_STEP_BUTTON_CAPTION']);
     echo "</td>\n</tr>\n</table>\n";
     closeForm();
 }


/**
 * Display the form to create the DB connexion in the current row of the table of the web page,
 * in the graphic interface in XHTML
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2022-06-30
 */
 function displayCreateDBConnexionStepResultForm()
 {
     openForm('FormInstall', 'post', 'index.php', '', "VerificationCreateDBConnexion('".$GLOBALS['LANG_ERROR_JS_INSTALL_DB_SERVER']."', '".$GLOBALS['LANG_ERROR_JS_INSTALL_DB_USER']
                                                                                     ."', '".$GLOBALS['LANG_ERROR_JS_INSTALL_DB_PWD']."')");

     $sServerName = generateInputField("sServerName", "text", "50", "20", $GLOBALS["LANG_INSTALL_STEP_1_PAGE_DB_SERVER_TIP"], "localhost");
     $sUser = generateInputField("sUser", "text", "50", "20", $GLOBALS["LANG_INSTALL_STEP_1_PAGE_DB_USER_TIP"], "");
     $sPassword = generateInputField("sPassword", "text", "50", "20", $GLOBALS["LANG_INSTALL_STEP_1_PAGE_DB_PWD_TIP"], "");

     openStyledFrame($GLOBALS["LANG_INSTALL_STEP_1_PAGE_DB"], "Frame", "Frame", "DetailsNews");
     echo "<table id=\"InstallForm\" cellspacing=\"0\" cellpadding=\"0\">\n<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_INSTALL_STEP_1_PAGE_DB_SERVER"]."*</td><td class=\"Value\">$sServerName</td>\n</tr>\n";
     echo "<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_INSTALL_STEP_1_PAGE_DB_USER"]."*</td><td class=\"Value\">$sUser</td>\n</tr>\n";
     echo "<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_INSTALL_STEP_1_PAGE_DB_PWD"]."*</td><td class=\"Value\">$sPassword</td>\n</tr>\n";
     echo "</table>\n";

     closeStyledFrame();

     displayBR(1);

     // Display the buttons
     echo "<table class=\"validation\">\n<tr>\n\t<td>";
     insertInputField('bCheckStep', 'submit', '', '', $GLOBALS['LANG_INSTALL_NEXT_STEP_BUTTON_TIP'], $GLOBALS['LANG_INSTALL_NEXT_STEP_BUTTON_CAPTION']);
     echo "</td>\n</tr>\n</table>\n";
     closeForm();
 }


/**
 * Display the "next" button to go to the Check step in the current row of the table of the web page,
 * in the graphic interface in XHTML
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2022-07-01
 */
 function displayCheckStepForm()
 {
     // Open the temporary form
     openForm('FormInstall', 'post', 'index.php', '', "");
     displayBR(1);

     // Display the buttons
     echo "<table class=\"validation\">\n<tr>\n\t<td>";
     insertInputField('bCreateDBStep', 'submit', '', '', $GLOBALS['LANG_INSTALL_NEXT_STEP_BUTTON_TIP'], $GLOBALS['LANG_INSTALL_NEXT_STEP_BUTTON_CAPTION']);
     echo "</td>\n</tr>\n</table>\n";
     closeForm();
 }


/**
 * Display the result about the server configuration and the "next" button to go to the end step in the current row of the table of the web page,
 * in the graphic interface in XHTML
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2022-07-01
 *
 * @param $ArrayParams          Mixed Array        The checked parameters with the found value and the requested value
 */
 function displayCheckStepResultForm($ArrayParams)
 {
     openForm('FormInstall', 'post', 'index.php', '', "");

     $sPHPVersionResult = "<span class=\"ResultNOK\">".$GLOBALS['LANG_INSTALL_NOK']."</span>";
     $sDBConnectionResult = "<span class=\"ResultNOK\">".$GLOBALS['LANG_INSTALL_NOK']."</span>";
     $sMySQLVersionResult = "<span class=\"ResultNOK\">".$GLOBALS['LANG_INSTALL_NOK']."</span>";
     $sDirAccessResult = "";

     foreach($ArrayParams as $pName => $CurrentParamValue)
     {
         switch($pName)
         {
             case 'PHP':
                 // PHP version OK ?
                 if ($CurrentParamValue[0] >= $CurrentParamValue[1])
                 {
                     $sPHPVersionResult = "<span class=\"ResultOK\">".$GLOBALS['LANG_INSTALL_OK']."</span>";
                 }
                 break;

             case 'DB_SERVER':
                 // Connection to the databse server OK ?
                 if ($CurrentParamValue[0] == $CurrentParamValue[1])
                 {
                     $sDBConnectionResult = "<span class=\"ResultOK\">".$GLOBALS['LANG_INSTALL_OK']."</span>";
                 }
                 break;

             case 'MYSQL':
                 // MySQL version is OK ?
                 if ($CurrentParamValue[0] >= $CurrentParamValue[1])
                 {
                     $sMySQLVersionResult = "<span class=\"ResultOK\">".$GLOBALS['LANG_INSTALL_OK']."</span>";
                 }
                 break;

             case 'DIR_ACCESS':
                 // Directories are writable ?
                 foreach($ArrayParams['DIR_ACCESS'] as $DirName => $ArrayCurrValues)
                 {
                     if (!empty($sDirAccessResult))
                     {
                         $sDirAccessResult .= generateBR(1);
                     }

                     if ($ArrayCurrValues[0] == $ArrayCurrValues[1])
                     {
                         // Directory writable
                         $sDirAccessResult .= "$DirName : <span class=\"ResultOK\">".$GLOBALS['LANG_INSTALL_OK']."</span>";
                     }
                     else
                     {
                         // Directory not writable
                         $sDirAccessResult .= "$DirName : <span class=\"ResultNOK\">".$GLOBALS['LANG_INSTALL_NOK']."</span>";
                     }
                 }
                 break;
         }
     }

     openStyledFrame($GLOBALS["LANG_INSTALL_RESULT"], "Frame", "Frame", "DetailsNews");
     echo "<table id=\"InstallForm\" cellspacing=\"0\" cellpadding=\"0\">\n<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_INSTALL_STEP_2_PAGE_PHP_VERSION"]."</td><td class=\"Value\">$sPHPVersionResult</td>\n</tr>\n";
     echo "<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_INSTALL_STEP_2_PAGE_DB_CON"]."</td><td class=\"Value\">$sDBConnectionResult</td>\n</tr>\n";
     echo "<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_INSTALL_STEP_2_PAGE_MYSQL_VERSION"]."</td><td class=\"Value\">$sMySQLVersionResult</td>\n</tr>\n";
     echo "<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_INSTALL_STEP_2_PAGE_DIRECTORIES_ACCESS"]."</td><td class=\"Value\">$sDirAccessResult</td>\n</tr>\n";
     echo "</table>\n";
     closeStyledFrame();

     displayBR(1);

     // Display the buttons
     echo "<table class=\"validation\">\n<tr>\n\t<td>";
     insertInputField('bInstallEndStep', 'submit', '', '', $GLOBALS['LANG_INSTALL_NEXT_STEP_BUTTON_TIP'], $GLOBALS['LANG_INSTALL_NEXT_STEP_BUTTON_CAPTION']);
     echo "</td>\n</tr>\n</table>\n";
     closeForm();
 }


/**
 * Display the form to create the database with data in the current row of the table of the web page,
 * in the graphic interface in XHTML
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2022-07-07
 */
 function displayCreateDatabaseForm()
 {
     openForm('FormInstall', 'post', 'index.php', '', "VerificationCreateDB('".$GLOBALS['LANG_ERROR_JS_INSTALL_DB_NAME']."')");

     $sDBName = generateInputField("sDatabasename", "text", "50", "20", $GLOBALS["LANG_INSTALL_STEP_3_PAGE_DB_NAME_TIP"], "Planeta");

     openStyledFrame($GLOBALS["LANG_INSTALL_STEP_3_PAGE_CREATE_DB"], "Frame", "Frame", "DetailsNews");
     echo "<table id=\"InstallForm\" cellspacing=\"0\" cellpadding=\"0\">\n<tr>\n\t<td class=\"Label\">".$GLOBALS["LANG_INSTALL_STEP_3_PAGE_DB_NAME"]."*</td><td class=\"Value\">$sDBName</td>\n</tr>\n";
     echo "</table>\n";

     closeStyledFrame();

     displayBR(1);

     // Display the buttons
     echo "<table class=\"validation\">\n<tr>\n\t<td>";
     insertInputField('bSubmit', 'submit', '', '', $GLOBALS['LANG_INSTALL_NEXT_STEP_BUTTON_TIP'], $GLOBALS['LANG_INSTALL_NEXT_STEP_BUTTON_CAPTION']);
     echo "</td>\n</tr>\n</table>\n";
     closeForm();
 }


/**
 * Display the result about the database creation and the "next" button to go to users step in the current row
 * of the table of the web page, in the graphic interface in XHTML
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2022-07-04
 *
 * @param $ArrayParams          Mixed Array        The tables with the found value and the requested value
 */
 function displayCreateDatabaseStepResultForm($ArrayParams)
 {
     openForm('FormInstall', 'post', 'index.php', '', "");

     openStyledFrame($GLOBALS["LANG_INSTALL_RESULT"], "Frame", "Frame", "DetailsNews");
     echo "<table id=\"InstallForm\" cellspacing=\"0\" cellpadding=\"0\">\n";

     foreach($ArrayParams as $TableName => $CurrentParamValue)
     {
         echo "<tr>\n\t<td class=\"Label\">$TableName</td><td class=\"Value\">";
         if ($CurrentParamValue[0] >= $CurrentParamValue[1])
         {
             echo "<span class=\"ResultOK\">".$GLOBALS['LANG_INSTALL_OK']."</span>";
         }
         else
         {
             echo "<span class=\"ResultNOK\">".$GLOBALS['LANG_INSTALL_NOK']."</span>";
         }

         echo "</td>\n</tr>\n";
     }

     echo "</table>\n";
     closeStyledFrame();

     displayBR(1);

     // Display the buttons
     echo "<table class=\"validation\">\n<tr>\n\t<td>";
     insertInputField('bDisplayUsersStep', 'submit', '', '', $GLOBALS['LANG_INSTALL_NEXT_STEP_BUTTON_TIP'], $GLOBALS['LANG_INSTALL_NEXT_STEP_BUTTON_CAPTION']);
     echo "</td>\n</tr>\n</table>\n";
     closeForm();
 }


/**
 * Display the result about default users and the "next" button to go to the end step in the current row
 * of the table of the web page, in the graphic interface in XHTML
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2022-07-04
 *
 * @param $ArrayParams          Mixed Array        The support member states with the default login/password
 */
 function displayDefaultUsersStepResultForm($ArrayParams)
 {
     openForm('FormInstall', 'post', 'index.php', '', "");

     openStyledFrame($GLOBALS["LANG_INSTALL_RESULT"], "Frame", "Frame", "DetailsNews");
     echo "<table id=\"InstallForm\" cellspacing=\"0\" cellpadding=\"0\">\n";

     foreach($ArrayParams as $StateName => $CurrentParamValue)
     {
         echo "<tr>\n\t<td class=\"Label\">$StateName</td><td class=\"Value\">".$CurrentParamValue[0]." / ".$CurrentParamValue[1]."</td>\n</tr>\n";
     }

     echo "</table>\n";
     closeStyledFrame();

     displayBR(1);

     // Display the buttons
     echo "<table class=\"validation\">\n<tr>\n\t<td>";
     insertInputField('bRedirectStep', 'submit', '', '', $GLOBALS['LANG_INSTALL_NEXT_STEP_BUTTON_TIP'], $GLOBALS['LANG_INSTALL_NEXT_STEP_BUTTON_CAPTION']);
     echo "</td>\n</tr>\n</table>\n";
     closeForm();
 }
?>