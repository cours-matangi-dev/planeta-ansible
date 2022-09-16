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
 * Support module : delete expired temporary forum topics with messages and uploaded files.
 *
 * @author Christophe Javouhey
 * @version 3.7
 *     - 2021-12-27 : v3.7. Patch getIntranetRootDirectoryHDD() for PHP8
 *
 * @since 2021-05-01
 */

 if (!function_exists('getIntranetRootDirectoryHDD'))
 {
    /**
     * Give the path of the Intranet root directory on the HDD
     *
     * @author Christophe Javouhey
     * @version 1.1
     *     - 2021-12-27 : v1.1. Replace $sLocalDir{0} by substr($sLocalDir, 0, 1) for PHP8
     *
     * @since 2012-03-20
     *
     * @return String             Intranet root directory on the HDD
     */
     function getIntranetRootDirectoryHDD()
     {
         $sLocalDir = str_replace(array("\\"), array("/"), dirname(__FILE__)).'/';
         $bUnixOS = FALSE;
         if (substr($sLocalDir, 0, 1) == '/')
         {
             $bUnixOS = TRUE;
         }

         $ArrayTmp = explode('/', $sLocalDir);

         $iPos = array_search("CanteenCalandreta", $ArrayTmp);
         if ($iPos !== FALSE)
         {
             $sLocalDir = '';
             if ($bUnixOS)
             {
                 $sLocalDir = '/';
             }

             for($i = 0; $i <= $iPos; $i++)
             {
                 $sLocalDir .= $ArrayTmp[$i].'/';
             }
         }

         return $sLocalDir;
     }
 }

 $DOCUMENT_ROOT = getIntranetRootDirectoryHDD();

 include_once($DOCUMENT_ROOT.'GUI/GraphicInterface.php');

 $DbCon = dbConnection();

 // Current date
 $CurrentDate = date('Y-m-d');

 echo "Delete temporary forum topics having expiration date < $CurrentDate";

 // We get expired temporary topics
 $ArrayExpiredForumTopics = dbSearchForumTopic($DbCon, array(
                                                             "ExpirationStartDate" => array('<', $CurrentDate)
                                                            ), "ForumTopicID", 1, 0);

 if ((isset($ArrayExpiredForumTopics['ForumTopicID'])) && (!empty($ArrayExpiredForumTopics['ForumTopicID'])))
 {
     $HDDDirectory = $CONF_UPLOAD_FORUM_MESSAGE_FILES_DIRECTORY_HDD;

     // We have temprary forum topics to delete (with messages and uploaded files)
     foreach($ArrayExpiredForumTopics['ForumTopicID'] as $ft => $ForumTopicID)
     {
         // We get messages of the topic to delete
         $ArrayForumMessages = dbSearchForumMessage($DbCon, array(
                                                                  'ForumTopicID' => $ForumTopicID
                                                                 ), "ForumMessageID", 1, 0);

         if ((isset($ArrayForumMessages['ForumMessageID'])) && (!empty($ArrayForumMessages['ForumMessageID'])))
         {
             // There are some messages to delete
             foreach($ArrayForumMessages['ForumMessageID'] as $fm => $ForumMessageID)
             {
                 // We get uploaded files of the message to delete
                 $ArrayFiles = getForumMessageUploadedFiles($DbCon, $ForumMessageID, array(), 'UploadedFileDate');

                 if ((isset($ArrayFiles['UploadedFileID'])) && (!empty($ArrayFiles['UploadedFileID'])))
                 {
                     foreach($ArrayFiles['UploadedFileID'] as $uf => $UploadedFileID)
                     {
                         $Year = date('Y', strtotime($ArrayFiles['UploadedFileDate'][$uf]));
                         $PathFileToDelete = $HDDDirectory."$Year/".$ArrayFiles['UploadedFileName'][$uf];

                         // Delete file on the HDD and DB
                         if (file_exists($PathFileToDelete))
                         {
                             unlink($PathFileToDelete);
                         }

                         dbDeleteUploadedFile($DbCon, $UploadedFileID);
                     }
                 }

                 unset($ArrayFiles);
             }
         }

         // Delete the topic and the linked messages, subscribtions and last read messages ID
         dbDeleteForumTopic($DbCon, $ForumTopicID);

         unset($ArrayForumMessages);
     }
 }

 // We close the database connection
 dbDisconnection($DbCon);
?>