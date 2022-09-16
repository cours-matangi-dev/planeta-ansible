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
 * Support module : functions used to validate some forms of Install module
 *
 * @author Christophe Javouhey
 * @version 3.8
 * @since 2022-06-27
 */


/**
 * Function used to validate the form to create the db connexion
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2022-06-30
 *
 * @param ServerMsgError      String    Error message if the server name field is empty
 * @param UserMsgError        String    Error message if the user name field is empty
 * @param PwdMsgError         String    Error message if the password field is empty
 *
 * @return Boolean            TRUE if the form is correctly entered, FALSE otherwise
 */
 function VerificationCreateDBConnexion(ServerMsgError, UserMsgError, PwdMsgError)
 {
     if (document.forms[0].sServerName.value == "")
     {
         alert(ServerMsgError);
         return false;
     }
     else if (document.forms[0].sUser.value == "")
     {
         alert(UserMsgError);
         return false;
     }
     else if (document.forms[0].sPassword.value == "")
     {
         alert(PwdMsgError);
         return false;
     }
     else
     {
         return true;
     }
 }


/**
 * Function used to validate the form to create the database
 *
 * @author Christophe Javouhey
 * @version 1.0
 * @since 2022-07-01
 *
 * @param DBNameMsgError      String    Error message if the database name field is empty
 *
 * @return Boolean            TRUE if the form is correctly entered, FALSE otherwise
 */
 function VerificationCreateDB(DBNameMsgError)
 {
     if (document.forms[0].sDatabasename.value == "")
     {
         alert(DBNameMsgError);
         return false;
     }
     else
     {
         return true;
     }
 }

