********************************************
*                                          *
*         Quick start instructions         *
*                                          *
********************************************

How to install quickly the CanteenCalandreta software on Windows with WampServer (or similar) or on a true server (OVH, 1&1...)?

1) Download CanteenCalandreta at https://framagit.org/calandreta/planeta/

2) Unzip the zip file in /www/ directory of your web server (ex : Apache). You must have now the directory /web-server-path/www/CanteenCalandreta/ with its sub-directories (/Admin/, /Common/,...)

3) Set unix access rights 777 for the following sub-directories of /CanteenCalandreta/ : /Exports/ and /Upload/

4) Execute /Admin/Install/index.php to complete the installation of Planeta (connection to the database, check the access rights on directories, create the database with data...).

So, CanteenCalandreta should work. To check it, go on the page http://domainname.ext/CanteenCalandreta/Support/index.php (domainname.ext can be localhost). The database contains 1 user for each profil except for families.
The login/password of the users for the Support module are :
* Admin user : admin/admin. He has lot of rights.
* Resp facture user : rf/rf. He generates bills, check plannings and register payments of families.
* Resp inscript user : ri/ri. Can register families to the canteen planning instead of families.
* Ajude user : aj/aj. Get the list of the synthesis of the day. Register children to the nursery planning.
* Resp admin : ra/ra. Administrative responsible : can access to all data in read-only mode.
* Resp Ev : rv/rv. Events responsible : create and mange festive and maintenance events.

5) Thanks to the admin account, log on CanteenCalandreta to create families and their children, then go on "Admin" module to create families accounts and update other variables of configuration (about canteen prices, monthly contributions...).

6) Set, at least, the following files in the cron with the right date/frequency :
* /CanteenCalandreta/Support/ExecuteDeleteTemporaryForumTopics.php
* /CanteenCalandreta/Support/ExecuteJobs.php
* /CanteenCalandreta/Support/SendEmailDailyCanteenPlanning.php
* /CanteenCalandreta/Support/SendEmailDailyNurseryPlanningUpdates.php
* /CanteenCalandreta/Support/SendEmailEventPbContributionReminder.php
* /CanteenCalandreta/Support/SendEmailEventReminder.php
* /CanteenCalandreta/Support/SendEmailLaundryReminder.php
* /CanteenCalandreta/Support/SendEmailSnackDuringWeekReminder.php
* /CanteenCalandreta/Support/SendEmailSnackReminder.php
* /CanteenCalandreta/Support/SendEmailWarningTooManyCanteenRegistrations.php
* /CanteenCalandreta/Support/SendEmailWeeklyCanteenPlanning.php
