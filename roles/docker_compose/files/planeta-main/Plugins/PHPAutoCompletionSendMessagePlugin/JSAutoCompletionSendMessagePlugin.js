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
 * PHP plugin autocompletion module : add the autocompletion function for the recipients and in copy
 * fields of the "Send message" form
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2016-03-02
 */


 var AutoCompletionSendMessagePluginPath;
 var AutoCompletionSendMessagePluginLangage;
 var AutoCompletionSendMessagePluginAjax;


/**
 * Function used to init this plugin
 *
 * @author Christophe Javouhey
 * @version 2.0
 *     - 2021-05-19 : get and display pictures of families
 *
 * @since 2016-03-02
 */
 function initAutoCompletionSendMessagePlugin(FormFieldName, Lang)
 {
     AutoCompletionSendMessagePluginLangage = Lang;

     $A(document.getElementsByTagName("script")).findAll( function(s) {
         return (s.src && s.src.match(/JSAutoCompletionSendMessagePlugin\.js(\?.*)?$/))
     }).each( function(s) {
         AutoCompletionSendMessagePluginPath = s.src.replace(/JSAutoCompletionSendMessagePlugin\.js(\?.*)?$/,'');
     });

     // We check if the form field exists
     var objFormField = document.getElementById(FormFieldName);
     if (objFormField) {
         // We create the DIV to display values found
         var AutoCompletionDiv = document.createElement('div');
         AutoCompletionDiv.setAttribute('id', FormFieldName + 'ListAutoCompletion_update');
         AutoCompletionDiv.className = 'FormFieldAutoCompletionlist_update';
         objFormField.parentNode.insertBefore(AutoCompletionDiv, objFormField.nextSibling);

         new Ajax.Autocompleter(
                                FormFieldName,
                                FormFieldName + 'ListAutoCompletion_update',
                                AutoCompletionSendMessagePluginPath + 'PHPAutoCompletionSendMessagePlugin.php',
                                {
                                    method: 'post',
                                    paramName: 'debut',
                                    minChars: 2,
                                    tokens: ';',
                                    afterUpdateElement: getAutoCompletionSendMessageSelectionId,
                                    parameters: 'FormFieldName=' + FormFieldName
                                }
                               );

         // We create the Ajax object to get pictures of families
         if(window.XMLHttpRequest) {  // Firefox
             AutoCompletionSendMessagePluginAjax = new XMLHttpRequest();
         } else if(window.ActiveXObject) {  // Internet Explorer
             AutoCompletionSendMessagePluginAjax = new ActiveXObject("Microsoft.XMLHTTP");
         } else { // XMLHttpRequest non supporté par le navigateur
             alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
         }
     }
 }


 function getAutoCompletionSendMessageSelectionId(input, li)
 {
     var objRecipientsList = document.getElementById('objRecipientsList');
     if (objRecipientsList) {
         // Add an item in this <div>
         var objItemAdded = document.createElement('div');
         objItemAdded.setAttribute('id', 'L' + li.id);
         objItemAdded.className = 'AutoCompletionSendMessageItem';
         objItemAdded.innerHTML = li.innerHTML;

         var sTip = '';
         switch(AutoCompletionSendMessagePluginLangage) {
             case 'oc':
                 sTip = "Clicatz aici per suprimir la familha/alias " + li.innerHTML + ".";
                 break;

             case 'fr':
                 sTip = "Cliquez ici pour supprimer la destinataire " + li.innerHTML + ".";
                 break;

             case 'en':
             default:
                 sTip = "Click here to delete the " + li.innerHTML + " recipient.";
                 break;
         }

         objItemAdded.setAttribute('title', sTip);

         if (window.attachEvent) {
             objItemAdded.attachEvent("onclick", AutoCompletionSendMessageItemOnClick);            // IE
         } else {
             objItemAdded.addEventListener("click", AutoCompletionSendMessageItemOnClick, false);  // FF
         }

         objRecipientsList.appendChild(objItemAdded);

         // To get pictures if it'a family
         AutoCompletionSendMessageGetFamilyPictures(li.id);
     }

     // Delete value in the input type text
     input.value = '';
 }


 function AutoCompletionSendMessageItemOnClick(evt)
 {
     var objItem = evt.target || evt.srcElement;

     // Delete the item
     objItem.parentNode.removeChild(objItem);

     // We check if there are pictures linked to this item
     var UserPicID = objItem.id.substring(1);
     var objImg = document.getElementById('Pic' + UserPicID + '-1');
     if (objImg) {
         // There is a main picture for this family
         objImg.parentNode.removeChild(objImg);
     }

     objImg = document.getElementById('Pic' + UserPicID + '-2');
     if (objImg) {
         // There is a second picture for this family
         objImg.parentNode.removeChild(objImg);
     }
 }


 function AutoCompletionSendMessageGetFamilyPictures(id)
 {
     // We get pictures only if the selected item is a family
     switch(id.substring(0, 1)) {
         case 'F':
         case 'R':
         case 'S':
             // It's a family or a supporter or a workgroup registration
             AutoCompletionSendMessagePluginAjax.onreadystatechange = AutoCompletionSendMessagePluginHandlerHTMLPictures;
             AutoCompletionSendMessagePluginAjax.open("GET", AutoCompletionSendMessagePluginPath + "PHPAutoCompletionSendMessagePlugin.php?getPictures=" + id, true);
             AutoCompletionSendMessagePluginAjax.send(null);
             break;
     }
 }


 function AutoCompletionSendMessagePluginHandlerHTMLPictures()
 {
     if ((AutoCompletionSendMessagePluginAjax.readyState == 4) && (AutoCompletionSendMessagePluginAjax.status == 200)) {
         if (AutoCompletionSendMessagePluginAjax.responseText != '') {
             var objPicArea = document.getElementById('objRecipientsList');
             if (objPicArea) {
                 objPicArea.parentNode.insertAdjacentHTML('beforeend', AutoCompletionSendMessagePluginAjax.responseText);
             }
         }
     }
 }



