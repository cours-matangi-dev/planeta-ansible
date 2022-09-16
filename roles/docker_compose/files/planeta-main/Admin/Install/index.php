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
 * Install module : Home page of the Install module
 *
 * @author Christophe Javouhey
 * @version 3.8
 * @since 2022-06-27
 */

 // Include the graphic primitives library
 require '../../GUI/GraphicInterface.php';

 // To measure the execution script time
 initStartTime();

 // Compute the root path of the application
 $sAppName = "CanteenCalandreta";
 $sRootInstallDir = dirname(__FILE__);
 $iPos = stripos($sRootInstallDir, $sAppName);
 if ($iPos !== FALSE)
 {
     $sRootInstallDir = substr($sRootInstallDir, 0, $iPos + strlen($sAppName) + 1);
 }

 //################################ FORM PROCESSING ##########################
 $iInstallationStep = 1;  // Start install
 $bContinue = TRUE;
 $ArrayResults = array(
                       'OK' => array(),
                       'Errors' => array()
                      );
 $ArrayParams = array();

 if (!empty($_POST["bCreateDBConStep"]))
 {
     //**************** Start installation ****************//
     $iInstallationStep = 2;
 }
 elseif (!empty($_POST["bCheckStep"]))
 {
     //**************** Init database connection ****************//
     // We get entered data
     $sServer = trim(strip_tags($_POST['sServerName']));
     if (empty($sServer))
     {
         // Error
         $bContinue = FALSE;
         $ArrayResults['Errors'][] = $LANG_ERROR_INSTALL_DB_SERVER;
     }

     $sUser = trim(strip_tags($_POST['sUser']));
     if (empty($sUser))
     {
         // Error
         $bContinue = FALSE;
         $ArrayResults['Errors'][] = $LANG_ERROR_INSTALL_DB_USER;
     }

     $sPassword = trim(strip_tags($_POST['sPassword']));
     if (empty($sPassword))
     {
         // Error
         $bContinue = FALSE;
         $ArrayResults['Errors'][] = $LANG_ERROR_INSTALL_DB_PWD;
     }

     if ($bContinue)
     {
         // We can create the /Common/ConfigDB.php file
         if (file_exists($sRootInstallDir."Common/ConfigDB.php"))
         {
             unlink($sRootInstallDir."Common/ConfigDB.php");
         }

         $sFileContent = "<?php\n";
         $sFileContent .= file_get_contents($sRootInstallDir."Admin/Install/gpl_header.txt");
         $sFileContent .= "\n\n/**\n * Database configuration file of Canteen Calandreta\n */\n";

         if (stripos($sServer, '$_SERVER') === FALSE)
         {
             $sFileContent .= ' $CONF_DB_SERVER                 = "'.$sServer."\";\n";
         }
         else
         {
             $sFileContent .= ' $CONF_DB_SERVER                 = '.$sServer.";\n";
         }

         $sFileContent .= ' $CONF_DB_USER                   = "'.$sUser."\";\n";
         $sFileContent .= ' $CONF_DB_PASSWORD               = "'.$sPassword."\";\n";
         $sFileContent .= ' $CONF_DB_DATABASE               = "{{DATABASE}}";    // Name of the database used by the application'."\n";
         $sFileContent .= ' $CONF_DB_PORT                   = "";'."\n";
         $sFileContent .= ' $CONF_DB_SGBD_TYPE              = "mysqli";    // Type of SGBD : mysql, pgsql, oci8, MSAccess, IBMDB2,...'."\n";
         $sFileContent .= ' $CONF_DB_SGBD_VERSION           = 5;    // Version of the sgbd : mysql 3, mysql 5...'."\n";
         $sFileContent .= ' $CONF_DB_PERSISTANCE_CONNECTION = FALSE;'."\n";
         $sFileContent .= "?>";

         $bResult = file_put_contents($sRootInstallDir."Common/ConfigDB.php", $sFileContent);
         if ($bResult === FALSE)
         {
             // Error : file non created
             $ArrayResults['Errors'][] = $LANG_ERROR_INSTALL_DB_CREATE_FILE;
             $bContinue = FALSE;
         }
         else
         {
             // Next step
             $iInstallationStep = 3;
         }

         unset($sFileContent);
     }
 }
 elseif (!empty($_POST["bCreateDBStep"]))
 {
     //**************** Check server configuration ****************//
     $iInstallationStep = 4;

     // We check the PHP version
     $ArrayPhpVersion = explode('.', phpversion());
     $ArrayParams['PHP'] = array($ArrayPhpVersion[0], 5);

     // We check the database server version
     $sDsn = $CONF_DB_SGBD_TYPE.'://'.$CONF_DB_USER.':'.$CONF_DB_PASSWORD.'@'.$CONF_DB_SERVER;

     $DbCon = DB::connect($sDsn, FALSE);
     if (DB::isError($DbCon))
     {
          $ArrayParams['DB_SERVER'] = array(0, 1);
          $ArrayParams['MYSQL'] = array(0, 5);

          $ArrayResults['Errors'][] = $LANG_ERROR_INSTALL_DB_NO_CONNECTION;
          $bContinue = FALSE;
     }
     else
     {
         $ArrayParams['DB_SERVER'] = array(1, 1);

         // Release the connection to the database
         dbDisconnection($DbCon);

         // We get the database server version
         $DbCon = mysqli_connect($CONF_DB_SERVER, $CONF_DB_USER, $CONF_DB_PASSWORD);
         if (!mysqli_connect_errno())
         {
             // MySQL version : xyyzz (x = major version)
             $ArrayMySQLVersion = str_split(strrev(mysqli_get_server_version($DbCon)), 2);
             $sMySQLVersion = strrev($ArrayMySQLVersion[count($ArrayMySQLVersion) - 1]);

             $ArrayParams['MYSQL'] = array($sMySQLVersion, 5);
             mysqli_close($DbCon);
         }
     }

     // We check if we write in some directories
     $ArrayParams['DIR_ACCESS'] = array(
                                        $sRootInstallDir.'Exports/' => array(0, 1),
                                        $sRootInstallDir.'Upload/' => array(0, 1)
                                       );
     foreach($ArrayParams['DIR_ACCESS'] as $DirName => $CurrentValue)
     {
         if (is_writable($DirName))
         {
             $ArrayParams['DIR_ACCESS'][$DirName][0] = 1;
         }
     }
 }
 elseif (!empty($_POST["bInstallEndStep"]))
 {
     //**************** Start to create the database with data ****************//
     $iInstallationStep = 5;
 }
 elseif (!empty($_POST["bSubmit"]))
 {
     //**************** Create the database with data ****************//
     $iInstallationStep = 6;

     // We get entered data
     $sDBName = trim(strip_tags($_POST['sDatabasename']));
     if (empty($sDBName))
     {
         // Error
         $bContinue = FALSE;
         $ArrayResults['Errors'][] = $LANG_ERROR_INSTALL_DB_NAME;
     }

     if (file_exists($sRootInstallDir."Common/ConfigDB.php"))
     {
         // We update the ConfigDB.php file with the entered database name
         $sFileContent = file_get_contents($sRootInstallDir."Common/ConfigDB.php");
         $sFileContent = str_replace(array("{{DATABASE}}"), array($sDBName), $sFileContent);
         $bResult = file_put_contents($sRootInstallDir."Common/ConfigDB.php", $sFileContent);
         if ($bResult === FALSE)
         {
             // File not updated
             $ArrayResults['Errors'][] = $LANG_ERROR_INSTALL_DB_UPDATE_FILE;
             $bContinue = FALSE;
         }

         unset($sFileContent);
     }
     else
     {
         // The file doesn't exist
         $ArrayResults['Errors'][] = $LANG_ERROR_INSTALL_DB_CONFIG_FILE;
         $bContinue = FALSE;
     }

     if ($bContinue)
     {
         // We read the SQL file to create all tables with data
         // Create the database
         $DbCon = mysqli_connect($CONF_DB_SERVER, $CONF_DB_USER, $CONF_DB_PASSWORD);
         if (!mysqli_connect_errno())
         {
             mysqli_query($DbCon, "DROP DATABASE `$sDBName`");
             mysqli_query($DbCon, "CREATE DATABASE `$sDBName`");

             if (mysqli_select_db($DbCon, $sDBName))
             {
                 $ArraySQLFile = file($sRootInstallDir."Admin/Install/create_db_with_data.sql");

                 // We create tables
                 $sSQL = "";
                 $bStartCreate = FALSE;
                 $bStartInsert = FALSE;
                 foreach($ArraySQLFile as $t => $CurrentSQLLine)
                 {
                     if (stripos($CurrentSQLLine, "CREATE TABLE") !== FALSE)
                     {
                         // Before, we check if we have data to insert into the previous table
                         if ($bStartInsert)
                         {
                             $bResult = mysqli_query($DbCon, trim($sSQL));
                             $bStartInsert = FALSE;
                         }

                         // We detect the table name to create
                         $sTableName = trim(str_replace(array("CREATE TABLE", " ", "`", "("), array("", "", "", ""), $CurrentSQLLine));
                         $ArrayParams[$sTableName] = array(0, 1);

                         $sSQL = $CurrentSQLLine;
                         $bStartCreate = TRUE;
                     }
                     elseif (($bStartCreate) && (stripos($CurrentSQLLine, ") ENGINE=MyISAM") !== FALSE))
                     {
                         $sSQL .= $CurrentSQLLine;
                         $bResult = mysqli_query($DbCon, trim($sSQL));
                         if ($bResult)
                         {
                             // Table created
                             $ArrayParams[$sTableName] = array(1, 1);
                         }

                         $sTableName = '';
                         $bStartCreate = FALSE;
                     }
                     elseif (stripos($CurrentSQLLine, "INSERT INTO") !== FALSE)
                     {
                         // Before, we check if we have data to insert into the previous or current table
                         if ($bStartInsert)
                         {
                             $bResult = mysqli_query($DbCon, trim($sSQL));
                             $bStartInsert = FALSE;
                         }

                         // We detect data to insert into the table
                         $sSQL = $CurrentSQLLine;
                         $bStartInsert = TRUE;
                     }
                     elseif ($bStartCreate)
                     {
                         // Go on to get SQL instructions to create the table
                         $sSQL .= $CurrentSQLLine;
                     }
                     elseif ($bStartInsert)
                     {
                         // Go on to get SQL instructions to insert data into the table
                         $sSQL .= $CurrentSQLLine;
                     }
                 }

                 unset($ArraySQLFile);
             }
             else
             {
                 // Error : no database
                 $ArrayResults['Errors'][] = $LANG_ERROR_INSTALL_DB_NO_DATABASE;
                 $bContinue = FALSE;
             }
         }
         else
         {
             // Error : no connection to the database server
             $ArrayResults['Errors'][] = $LANG_ERROR_INSTALL_DB_NO_CONNECTION;
             $bContinue = FALSE;
         }

         mysqli_close($DbCon);
     }
 }
 elseif (!empty($_POST["bDisplayUsersStep"]))
 {
     //**************** Display created users ****************//
     $iInstallationStep = 7;

     $DbCon = dbConnection();

     // We get support member states
     $ArraySMStates = getAllSupportMembersStatesInfos($DbCon, 'SupportMemberStateID');

     // We get default users infos
     $ArraySMSStateInfos = array();
     if (file_exists($sRootInstallDir."Admin/Install/default_users.txt"))
     {
         $ArrayFile = file($sRootInstallDir."Admin/Install/default_users.txt");
         if (!empty($ArrayFile))
         {
             foreach($ArrayFile as $s => $CurrentLine)
             {
                 // ID:login/pwd
                 $ArrayTmp = explode(':', $CurrentLine);
                 $ArrayLoginPwd = explode('/', $ArrayTmp[1]);
                 $ArraySMSStateInfos[$ArrayTmp[0]] = array($ArrayLoginPwd[0], $ArrayLoginPwd[1]);
             }
         }
     }
     else
     {
         // No defualt users file
         $bContinue = FALSE;
     }

     if ((isset($ArraySMStates['SupportMemberStateID'])) && (!empty($ArraySMStates['SupportMemberStateID'])) && ($bContinue))
     {
         foreach($ArraySMStates['SupportMemberStateID'] as $sms => $CurrentStateID)
         {
             if (isset($ArraySMSStateInfos[$CurrentStateID]))
             {
                 // Login / password
                 $ArrayParams[$ArraySMStates['SupportMemberStateName'][$sms]] = array($ArraySMSStateInfos[$CurrentStateID][0],
                                                                                      $ArraySMSStateInfos[$CurrentStateID][1]);
             }
             else
             {
                 // No default user for this support member state
                 $ArrayParams[$ArraySMStates['SupportMemberStateName'][$sms]] = array('', '');
             }
         }
     }

     unset($ArraySMSStateInfos, $ArraySMStates);

     dbDisconnection($DbCon);
 }
 elseif (!empty($_POST["bRedirectStep"]))
 {
     //**************** Redirect user on login form ****************//
     $iInstallationStep = 8;
     header("location: ".$CONF_ROOT_DIRECTORY."Support/index.php");
 }

 //################################ END FORM PROCESSING ##########################

 initGraphicInterface(
                      $LANG_INTRANET_NAME,
                      array(
                            'Styles_install.css' => 'screen',
                            '../../GUI/Styles/styles.css' => 'screen',
                            ),
                      array('Verifications.js'),
                      ''
                     );
 openWebPage();

 // Display invisible link to go directly to content
 displayStyledLinkText($LANG_GO_TO_CONTENT, '#InstallForm', 'Accessibility');

 // Display the header of the application
 displayHeader($LANG_INTRANET_HEADER);

 // Content of the web page
 openArea('id="content"');

 openArea('id="page"');

 // Display the informations, forms, etc. on the right of the web page
 displayTitlePage($LANG_INSTALL_INDEX_PAGE_TITLE, 2);

 // We check if there are some errors to display
 if (!empty($ArrayResults['Errors']))
 {
     // We display all error messages
     openParagraph();

     echo "<ul class=\"ErrorMsg\">\n";
     foreach($ArrayResults['Errors'] as $e => $CurrentErrorMsg)
     {
         echo "\t<li>$CurrentErrorMsg</li>\n";
     }

     echo "</ul>\n";

     closeParagraph();
 }

 // We display form to install the application
 switch($iInstallationStep)
 {
     case 1:
         // Start install : intro
         openParagraph();
         displayStyledText($LANG_INSTALL_INDEX_PAGE_INTRODUCTION, '');
         closeParagraph();

         // Display the form of the step
         displayStartInstallStepForm();
         break;

     case 2:
         // Step 1 : display the form to create the database connexion
         openParagraph();
         displayStyledText($LANG_INSTALL_STEP_1_PAGE_INTRODUCTION, '');
         closeParagraph();

         displayCreateDBConnexionStepResultForm();
         break;

     case 3:
         // Step 2 : display the form to check if the server configuration is OK
         openParagraph();
         displayStyledText($LANG_INSTALL_STEP_2_PAGE_INTRODUCTION, '');
         closeParagraph();

         displayCheckStepForm();
         break;

     case 4:
         // Step 2 : display the result form about the server configuration
         openParagraph();
         displayStyledText($LANG_INSTALL_STEP_2_PAGE_INTRODUCTION, '');
         closeParagraph();

         // Display the result form of the step n°2
         displayCheckStepResultForm($ArrayParams);
         break;

     case 5:
         // Step 3 : display the form to create the database with data
         openParagraph();
         displayStyledText($LANG_INSTALL_STEP_3_PAGE_INTRODUCTION, '');
         closeParagraph();

         // Display the result form of the step
         displayCreateDatabaseForm();
         break;

     case 6:
         // Step 3 : display the result form of the step n°3
         openParagraph();
         displayStyledText($LANG_INSTALL_STEP_3_PAGE_INTRODUCTION, '');
         closeParagraph();

         openParagraph();
         displayStyledText($LANG_INSTALL_STEP_3_PAGE_DB_NB_TABLES.count($ArrayParams), '');
         closeParagraph();

         // Display the result form of the step
         displayCreateDatabaseStepResultForm($ArrayParams);
         break;

     case 7:
         // Step 4 : display the result form about default users
         openParagraph();
         displayStyledText($LANG_INSTALL_STEP_4_PAGE_INTRODUCTION, '');
         closeParagraph();

         displayDefaultUsersStepResultForm($ArrayParams);
         break;
 }

 // To measure the execution script time
 if ($CONF_DISPLAY_EXECUTION_TIME_SCRIPT)
 {
     openParagraph('InfoMsg');
     initEndTime();
     displayExecutionScriptTime('ExecutionTime');
     closeParagraph();
 }

 closeArea();

 // Close the <div> "content"
 closeArea();

 // Footer of the application
 displayFooter($LANG_INTRANET_FOOTER);

 // Close the web page
 closeWebPage();

 closeGraphicInterface();
?>