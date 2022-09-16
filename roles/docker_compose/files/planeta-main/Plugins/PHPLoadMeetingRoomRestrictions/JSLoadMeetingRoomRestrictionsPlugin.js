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
 * @author  Christophe Javouhey
 * @version 1.0
 * @since 2019-11-22
 */


 var LoadMeetingRoomRestrictionsPluginAjax;
 var LoadMeetingRoomRestrictionsPluginPath;
 var LoadMeetingRoomRestrictionsPluginSelectedStartTime = '';
 var LoadMeetingRoomRestrictionsPluginSelectedEndTime = '';


/**
 * Function used to init this plugin
 *
 * @author Christophe Javouhey
 * @version 1.0
 *
 * @param Path               String        Path of the script
 *
 * @since 2019-11-22
 */
 function initLoadMeetingRoomRestrictionsPlugin(Path)
 {
     LoadMeetingRoomRestrictionsPluginPath = Path;

     var objMeetingRoomList = document.getElementById('lMeetingRoomID');
     var objStartDate = document.getElementById('startDate');
     var objStartTimeList = document.getElementById('lStartTime');
     var objEndTimeList = document.getElementById('lEndTime');

     if ((objMeetingRoomList) && (objStartDate) && (objStartTimeList) && (objEndTimeList)) {
         LoadMeetingRoomRestrictionsPluginSelectedStartTime = objStartTimeList.options[objStartTimeList.options.selectedIndex].value;
         LoadMeetingRoomRestrictionsPluginSelectedEndTime = objEndTimeList.options[objEndTimeList.options.selectedIndex].value;

         // Add event on the start date
         if (window.attachEvent) {
             objStartDate.attachEvent("ondatechange", LoadMeetingRoomRestrictionsPluginStartDateonChange);                        // IE
             objMeetingRoomList.attachEvent("onchange", LoadMeetingRoomRestrictionsPluginStartDateonChange);
         } else {
             objStartDate.addEventListener("ondatechange", LoadMeetingRoomRestrictionsPluginStartDateonChange, false);                // FF
             objMeetingRoomList.addEventListener("change", LoadMeetingRoomRestrictionsPluginStartDateonChange, false);
         }

         if(window.XMLHttpRequest) // Firefox
             LoadMeetingRoomRestrictionsPluginAjax = new XMLHttpRequest();
         else if(window.ActiveXObject) // Internet Explorer
             LoadMeetingRoomRestrictionsPluginAjax = new ActiveXObject("Microsoft.XMLHTTP");
         else { // XMLHttpRequest non supporté par le navigateur
             alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
         }

         // To get restrictions for the default entered start date
         var event = new Event('ondatechange');
         objStartDate.dispatchEvent(event);
     }
 }


 //####################### EVENTS ############################
 function LoadMeetingRoomRestrictionsPluginStartDateonChange(evt)
 {
     // Get entered start date
     var objStartDate = document.getElementById('startDate');
     var sStartDateValue = objStartDate.value;

     // Get the selected meeting room
     var objMeetingRoomList = document.getElementById('lMeetingRoomID');
     var SelectedMeetingRoomID = objMeetingRoomList.options[objMeetingRoomList.options.selectedIndex].value;

     // Send the Ajax request to get restrictions on the selected meeting room and the selected start date
     if (objStartDate.value != '') {
         // We convert the date in english format
         var ArrayDate = LoadMeetingRoomRestrictionsPluginListSepToArray(sStartDateValue, '/');
         sStartDateValue = ArrayDate[2] + '-' + ArrayDate[1] + '-' + ArrayDate[0];

         // Selected meeting room ?
         var sMeetingRoom = '';
         if (SelectedMeetingRoomID > 0) {
             sMeetingRoom = "&ForMeetingRoom=" + SelectedMeetingRoomID;
         }

         LoadMeetingRoomRestrictionsPluginAjax.onreadystatechange = LoadMeetingRoomRestrictionsPluginHandlerHTML;
         LoadMeetingRoomRestrictionsPluginAjax.open("GET", LoadMeetingRoomRestrictionsPluginPath + "PHPLoadMeetingRoomRestrictionsPlugin.php?getTimeSlotsFor=" + sStartDateValue + sMeetingRoom, true);
         LoadMeetingRoomRestrictionsPluginAjax.send(null);
     }
 }


 function LoadMeetingRoomRestrictionsPluginHandlerHTML()
 {
     if ((LoadMeetingRoomRestrictionsPluginAjax.readyState == 4) && (LoadMeetingRoomRestrictionsPluginAjax.status == 200)) {
         if (LoadMeetingRoomRestrictionsPluginAjax.responseText == '503') {
             alert("Impossible de charger les restrictions!");
         } else {
             if (LoadMeetingRoomRestrictionsPluginAjax.responseText == '') {
                 // Do nothing
             } else {
                 // There are some erstrictions for the date and this meeting room
                 // Delete all previous time slots in start time slots and end time slots
                 var objStartTimeList = document.getElementById('lStartTime');
                 var objEndTimeList = document.getElementById('lEndTime');
                 LoadMeetingRoomRestrictionsPluginDeleteTimeSlots('lStartTime');
                 LoadMeetingRoomRestrictionsPluginDeleteTimeSlots('lEndTime');

                 objStartTimeList.innerHTML = LoadMeetingRoomRestrictionsPluginAjax.responseText;

                 // Set the previsous selected start time value
                 if (LoadMeetingRoomRestrictionsPluginSelectedStartTime != '') {
                     LoadMeetingRoomRestrictionsPluginGetSelectedOptionWithVal(objStartTimeList, LoadMeetingRoomRestrictionsPluginSelectedStartTime);
                 }

                 objEndTimeList.innerHTML = LoadMeetingRoomRestrictionsPluginAjax.responseText;

                 // Set the previsous selected end time value
                 if (LoadMeetingRoomRestrictionsPluginSelectedEndTime != '') {
                     LoadMeetingRoomRestrictionsPluginGetSelectedOptionWithVal(objEndTimeList, LoadMeetingRoomRestrictionsPluginSelectedEndTime);
                 }
             }
         }
     }
 }


 //####################### OTHER FUNCTIONS ###################
 function LoadMeetingRoomRestrictionsPluginGetSelectedOptionWithVal(sel, val)
 {
     var opt;
     var len = sel.options.length;

     for(var i = 0; i < len; i++) {
         opt = sel.options[i];
         if (opt.value == val ) {
             sel.selectedIndex = i;
             break;
         }
     }

     return opt;
 }


 function LoadMeetingRoomRestrictionsPluginDeleteTimeSlots(TimeSlotsList)
 {
     var objTimeSlotsList = document.getElementById(TimeSlotsList);
     if (objTimeSlotsList) {
         // We delete all values
         for(var i = objTimeSlotsList.length - 1; i >= 0; i--) {
             if (objTimeSlotsList.options[i].value != '0') {
                 // We delete this value from the list
                 objTimeSlotsList.remove(i);
             }
         }
     }
 }


 function LoadMeetingRoomRestrictionsPluginListSepToArray(List, Separator)
 {
     var ArrayResult = new Array();

     if (List != "")
     {
         var i = 0;
         var PosInit = 0;
         var Pos = List.indexOf(Separator, PosInit);
         while (Pos != -1)
         {
             // We extract the value
             ArrayResult[i] = List.substring(PosInit, Pos);
             PosInit = Pos + 1;
             i++;

             // We extract the next value
             Pos = List.indexOf(Separator, PosInit);
         }

         // We try to extract the last value
         var sLastValue = List.substring(PosInit, List.length);
         if (sLastValue != "")
         {
             ArrayResult[i] = sLastValue;
         }
     }

     return ArrayResult;
 }










