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
 * PHP plugin link meeting rooms restrictions to a date : display only time slots allowed
 * for the given meeting room dans the given date
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2019-11-22
 */


 // Include Config.php because of the name of the session
 require '../../GUI/GraphicInterface.php';

 session_start();

 $Html = NULL;
 if (isset($_SESSION['SupportMemberID']))
 {
     // We get the parameters
     if (array_key_exists('getTimeSlotsFor', $_GET))
     {
         $StartDate = trim(strip_tags($_GET['getTimeSlotsFor']));
         if (preg_match("[\d\d\d\d-\d\d-\d\d]", $StartDate) != 0)
         {
             $MeetingRoomRestrictions = $CONF_MEETING_REGISTRATIONS_OPENED_HOURS_FOR_WEEK_DAYS;

             // Check if the meeting room is set
             $MeetingRoomID = 0;
             if (array_key_exists('ForMeetingRoom', $_GET))
             {
                 $MeetingRoomID = trim(strip_tags($_GET['ForMeetingRoom']));
                 if (($MeetingRoomID > 0) && (isInteger($MeetingRoomID)))
                 {
                     $DbCon = dbConnection();

                     if (isExistingMeetingRoom($DbCon, $MeetingRoomID))
                     {
                         $MeetingRoomRestrictions = getMeetingRoomRestrictions($DbCon, $MeetingRoomID);
                         if (empty($MeetingRoomRestrictions))
                         {
                             $MeetingRoomRestrictions = $CONF_MEETING_REGISTRATIONS_OPENED_HOURS_FOR_WEEK_DAYS;
                         }
                         else
                         {
                             // We extract the restriction of this room : HH:mm-HH:mm,HH:mm-HH:mm,...#H:mm-HH:mm,...#...
                             $ArrayRestrictions = explode('#', $MeetingRoomRestrictions);
                             $MeetingRoomRestrictions = array();

                             if (!empty($ArrayRestrictions))
                             {
                                 foreach($ArrayRestrictions as $r => $CurrentRestrictions)
                                 {
                                     $ArrayTimeSlots = explode(',', $CurrentRestrictions);
                                     foreach($ArrayTimeSlots as $ts => $CurrentTimeSlot)
                                     {
                                         $ArrayTimeSlots[$ts] = trim($CurrentTimeSlot);
                                     }

                                     $MeetingRoomRestrictions[] = $ArrayTimeSlots;
                                 }
                             }
                         }
                     }

                     // Release the connection to the database
                     dbDisconnection($DbCon);
                 }
             }

             $Html = '';
             if (!empty($MeetingRoomRestrictions))
             {
                 // Get the number of the selected day
                 $NumDay = date('N', strtotime($StartDate));
                 if (isset($MeetingRoomRestrictions[$NumDay - 1]))
                 {
                     $ArrayTimeSlots = array();
                     foreach($MeetingRoomRestrictions[$NumDay - 1] as $CurrentTimeSlotValue)
                     {
                         $ArrayRestrictions = explode('-', $CurrentTimeSlotValue);
                         $StartTime = $ArrayRestrictions[0].':00';
                         $EndTime = $ArrayRestrictions[1].':00';

                         $ArrayTmpTimeSlots = generateTimeSlots($StartTime, $EndTime, $CONF_MEETING_REGISTRATIONS_TIME_SLOT_SIZE);
                         if (!empty($ArrayTmpTimeSlots))
                         {
                             $ArrayTimeSlots = array_merge($ArrayTimeSlots, $ArrayTmpTimeSlots);
                         }
                     }

                     if (!empty($ArrayTimeSlots))
                     {
                         foreach($ArrayTimeSlots as $TimeSlot)
                         {
                             $Html .= "<option value=\"$TimeSlot\">$TimeSlot</option>";
                         }
                     }
                 }
             }
         }
     }
 }


 if (!is_null($Html))
 {
     echo $Html;
 }
 else
 {
     echo '503';
 }
?>
