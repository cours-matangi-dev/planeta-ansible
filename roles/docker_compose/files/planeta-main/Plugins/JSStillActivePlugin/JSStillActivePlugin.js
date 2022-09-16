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
 * JS plugin session still active module : check if the session of the logged
 * user is still active
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-05-05
 */


 var StillActivePluginPath;
 var StillActivePluginAjax;
 var StillActivePluginPeriodicalAjax;
 var StillActivePluginLanguage;
 var StillActivePluginPolling;


/**
 * Function used to init this plugin
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2021-05-05
 *
 * @param Timer     Integer    Timeout to poll if the session is still active, in seconds [10..n]
 */
 function initStillActivePlugin(Path, Language, Timer)
 {
     if (Timer < 10) {
         Timer = 10;
     }

     StillActivePluginLanguage = Language;
     StillActivePluginPath = Path;

     // Get input fields with "submit" type
     if(window.XMLHttpRequest) {
         // Firefox
         StillActivePluginAjax = new XMLHttpRequest();
         StillActivePluginPeriodicalAjax = new XMLHttpRequest();
     } else if(window.ActiveXObject) {
         // Internet Explorer
         StillActivePluginAjax = new ActiveXObject("Microsoft.XMLHTTP");
         StillActivePluginPeriodicalAjax = new ActiveXObject("Microsoft.XMLHTTP");
     } else { // XMLHttpRequest non supporté par le navigateur
         alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
     }

     // Add verification on "submit" button to check if the session is still activated when a form is submitted
     var ArrayTmp = document.querySelectorAll("input[type='submit']");
     for(var b = 0; b < ArrayTmp.length; b++) {
         if(window.attachEvent) {
             ArrayTmp[b].attachEvent("onclick", StillActivePluginCheckBeforeSubmit);   // IE
         } else {
             ArrayTmp[b].addEventListener("click", StillActivePluginCheckBeforeSubmit, false);   // FF
         }
     }

     // Create a periodical verification
     StillActivePluginPolling = setInterval(function(){
         StillActivePluginPeriodicalAjax.onreadystatechange = StillActivePluginHandlerTXT;
         StillActivePluginPeriodicalAjax.open("GET", StillActivePluginPath + 'PHPStillActivePlugin.php', true);
         StillActivePluginPeriodicalAjax.send(null);
     }, Timer * 1000);
 }


 function StillActivePluginClick(evt)
 {
     // Remove the alert message
     var DivAlert = document.getElementById('StillActiveAlert');
     DivAlert.parentNode.removeChild(DivAlert);
 }


 // Used by the submittion of a form
 function StillActivePluginCheckBeforeSubmit(evt)
 {
     StillActivePluginAjax.open("GET", StillActivePluginPath + 'PHPStillActivePlugin.php', false);
     StillActivePluginAjax.send(null);

     if (StillActivePluginAjax.responseText != 200) {
         // No connected
         switch(StillActivePluginLanguage) {
             case 'oc':
                 alert("La vòstra sesilha es acabada!\nAvètz de vos tornar connectar se\nvolètz pas pèrdre las donadas qu'èretz\nen tren de dintrar!");
                 break;

             case 'fr':
             default:
                 alert("ATTENTION : Votre session a expiré!\nVous devez vour reconnecter si vous ne\nvoulez pas perdre les données que vous\nétiez en train de saisir!");
                 break;
         }

         evt.stop();

         return false;
     }
 }


 // Used by the periodical verification
 function StillActivePluginHandlerTXT()
 {
     if ((StillActivePluginPeriodicalAjax.readyState == 4) && (StillActivePluginPeriodicalAjax.status == 200)) {
         if (null == StillActivePluginPeriodicalAjax.responseText.match(/^200/)) {
             // We stop the polling
             clearInterval(StillActivePluginPolling);

             // Received code <> 200 -> The user isn't connected
             var DivAlert = document.createElement('div');
             DivAlert.setAttribute('id', 'StillActiveAlert');

             var ParaAlert = document.createElement('p');
             switch(StillActivePluginLanguage) {
                 case 'oc':
                     ParaAlert.innerHTML = "La vòstra sesilha es acabada! Avètz de vos tornar connectar se volètz pas pèrdre las donadas qu'èretz en tren de dintrar!<br /><br /><br />";
                     break;

                 case 'fr':
                     ParaAlert.innerHTML = "ATTENTION : Votre session a expiré! Vous devez vous reconnecter si vous ne voulez pas perdre les données que vous étiez en train de saisir!<br /><br /><br />";
                     break;

                 case 'en':
                 default:
                     ParaAlert.innerHTML = "WARNING : Your session has expired! You need to reconnect if you won't to lose the data you were entering!<br /><br /><br />";
                     break;
             }

             var ButtonAlert = document.createElement('button');
             ButtonAlert.setAttribute('type', 'button');
             ButtonAlert.innerHTML = "Ok";

             if(window.attachEvent) {
                 ButtonAlert.attachEvent("onclick", StillActivePluginClick);  // IE
             } else {
                 ButtonAlert.addEventListener("click", StillActivePluginClick, false);  // FF
             }

             ParaAlert.appendChild(ButtonAlert);

             DivAlert.appendChild(ParaAlert);
             if (document.getElementById('webpage')) {
                 document.getElementById('webpage').appendChild(DivAlert);
             } else {
                 document.getElementById('content').appendChild(DivAlert);
             }
         }
     }
 }




