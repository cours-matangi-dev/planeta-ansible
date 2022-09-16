CREATE TABLE `Alias` (
  `AliasID` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `AliasName` varchar(50) NOT NULL,
  `AliasDescription` varchar(255) DEFAULT NULL,
  `AliasMailingList` mediumtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `Banks` (
  `BankID` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `BankName` varchar(50) NOT NULL,
  `BankAcronym` varchar(5) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `Banks` (`BankID`, `BankName`, `BankAcronym`) VALUES
(1, 'Banque Populaire', 'BP'),
(2, 'Crédit Agricole', 'CA'),
(3, 'Crédit Mutuel', 'CM'),
(4, 'Société Générale', 'SG'),
(5, 'Caisse d\'Epargne', 'CE'),
(6, 'Banque Nationale de Paris', 'BNP'),
(7, 'Banque Populaire Occitane', 'BPO'),
(8, 'Crédit Lyonnais', 'LCL'),
(9, 'Crédit Industriel et Commercial', 'CIC'),
(10, 'Banque Courtois', 'BC'),
(11, 'Crédit Coopératif', 'CC'),
(12, 'Banque Postale', 'CCP'),
(13, 'AXA banque', 'AXA'),
(14, 'HSBC', 'HSBC');

CREATE TABLE `Bills` (
  `BillID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `BillDate` datetime NOT NULL,
  `BillForDate` date NOT NULL,
  `BillPreviousBalance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `BillDeposit` decimal(10,2) NOT NULL DEFAULT '0.00',
  `BillMonthlyContribution` decimal(10,2) NOT NULL DEFAULT '0.00',
  `BillNbCanteenRegistrations` TINYINT UNSIGNED NULL DEFAULT NULL,
  `BillCanteenAmount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `BillWithoutMealAmount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `BillNbNurseryRegistrations` TINYINT UNSIGNED NULL DEFAULT NULL,
  `BillNurseryAmount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `BillNurseryNbDelays` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `BillOtherAmount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `BillPaidAmount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `BillPaid` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `FamilyID` smallint(5) UNSIGNED NOT NULL,
  KEY `FamilyID` (`FamilyID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `CanteenRegistrations` (
  `CanteenRegistrationID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `CanteenRegistrationDate` date NOT NULL,
  `CanteenRegistrationForDate` date NOT NULL,
  `CanteenRegistrationAdminDate` date DEFAULT NULL,
  `CanteenRegistrationChildGrade` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `CanteenRegistrationChildClass` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `CanteenRegistrationWithoutPork` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `CanteenRegistrationValided` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `ChildID` smallint(5) UNSIGNED NOT NULL,
  KEY `ChildID` (`ChildID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `CanteenRegistrationsChildrenHabits` (
  `CanteenRegistrationChildHabitID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `CanteenRegistrationChildHabitProfil` smallint(5) UNSIGNED NOT NULL,
  `CanteenRegistrationChildHabitRate` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `CanteenRegistrationChildHabitType` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `ChildID` smallint(5) UNSIGNED NOT NULL,
  KEY `ChildID` (`ChildID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `Children` (
  `ChildID` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ChildFirstname` varchar(50) NOT NULL,
  `ChildSchoolDate` date NOT NULL,
  `ChildDesactivationDate` date DEFAULT NULL,
  `ChildGrade` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `ChildClass` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `ChildWithoutPork` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `ChildEmail` varchar(100) DEFAULT NULL,
  `FamilyID` smallint(5) UNSIGNED NOT NULL,
  KEY `FamilyID` (`FamilyID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ConfigParameters` (
  `ConfigParameterID` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ConfigParameterName` varchar(255) NOT NULL,
  `ConfigParameterType` varchar(10) NOT NULL,
  `ConfigParameterValue` mediumtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `ConfigParameters` (`ConfigParameterID`, `ConfigParameterName`, `ConfigParameterType`, `ConfigParameterValue`) VALUES
(1, 'CONF_SCHOOL_YEAR_START_DATES', 'xml', '<config-parameters>\r\n<school-year id=\"2010\">2009-09-05</school-year>\r\n<school-year id=\"2011\">2010-09-06</school-year>\r\n<school-year id=\"2012\">2011-09-05</school-year>\r\n<school-year id=\"2013\">2012-09-04</school-year>\r\n<school-year id=\"2014\">2013-09-03</school-year>\r\n<school-year id=\"2015\">2014-09-02</school-year>\r\n<school-year id=\"2016\">2015-09-01</school-year>\r\n<school-year id=\"2017\">2016-09-01</school-year>\r\n<school-year id=\"2018\">2017-09-04</school-year>\r\n<school-year id=\"2019\">2018-09-03</school-year>\r\n<school-year id=\"2020\">2019-09-02</school-year>\r\n<school-year id=\"2021\">2020-09-01</school-year>\r\n<school-year id=\"2022\">2021-09-02</school-year>\r\n</config-parameters>'),
(2, 'CONF_CLASSROOMS', 'xml', '<config-parameters>\r\n<school-year id=\"2010\">\r\n<classroom>-</classroom>\r\n<classroom>TPS-PS-MS</classroom>\r\n<classroom>MS-GS</classroom>\r\n<classroom>CP-CE1</classroom>\r\n<classroom>CE2-CM1-CM2</classroom>\r\n</school-year>\r\n<school-year id=\"2011\">\r\n<classroom>-</classroom>\r\n<classroom>TPS-PS-MS</classroom>\r\n<classroom>MS-GS</classroom>\r\n<classroom>CP-CE1</classroom>\r\n<classroom>CE2-CM1-CM2</classroom>\r\n</school-year>\r\n<school-year id=\"2012\">\r\n<classroom>-</classroom>\r\n<classroom>TPS-PS-MS</classroom>\r\n<classroom>MS-GS</classroom>\r\n<classroom>CP-CE1</classroom>\r\n<classroom>CE2-CM1-CM2</classroom>\r\n</school-year>\r\n<school-year id=\"2013\">\r\n<classroom>-</classroom>\r\n<classroom>TPS-PS-MS</classroom>\r\n<classroom>MS-GS</classroom>\r\n<classroom>CP-CE1</classroom>\r\n<classroom>CE2-CM1-CM2</classroom>\r\n</school-year>\r\n<school-year id=\"2014\">\r\n<classroom>-</classroom>\r\n<classroom>TPS-PS-MS</classroom>\r\n<classroom>GS-CP</classroom>\r\n<classroom>CE1-CE2</classroom>\r\n<classroom>CM1-CM2</classroom>\r\n</school-year>\r\n<school-year id=\"2015\">\r\n<classroom>-</classroom>\r\n<classroom>TPS-PS-MS</classroom>\r\n<classroom>GS-CP</classroom>\r\n<classroom>CE1-CE2</classroom>\r\n<classroom>CM1-CM2</classroom>\r\n</school-year>\r\n<school-year id=\"2016\">\r\n<classroom>-</classroom>\r\n<classroom>TPS-PS-MS</classroom>\r\n<classroom>GS-CP</classroom>\r\n<classroom>CE1-CE2</classroom>\r\n<classroom>CM1-CM2</classroom>\r\n</school-year>\r\n<school-year id=\"2017\">\r\n<classroom>-</classroom>\r\n<classroom>TPS-PS-MS</classroom>\r\n<classroom>GS-CP</classroom>\r\n<classroom>CE1-CE2</classroom>\r\n<classroom>CM1-CM2</classroom>\r\n</school-year>\r\n<school-year id=\"2018\">\r\n<classroom>-</classroom>\r\n<classroom>TPS-PS-MS</classroom>\r\n<classroom>GS-CP</classroom>\r\n<classroom>CE1-CE2</classroom>\r\n<classroom>CM1-CM2</classroom>\r\n</school-year>\r\n<school-year id=\"2019\">\r\n<classroom>-</classroom>\r\n<classroom>TPS-PS-MS</classroom>\r\n<classroom>GS-CP</classroom>\r\n<classroom>CE1-CE2</classroom>\r\n<classroom>CM1-CM2</classroom>\r\n</school-year>\r\n<school-year id=\"2020\">\r\n<classroom>-</classroom>\r\n<classroom>TPS-PS-MS</classroom>\r\n<classroom>GS-CP</classroom>\r\n<classroom>CE1-CE2</classroom>\r\n<classroom>CM1-CM2</classroom>\r\n</school-year>\r\n<school-year id=\"2021\">\r\n<classroom>-</classroom>\r\n<classroom>TPS-PS-MS</classroom>\r\n<classroom>GS-CP</classroom>\r\n<classroom>CE1-CE2</classroom>\r\n<classroom>CM1-CM2</classroom>\r\n</school-year>\r\n<school-year id=\"2022\">\r\n<classroom>-</classroom>\r\n<classroom>TPS-PS-MS</classroom>\r\n<classroom>GS-CP</classroom>\r\n<classroom>CE1-CE2</classroom>\r\n<classroom>CM1-CM2</classroom>\r\n</school-year>\r\n</config-parameters>'),
(3, 'CONF_CONTRIBUTIONS_ANNUAL_AMOUNTS', 'xml', '<config-parameters>\r\n<school-year id=\"2010\">\r\n<amount nbvotes=\"0\">15.00</amount>\r\n<amount nbvotes=\"1\">15.00</amount>\r\n<amount nbvotes=\"2\">30.00</amount>\r\n<amount nbvotes=\"3\">45.00</amount>\r\n</school-year>\r\n<school-year id=\"2011\">\r\n<amount nbvotes=\"0\">15.00</amount>\r\n<amount nbvotes=\"1\">15.00</amount>\r\n<amount nbvotes=\"2\">30.00</amount>\r\n<amount nbvotes=\"3\">45.00</amount>\r\n</school-year>\r\n<school-year id=\"2012\">\r\n<amount nbvotes=\"0\">15.00</amount>\r\n<amount nbvotes=\"1\">15.00</amount>\r\n<amount nbvotes=\"2\">30.00</amount>\r\n<amount nbvotes=\"3\">45.00</amount>\r\n</school-year>\r\n<school-year id=\"2013\">\r\n<amount nbvotes=\"0\">15.00</amount>\r\n<amount nbvotes=\"1\">15.00</amount>\r\n<amount nbvotes=\"2\">30.00</amount>\r\n<amount nbvotes=\"3\">45.00</amount>\r\n</school-year>\r\n<school-year id=\"2014\">\r\n<amount nbvotes=\"0\">15.00</amount>\r\n<amount nbvotes=\"1\">15.00</amount>\r\n<amount nbvotes=\"2\">30.00</amount>\r\n<amount nbvotes=\"3\">45.00</amount>\r\n</school-year>\r\n<school-year id=\"2015\">\r\n<amount nbvotes=\"0\">15.00</amount>\r\n<amount nbvotes=\"1\">15.00</amount>\r\n<amount nbvotes=\"2\">30.00</amount>\r\n<amount nbvotes=\"3\">45.00</amount>\r\n</school-year>\r\n<school-year id=\"2016\">\r\n<amount nbvotes=\"0\">15.00</amount>\r\n<amount nbvotes=\"1\">15.00</amount>\r\n<amount nbvotes=\"2\">30.00</amount>\r\n<amount nbvotes=\"3\">45.00</amount>\r\n</school-year>\r\n<school-year id=\"2017\">\r\n<amount nbvotes=\"0\">15.00</amount>\r\n<amount nbvotes=\"1\">15.00</amount>\r\n<amount nbvotes=\"2\">30.00</amount>\r\n<amount nbvotes=\"3\">45.00</amount>\r\n</school-year>\r\n<school-year id=\"2018\">\r\n<amount nbvotes=\"0\">15.00</amount>\r\n<amount nbvotes=\"1\">15.00</amount>\r\n<amount nbvotes=\"2\">30.00</amount>\r\n<amount nbvotes=\"3\">45.00</amount>\r\n</school-year>\r\n<school-year id=\"2019\">\r\n<amount nbvotes=\"0\">15.00</amount>\r\n<amount nbvotes=\"1\">15.00</amount>\r\n<amount nbvotes=\"2\">30.00</amount>\r\n<amount nbvotes=\"3\">45.00</amount>\r\n</school-year>\r\n<school-year id=\"2020\">\r\n<amount nbvotes=\"0\">15.00</amount>\r\n<amount nbvotes=\"1\">15.00</amount>\r\n<amount nbvotes=\"2\">30.00</amount>\r\n<amount nbvotes=\"3\">45.00</amount>\r\n</school-year>\r\n<school-year id=\"2021\">\r\n<amount nbvotes=\"0\">15.00</amount>\r\n<amount nbvotes=\"1\">15.00</amount>\r\n<amount nbvotes=\"2\">30.00</amount>\r\n<amount nbvotes=\"3\">45.00</amount>\r\n</school-year>\r\n<school-year id=\"2022\">\r\n<amount nbvotes=\"0\">15.00</amount>\r\n<amount nbvotes=\"1\">15.00</amount>\r\n<amount nbvotes=\"2\">30.00</amount>\r\n<amount nbvotes=\"3\">45.00</amount>\r\n</school-year>\r\n</config-parameters>'),
(5, 'CONF_CANTEEN_PRICES', 'xml', '<config-parameters>\r\n<school-year id=\"2010\">\r\n<lunch-price>3.66</lunch-price>\r\n<nursery-price>0.00</nursery-price>\r\n</school-year>\r\n<school-year id=\"2011\">\r\n<lunch-price>3.66</lunch-price>\r\n<nursery-price>0.00</nursery-price>\r\n</school-year>\r\n<school-year id=\"2012\">\r\n<lunch-price>3.66</lunch-price>\r\n<nursery-price>0.00</nursery-price>\r\n</school-year>\r\n<school-year id=\"2013\">\r\n<lunch-price>3.73</lunch-price>\r\n<nursery-price>0.00</nursery-price>\r\n</school-year>\r\n<school-year id=\"2014\">\r\n<lunch-price>3.79</lunch-price>\r\n<nursery-price>0.00</nursery-price>\r\n</school-year>\r\n<school-year id=\"2015\">\r\n<lunch-price>4.00</lunch-price>\r\n<nursery-price>0.00</nursery-price>\r\n</school-year>\r\n<school-year id=\"2016\">\r\n<lunch-price>3.40</lunch-price>\r\n<nursery-price>1.00</nursery-price>\r\n</school-year>\r\n<school-year id=\"2017\">\r\n<lunch-price>3.04</lunch-price>\r\n<nursery-price>1.00</nursery-price>\r\n</school-year>\r\n<school-year id=\"2018\">\r\n<lunch-price>3.08</lunch-price>\r\n<nursery-price>1.00</nursery-price>\r\n</school-year>\r\n<school-year id=\"2019\">\r\n<lunch-price>3.14</lunch-price>\r\n<nursery-price>1.00</nursery-price>\r\n</school-year>\r\n<school-year id=\"2020\">\r\n<lunch-price>3.28</lunch-price>\r\n<nursery-price>0.00</nursery-price>\r\n</school-year>\r\n<school-year id=\"2021\">\r\n<lunch-price>3.28</lunch-price>\r\n<nursery-price>0.00</nursery-price>\r\n</school-year>\r\n<school-year id=\"2022\">\r\n<lunch-price>3.28</lunch-price>\r\n<nursery-price>0.00</nursery-price>\r\n</school-year>\r\n</config-parameters>'),
(6, 'CONF_NURSERY_PRICES', 'xml', '<config-parameters>\r\n<school-year id=\"2010\">\r\n<am-nursery-price>1.25</am-nursery-price>\r\n<pm-nursery-price>1.25</pm-nursery-price>\r\n</school-year>\r\n<school-year id=\"2011\">\r\n<am-nursery-price>1.25</am-nursery-price>\r\n<pm-nursery-price>1.25</pm-nursery-price>\r\n</school-year>\r\n<school-year id=\"2012\">\r\n<am-nursery-price>1.25</am-nursery-price>\r\n<pm-nursery-price>1.25</pm-nursery-price>\r\n</school-year>\r\n<school-year id=\"2013\">\r\n<am-nursery-price>1.25</am-nursery-price>\r\n<pm-nursery-price>1.25</pm-nursery-price>\r\n</school-year>\r\n<school-year id=\"2014\">\r\n<am-nursery-price>1.25</am-nursery-price>\r\n<pm-nursery-price>1.25</pm-nursery-price>\r\n</school-year>\r\n<school-year id=\"2015\">\r\n<am-nursery-price>1.50</am-nursery-price>\r\n<pm-nursery-price>1.50</pm-nursery-price>\r\n</school-year>\r\n<school-year id=\"2016\">\r\n<am-nursery-price>1.50</am-nursery-price>\r\n<pm-nursery-price>1.50</pm-nursery-price>\r\n</school-year>\r\n<school-year id=\"2017\">\r\n<am-nursery-price>1.50</am-nursery-price>\r\n<pm-nursery-price>1.50</pm-nursery-price>\r\n</school-year>\r\n<school-year id=\"2018\">\r\n<am-nursery-price>1.50</am-nursery-price>\r\n<pm-nursery-price>1.50</pm-nursery-price>\r\n</school-year>\r\n<school-year id=\"2019\">\r\n<am-nursery-price>1.50</am-nursery-price>\r\n<pm-nursery-price>1.50</pm-nursery-price>\r\n</school-year>\r\n<school-year id=\"2020\">\r\n<am-nursery-price>0.00</am-nursery-price>\r\n<pm-nursery-price>0.00</pm-nursery-price>\r\n<other-timeslots>\r\n<other-timeslot-price id=\"T1\">0.00</other-timeslot-price>\r\n<other-timeslot-price id=\"T2\">0.00</other-timeslot-price>\r\n</other-timeslots>\r\n</school-year>\r\n<school-year id=\"2021\">\r\n<am-nursery-price>0.00</am-nursery-price>\r\n<pm-nursery-price>0.00</pm-nursery-price>\r\n<other-timeslots>\r\n<other-timeslot-price id=\"T1\">0.00</other-timeslot-price>\r\n<other-timeslot-price id=\"T2\">0.00</other-timeslot-price>\r\n</other-timeslots>\r\n</school-year>\r\n<school-year id=\"2022\">\r\n<am-nursery-price>0.00</am-nursery-price>\r\n<pm-nursery-price>0.00</pm-nursery-price>\r\n<other-timeslots>\r\n<other-timeslot-price id=\"T1\">0.00</other-timeslot-price>\r\n<other-timeslot-price id=\"T2\">0.00</other-timeslot-price>\r\n</other-timeslots>\r\n</school-year>\r\n</config-parameters>'),
(4, 'CONF_CONTRIBUTIONS_MONTHLY_AMOUNTS', 'xml', '<config-parameters>\r\n<school-year id=\"2010\">\r\n<monthly-contribution mode=\"MC_DEFAULT_MODE\">\r\n<amount nbchildren=\"1\">17.16</amount>\r\n<amount nbchildren=\"2\">29.64</amount>\r\n<amount nbchildren=\"3\">31.20</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_BENEFACTOR_MODE\">\r\n<amount nbchildren=\"1\">17.16</amount>\r\n<amount nbchildren=\"2\">29.64</amount>\r\n<amount nbchildren=\"3\">31.20</amount>\r\n</monthly-contribution>\r\n</school-year>\r\n<school-year id=\"2011\">\r\n<monthly-contribution mode=\"MC_DEFAULT_MODE\">\r\n<amount nbchildren=\"1\">17.16</amount>\r\n<amount nbchildren=\"2\">29.64</amount>\r\n<amount nbchildren=\"3\">31.20</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_BENEFACTOR_MODE\">\r\n<amount nbchildren=\"1\">17.16</amount>\r\n<amount nbchildren=\"2\">29.64</amount>\r\n<amount nbchildren=\"3\">31.20</amount>\r\n</monthly-contribution>\r\n</school-year>\r\n<school-year id=\"2012\">\r\n<monthly-contribution mode=\"MC_DEFAULT_MODE\">\r\n<amount nbchildren=\"1\">17.16</amount>\r\n<amount nbchildren=\"2\">29.64</amount>\r\n<amount nbchildren=\"3\">31.20</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_BENEFACTOR_MODE\">\r\n<amount nbchildren=\"1\">17.16</amount>\r\n<amount nbchildren=\"2\">29.64</amount>\r\n<amount nbchildren=\"3\">31.20</amount>\r\n</monthly-contribution>\r\n</school-year>\r\n<school-year id=\"2013\">\r\n<monthly-contribution mode=\"MC_DEFAULT_MODE\">\r\n<amount nbchildren=\"1\">17.16</amount>\r\n<amount nbchildren=\"2\">29.64</amount>\r\n<amount nbchildren=\"3\">31.20</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_BENEFACTOR_MODE\">\r\n<amount nbchildren=\"1\">17.16</amount>\r\n<amount nbchildren=\"2\">29.64</amount>\r\n<amount nbchildren=\"3\">31.20</amount>\r\n</monthly-contribution>\r\n</school-year>\r\n<school-year id=\"2014\">\r\n<monthly-contribution mode=\"MC_DEFAULT_MODE\">\r\n<amount nbchildren=\"1\">17.16</amount>\r\n<amount nbchildren=\"2\">29.64</amount>\r\n<amount nbchildren=\"3\">31.20</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_BENEFACTOR_MODE\">\r\n<amount nbchildren=\"1\">1.00</amount>\r\n<amount nbchildren=\"2\">13.48</amount>\r\n<amount nbchildren=\"3\">15.04</amount>\r\n</monthly-contribution>\r\n</school-year>\r\n<school-year id=\"2015\">\r\n<monthly-contribution mode=\"MC_DEFAULT_MODE\">\r\n<amount nbchildren=\"1\">17.16</amount>\r\n<amount nbchildren=\"2\">29.64</amount>\r\n<amount nbchildren=\"3\">31.20</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_BENEFACTOR_MODE\">\r\n<amount nbchildren=\"1\">1.00</amount>\r\n<amount nbchildren=\"2\">13.48</amount>\r\n<amount nbchildren=\"3\">15.04</amount>\r\n</monthly-contribution>\r\n</school-year>\r\n<school-year id=\"2016\">\r\n<monthly-contribution mode=\"MC_DEFAULT_MODE\">\r\n<amount nbchildren=\"1\">17.16</amount>\r\n<amount nbchildren=\"2\">29.64</amount>\r\n<amount nbchildren=\"3\">31.20</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_BENEFACTOR_MODE\">\r\n<amount nbchildren=\"1\">1.00</amount>\r\n<amount nbchildren=\"2\">13.48</amount>\r\n<amount nbchildren=\"3\">15.04</amount>\r\n</monthly-contribution>\r\n</school-year>\r\n<school-year id=\"2017\">\r\n<monthly-contribution mode=\"MC_DEFAULT_MODE\">\r\n<amount nbchildren=\"1\">25.00</amount>\r\n<amount nbchildren=\"2\">43.75</amount>\r\n<amount nbchildren=\"3\">56.25</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_FAMILY_COEFF_1_MODE\">\r\n<amount nbchildren=\"1\">17.50</amount>\r\n<amount nbchildren=\"2\">30.63</amount>\r\n<amount nbchildren=\"3\">39.38</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_FAMILY_COEFF_2_MODE\">\r\n<amount nbchildren=\"1\">20.00</amount>\r\n<amount nbchildren=\"2\">35.00</amount>\r\n<amount nbchildren=\"3\">45.00</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_FAMILY_COEFF_3_MODE\">\r\n<amount nbchildren=\"1\">22.55</amount>\r\n<amount nbchildren=\"2\">39.38</amount>\r\n<amount nbchildren=\"3\">50.63</amount>\r\n</monthly-contribution>\r\n</school-year>\r\n<school-year id=\"2018\">\r\n<monthly-contribution mode=\"MC_DEFAULT_MODE\">\r\n<amount nbchildren=\"1\">25.34</amount>\r\n<amount nbchildren=\"2\">44.34</amount>\r\n<amount nbchildren=\"3\">57.01</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_FAMILY_COEFF_1_MODE\">\r\n<amount nbchildren=\"1\">17.74</amount>\r\n<amount nbchildren=\"2\">31.04</amount>\r\n<amount nbchildren=\"3\">39.91</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_FAMILY_COEFF_2_MODE\">\r\n<amount nbchildren=\"1\">20.27</amount>\r\n<amount nbchildren=\"2\">35.47</amount>\r\n<amount nbchildren=\"3\">45.61</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_FAMILY_COEFF_3_MODE\">\r\n<amount nbchildren=\"1\">22.80</amount>\r\n<amount nbchildren=\"2\">39.91</amount>\r\n<amount nbchildren=\"3\">51.31</amount>\r\n</monthly-contribution>\r\n</school-year>\r\n<school-year id=\"2019\">\r\n<monthly-contribution mode=\"MC_DEFAULT_MODE\">\r\n<amount nbchildren=\"1\">25.69</amount>\r\n<amount nbchildren=\"2\">44.96</amount>\r\n<amount nbchildren=\"3\">57.81</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_FAMILY_COEFF_1_MODE\">\r\n<amount nbchildren=\"1\">17.99</amount>\r\n<amount nbchildren=\"2\">31.47</amount>\r\n<amount nbchildren=\"3\">40.47</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_FAMILY_COEFF_2_MODE\">\r\n<amount nbchildren=\"1\">20.55</amount>\r\n<amount nbchildren=\"2\">35.97</amount>\r\n<amount nbchildren=\"3\">46.25</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_FAMILY_COEFF_3_MODE\">\r\n<amount nbchildren=\"1\">23.12</amount>\r\n<amount nbchildren=\"2\">40.47</amount>\r\n<amount nbchildren=\"3\">52.03</amount>\r\n</monthly-contribution>\r\n</school-year>\r\n<school-year id=\"2020\">\r\n<monthly-contribution mode=\"MC_DEFAULT_MODE\">\r\n<amount nbchildren=\"1\">67.14</amount>\r\n<amount nbchildren=\"2\">107.09</amount>\r\n<amount nbchildren=\"3\">133.72</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_FAMILY_COEFF_1_MODE\">\r\n<amount nbchildren=\"1\">30.52</amount>\r\n<amount nbchildren=\"2\">48.68</amount>\r\n<amount nbchildren=\"3\">60.78</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_FAMILY_COEFF_2_MODE\">\r\n<amount nbchildren=\"1\">39.67</amount>\r\n<amount nbchildren=\"2\">63.28</amount>\r\n<amount nbchildren=\"3\">79.02</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_FAMILY_COEFF_3_MODE\">\r\n<amount nbchildren=\"1\">57.95</amount>\r\n<amount nbchildren=\"2\">92.44</amount>\r\n<amount nbchildren=\"3\">115.43</amount>\r\n</monthly-contribution>\r\n</school-year>\r\n<school-year id=\"2021\">\r\n<monthly-contribution mode=\"MC_DEFAULT_MODE\">\r\n<amount nbchildren=\"1\">68.15</amount>\r\n<amount nbchildren=\"2\">108.70</amount>\r\n<amount nbchildren=\"3\">135.73</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_FAMILY_COEFF_1_MODE\">\r\n<amount nbchildren=\"1\">30.98</amount>\r\n<amount nbchildren=\"2\">49.41</amount>\r\n<amount nbchildren=\"3\">61.69</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_FAMILY_COEFF_2_MODE\">\r\n<amount nbchildren=\"1\">40.26</amount>\r\n<amount nbchildren=\"2\">64.23</amount>\r\n<amount nbchildren=\"3\">70.21</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_FAMILY_COEFF_3_MODE\">\r\n<amount nbchildren=\"1\">58.82</amount>\r\n<amount nbchildren=\"2\">93.83</amount>\r\n<amount nbchildren=\"3\">117.16</amount>\r\n</monthly-contribution>\r\n</school-year>\r\n<school-year id=\"2022\">\r\n<monthly-contribution mode=\"MC_DEFAULT_MODE\">\r\n<amount nbchildren=\"1\">68.15</amount>\r\n<amount nbchildren=\"2\">108.70</amount>\r\n<amount nbchildren=\"3\">135.73</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_FAMILY_COEFF_1_MODE\">\r\n<amount nbchildren=\"1\">30.98</amount>\r\n<amount nbchildren=\"2\">49.41</amount>\r\n<amount nbchildren=\"3\">61.69</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_FAMILY_COEFF_2_MODE\">\r\n<amount nbchildren=\"1\">40.26</amount>\r\n<amount nbchildren=\"2\">64.23</amount>\r\n<amount nbchildren=\"3\">70.21</amount>\r\n</monthly-contribution>\r\n<monthly-contribution mode=\"MC_FAMILY_COEFF_3_MODE\">\r\n<amount nbchildren=\"1\">58.82</amount>\r\n<amount nbchildren=\"2\">93.83</amount>\r\n<amount nbchildren=\"3\">117.16</amount>\r\n</monthly-contribution>\r\n</school-year>\r\n</config-parameters>'),
(7, 'CONF_NURSERY_DELAYS_PRICES', 'xml', '<config-parameters>\r\n<school-year id=\"2010\"></school-year>\r\n<school-year id=\"2011\"></school-year>\r\n<school-year id=\"2012\"></school-year>\r\n<school-year id=\"2013\"></school-year>\r\n<school-year id=\"2014\">\r\n<nursery-delay-price nbdelays=\"1\">11.80</nursery-delay-price>\r\n<nursery-delay-price nbdelays=\"2\">11.80</nursery-delay-price>\r\n</school-year>\r\n<school-year id=\"2015\">\r\n<nursery-delay-price nbdelays=\"1\">11.80</nursery-delay-price>\r\n<nursery-delay-price nbdelays=\"2\">11.80</nursery-delay-price>\r\n</school-year>\r\n<school-year id=\"2016\">\r\n<nursery-delay-price nbdelays=\"1\">11.80</nursery-delay-price>\r\n<nursery-delay-price nbdelays=\"2\">11.80</nursery-delay-price>\r\n</school-year>\r\n<school-year id=\"2017\">\r\n<nursery-delay-price nbdelays=\"1\">11.80</nursery-delay-price>\r\n<nursery-delay-price nbdelays=\"2\">11.80</nursery-delay-price>\r\n</school-year>\r\n<school-year id=\"2018\">\r\n<nursery-delay-price nbdelays=\"1\">11.80</nursery-delay-price>\r\n<nursery-delay-price nbdelays=\"2\">11.80</nursery-delay-price>\r\n</school-year>\r\n<school-year id=\"2019\">\r\n<nursery-delay-price nbdelays=\"1\">11.80</nursery-delay-price>\r\n<nursery-delay-price nbdelays=\"2\">11.80</nursery-delay-price>\r\n</school-year>\r\n<school-year id=\"2020\">\r\n<nursery-delay-price nbdelays=\"1\">11.80</nursery-delay-price>\r\n<nursery-delay-price nbdelays=\"2\">11.80</nursery-delay-price>\r\n</school-year>\r\n<school-year id=\"2021\">\r\n<nursery-delay-price nbdelays=\"1\">11.80</nursery-delay-price>\r\n<nursery-delay-price nbdelays=\"2\">11.80</nursery-delay-price>\r\n</school-year>\r\n<school-year id=\"2022\">\r\n<nursery-delay-price nbdelays=\"1\">11.80</nursery-delay-price>\r\n<nursery-delay-price nbdelays=\"2\">11.80</nursery-delay-price>\r\n</school-year>\r\n</config-parameters>'),
(9, 'CONF_TEST_VAR_XML', 'xml', '<config-parameters>\r\n<year id=\"2015\">\r\n<Template idtype=\"const\">fichedescriptiveformulaire_5952.pdf</Template>\r\n<Language idtype=\"const\">fr-FR</Language>\r\n<Unit idtype=\"const\">EUR</Unit>\r\n<Page idtype=\"const\">\r\n<Page id=\"1\">\r\n<part id=\"Recipient\" idtype=\"const\">\r\n<field id=\"Name\">\r\n<Text idtype=\"const\">Calandreta Del Païs Murethin</Text>\r\n<PosX idtype=\"const\">52</PosX>\r\n<PosY idtype=\"const\">33</PosY>\r\n</field>\r\n</part>\r\n</Page>\r\n<Page id=\"2\">\r\n<part id=\"Donator\" idtype=\"const\">\r\n<field id=\"Nature\">\r\n<list keep=\"0\">\r\n<item id=\"Numéraire\">\r\n<Items idtype=\"const\" type=\"array\">\r\n<value keep=\"0\">0</value>\r\n</Items>\r\n<Text idtype=\"const\">X</Text>\r\n<PosX idtype=\"const\">9.7</PosX>\r\n<PosY idtype=\"const\">133.7</PosY>\r\n</item>\r\n<item id=\"Titres de sociétés côtés\">\r\n<Items idtype=\"const\" type=\"array\"></Items>\r\n<Text idtype=\"const\">X</Text>\r\n<PosX idtype=\"const\">52.3</PosX>\r\n<PosY idtype=\"const\">133.7</PosY>\r\n</item>\r\n<item id=\"Autres\">\r\n<Items idtype=\"const\" type=\"array\">\r\n<value keep=\"0\">1</value>\r\n<value keep=\"0\">2</value>\r\n</Items>\r\n<Text idtype=\"const\">X</Text>\r\n<PosX idtype=\"const\">110</PosX>\r\n<PosY idtype=\"const\">133.7</PosY>\r\n</item>\r\n</list>\r\n</field>\r\n</part>\r\n</Page>\r\n</Page>\r\n</year>\r\n</config-parameters>'),
(8, 'CONF_DONATION_TAX_RECEIPT_PARAMETERS', 'xml', '<config-parameters>\r\n<year id=\"2017\">\r\n<Template>fichedescriptiveformulaire_5952.pdf</Template>\r\n<Language>fr-FR</Language>\r\n<Unit>EUR</Unit>\r\n<pages>\r\n<Page id=\"1\">\r\n<part id=\"Recipient\">\r\n<field id=\"Reference\">\r\n<PosX>170</PosX>\r\n<PosY>8</PosY>\r\n</field>\r\n<field id=\"Name\">\r\n<Text>Calandreta Del Païs Murethin</Text>\r\n<PosX>52</PosX>\r\n<PosY>33</PosY>\r\n</field>\r\n<field id=\"StreetNum\">\r\n<Text>Avenue du Maréchal Lyautey</Text>\r\n<PosX>36</PosX>\r\n<PosY>44</PosY>\r\n</field>\r\n<field id=\"ZipCode\">\r\n<Text>31600</Text>\r\n<PosX>31</PosX>\r\n<PosY>50</PosY>\r\n</field>\r\n<field id=\"TownName\">\r\n<Text>Muret</Text>\r\n<PosX>70</PosX>\r\n<PosY>50</PosY>\r\n</field>\r\n<field id=\"Subject\">\r\n<Text>Enseignement en occitan de la TPS au CM2.</Text>\r\n<PosX>25</PosX>\r\n<PosY>60</PosY>\r\n</field>\r\n<field id=\"Organization\">\r\n<Text>X</Text>\r\n<PosX>9.7</PosX>\r\n<PosY>126.3</PosY>\r\n</field>\r\n</part>\r\n</Page>\r\n<Page id=\"2\">\r\n<part id=\"Donator\">\r\n<field id=\"Lastname\">\r\n<PosX>21</PosX>\r\n<PosY>16</PosY>\r\n</field>\r\n<field id=\"Firstname\">\r\n<PosX>119</PosX>\r\n<PosY>16</PosY>\r\n</field>\r\n<field id=\"Address\">\r\n<PosX>26</PosX>\r\n<PosY>29</PosY>\r\n</field>\r\n<field id=\"ZipCode\">\r\n<PosX>31</PosX>\r\n<PosY>35</PosY>\r\n</field>\r\n<field id=\"TownName\">\r\n<PosX>72</PosX>\r\n<PosY>35</PosY>\r\n</field>\r\n<field id=\"Amount\">\r\n<PosX>77</PosX>\r\n<PosY>60.5</PosY>\r\n</field>\r\n<field id=\"AmountInLetters\">\r\n<PosX>54</PosX>\r\n<PosY>70.5</PosY>\r\n</field>\r\n<field id=\"ReceptionDateDay\">\r\n<PosX>62</PosX>\r\n<PosY>79.5</PosY>\r\n</field>\r\n<field id=\"ReceptionDateMonth\">\r\n<PosX>76</PosX>\r\n<PosY>79.5</PosY>\r\n</field>\r\n<field id=\"ReceptionDateYear\">\r\n<PosX>92</PosX>\r\n<PosY>79.5</PosY>\r\n</field>\r\n<field id=\"Entity\">\r\n<list>\r\n<item id=\"200 du CGI\">\r\n<Items>\r\n<value>0</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>47.5</PosX>\r\n<PosY>93.7</PosY>\r\n</item>\r\n<item id=\"238 bis du CGI\">\r\n<Items>\r\n<value>1</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>97.3</PosX>\r\n<PosY>93.7</PosY>\r\n</item>\r\n<item id=\"885-0 V bis A du CGI\">\r\n<Items></Items>\r\n<Text>X</Text>\r\n<PosX>147.5</PosX>\r\n<PosY>93.7</PosY>\r\n</item>\r\n</list>\r\n</field>\r\n<field id=\"Type\">\r\n<Text>X</Text>\r\n<PosX>110</PosX>\r\n<PosY>111.5</PosY>\r\n</field>\r\n<field id=\"Nature\">\r\n<list>\r\n<item id=\"Numéraire\">\r\n<Items>\r\n<value>0</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>9.7</PosX>\r\n<PosY>133.7</PosY>\r\n</item>\r\n<item id=\"Titres de sociétés côtés\">\r\n<Items></Items>\r\n<Text>X</Text>\r\n<PosX>52.3</PosX>\r\n<PosY>133.7</PosY>\r\n</item>\r\n<item id=\"Autres\">\r\n<Items>\r\n<value>1</value>\r\n<value>2</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>110</PosX>\r\n<PosY>133.7</PosY>\r\n</item>\r\n</list>\r\n</field>\r\n<field id=\"PaymentMode\">\r\n<list>\r\n<item id=\"Remise d\'espèces\">\r\n<Items>\r\n<value>0</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>9.7</PosX>\r\n<PosY>156.1</PosY>\r\n</item>\r\n<item id=\"Chèque\">\r\n<Items>\r\n<value>1</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>52.3</PosX>\r\n<PosY>156.1</PosY>\r\n</item>\r\n<item id=\"Virement, prélèvement, carte bancaire\">\r\n<Items>\r\n<value>2</value>\r\n<value>3</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>110</PosX>\r\n<PosY>156.1</PosY>\r\n</item>\r\n</list>\r\n</field>\r\n<field id=\"TaxReceiptDateDay\">\r\n<PosX>134</PosX>\r\n<PosY>236.5</PosY>\r\n</field>\r\n<field id=\"TaxReceiptDateMonth\">\r\n<PosX>142</PosX>\r\n<PosY>236.5</PosY>\r\n</field>\r\n<field id=\"TaxReceiptDateYear\">\r\n<PosX>150</PosX>\r\n<PosY>236.5</PosY>\r\n</field>\r\n<field id=\"SignIn\">\r\n<Text>TamponCalandretaTr.png</Text>\r\n<PosX>123</PosX>\r\n<PosY>237</PosY>\r\n<DimWidth>70</DimWidth>\r\n</field>\r\n</part>\r\n</Page>\r\n</pages>\r\n</year>\r\n<year id=\"2018\">\r\n<Template>fichedescriptiveformulaire_5952.pdf</Template>\r\n<Language>fr-FR</Language>\r\n<Unit>EUR</Unit>\r\n<pages>\r\n<Page id=\"1\">\r\n<part id=\"Recipient\">\r\n<field id=\"Reference\">\r\n<PosX>170</PosX>\r\n<PosY>8</PosY>\r\n</field>\r\n<field id=\"Name\">\r\n<Text>Calandreta Del Païs Murethin</Text>\r\n<PosX>52</PosX>\r\n<PosY>33</PosY>\r\n</field>\r\n<field id=\"StreetNum\">\r\n<Text>Avenue du Maréchal Lyautey</Text>\r\n<PosX>36</PosX>\r\n<PosY>44</PosY>\r\n</field>\r\n<field id=\"ZipCode\">\r\n<Text>31600</Text>\r\n<PosX>31</PosX>\r\n<PosY>50</PosY>\r\n</field>\r\n<field id=\"TownName\">\r\n<Text>Muret</Text>\r\n<PosX>70</PosX>\r\n<PosY>50</PosY>\r\n</field>\r\n<field id=\"Subject\">\r\n<Text>Enseignement en occitan de la TPS au CM2.</Text>\r\n<PosX>25</PosX>\r\n<PosY>60</PosY>\r\n</field>\r\n<field id=\"Organization\">\r\n<Text>X</Text>\r\n<PosX>9.7</PosX>\r\n<PosY>126.3</PosY>\r\n</field>\r\n</part>\r\n</Page>\r\n<Page id=\"2\">\r\n<part id=\"Donator\">\r\n<field id=\"Lastname\">\r\n<PosX>21</PosX>\r\n<PosY>16</PosY>\r\n</field>\r\n<field id=\"Firstname\">\r\n<PosX>119</PosX>\r\n<PosY>16</PosY>\r\n</field>\r\n<field id=\"Address\">\r\n<PosX>26</PosX>\r\n<PosY>29</PosY>\r\n</field>\r\n<field id=\"ZipCode\">\r\n<PosX>31</PosX>\r\n<PosY>35</PosY>\r\n</field>\r\n<field id=\"TownName\">\r\n<PosX>72</PosX>\r\n<PosY>35</PosY>\r\n</field>\r\n<field id=\"Amount\">\r\n<PosX>77</PosX>\r\n<PosY>60.5</PosY>\r\n</field>\r\n<field id=\"AmountInLetters\">\r\n<PosX>54</PosX>\r\n<PosY>70.5</PosY>\r\n</field>\r\n<field id=\"ReceptionDateDay\">\r\n<PosX>62</PosX>\r\n<PosY>79.5</PosY>\r\n</field>\r\n<field id=\"ReceptionDateMonth\">\r\n<PosX>76</PosX>\r\n<PosY>79.5</PosY>\r\n</field>\r\n<field id=\"ReceptionDateYear\">\r\n<PosX>92</PosX>\r\n<PosY>79.5</PosY>\r\n</field>\r\n<field id=\"Entity\">\r\n<list>\r\n<item id=\"200 du CGI\">\r\n<Items>\r\n<value>0</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>47.5</PosX>\r\n<PosY>93.7</PosY>\r\n</item>\r\n<item id=\"238 bis du CGI\">\r\n<Items>\r\n<value>1</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>97.3</PosX>\r\n<PosY>93.7</PosY>\r\n</item>\r\n<item id=\"885-0 V bis A du CGI\">\r\n<Items></Items>\r\n<Text>X</Text>\r\n<PosX>147.5</PosX>\r\n<PosY>93.7</PosY>\r\n</item>\r\n</list>\r\n</field>\r\n<field id=\"Type\">\r\n<Text>X</Text>\r\n<PosX>110</PosX>\r\n<PosY>111.5</PosY>\r\n</field>\r\n<field id=\"Nature\">\r\n<list>\r\n<item id=\"Numéraire\">\r\n<Items>\r\n<value>0</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>9.7</PosX>\r\n<PosY>133.7</PosY>\r\n</item>\r\n<item id=\"Titres de sociétés côtés\">\r\n<Items></Items>\r\n<Text>X</Text>\r\n<PosX>52.3</PosX>\r\n<PosY>133.7</PosY>\r\n</item>\r\n<item id=\"Autres\">\r\n<Items>\r\n<value>1</value>\r\n<value>2</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>110</PosX>\r\n<PosY>133.7</PosY>\r\n</item>\r\n</list>\r\n</field>\r\n<field id=\"PaymentMode\">\r\n<list>\r\n<item id=\"Remise d\'espèces\">\r\n<Items>\r\n<value>0</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>9.7</PosX>\r\n<PosY>156.1</PosY>\r\n</item>\r\n<item id=\"Chèque\">\r\n<Items>\r\n<value>1</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>52.3</PosX>\r\n<PosY>156.1</PosY>\r\n</item>\r\n<item id=\"Virement, prélèvement, carte bancaire\">\r\n<Items>\r\n<value>2</value>\r\n<value>3</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>110</PosX>\r\n<PosY>156.1</PosY>\r\n</item>\r\n</list>\r\n</field>\r\n<field id=\"TaxReceiptDateDay\">\r\n<PosX>134</PosX>\r\n<PosY>236.5</PosY>\r\n</field>\r\n<field id=\"TaxReceiptDateMonth\">\r\n<PosX>142</PosX>\r\n<PosY>236.5</PosY>\r\n</field>\r\n<field id=\"TaxReceiptDateYear\">\r\n<PosX>150</PosX>\r\n<PosY>236.5</PosY>\r\n</field>\r\n<field id=\"SignIn\">\r\n<Text>TamponCalandretaTr.png</Text>\r\n<PosX>123</PosX>\r\n<PosY>237</PosY>\r\n<DimWidth>70</DimWidth>\r\n</field>\r\n</part>\r\n</Page>\r\n</pages>\r\n</year>\r\n<year id=\"2019\">\r\n<Template>fichedescriptiveformulaire_5952.pdf</Template>\r\n<Language>fr-FR</Language>\r\n<Unit>EUR</Unit>\r\n<pages>\r\n<Page id=\"1\">\r\n<part id=\"Recipient\">\r\n<field id=\"Reference\">\r\n<PosX>170</PosX>\r\n<PosY>8</PosY>\r\n</field>\r\n<field id=\"Name\">\r\n<Text>Calandreta Del Païs Murethin</Text>\r\n<PosX>52</PosX>\r\n<PosY>33</PosY>\r\n</field>\r\n<field id=\"StreetNum\">\r\n<Text>Avenue du Maréchal Lyautey</Text>\r\n<PosX>36</PosX>\r\n<PosY>44</PosY>\r\n</field>\r\n<field id=\"ZipCode\">\r\n<Text>31600</Text>\r\n<PosX>31</PosX>\r\n<PosY>50</PosY>\r\n</field>\r\n<field id=\"TownName\">\r\n<Text>Muret</Text>\r\n<PosX>70</PosX>\r\n<PosY>50</PosY>\r\n</field>\r\n<field id=\"Subject\">\r\n<Text>Enseignement en occitan de la TPS au CM2.</Text>\r\n<PosX>25</PosX>\r\n<PosY>60</PosY>\r\n</field>\r\n<field id=\"Organization\">\r\n<Text>X</Text>\r\n<PosX>9.7</PosX>\r\n<PosY>126.3</PosY>\r\n</field>\r\n</part>\r\n</Page>\r\n<Page id=\"2\">\r\n<part id=\"Donator\">\r\n<field id=\"Lastname\">\r\n<PosX>21</PosX>\r\n<PosY>16</PosY>\r\n</field>\r\n<field id=\"Firstname\">\r\n<PosX>119</PosX>\r\n<PosY>16</PosY>\r\n</field>\r\n<field id=\"Address\">\r\n<PosX>26</PosX>\r\n<PosY>29</PosY>\r\n</field>\r\n<field id=\"ZipCode\">\r\n<PosX>31</PosX>\r\n<PosY>35</PosY>\r\n</field>\r\n<field id=\"TownName\">\r\n<PosX>72</PosX>\r\n<PosY>35</PosY>\r\n</field>\r\n<field id=\"Amount\">\r\n<PosX>77</PosX>\r\n<PosY>60.5</PosY>\r\n</field>\r\n<field id=\"AmountInLetters\">\r\n<PosX>54</PosX>\r\n<PosY>70.5</PosY>\r\n</field>\r\n<field id=\"ReceptionDateDay\">\r\n<PosX>62</PosX>\r\n<PosY>79.5</PosY>\r\n</field>\r\n<field id=\"ReceptionDateMonth\">\r\n<PosX>76</PosX>\r\n<PosY>79.5</PosY>\r\n</field>\r\n<field id=\"ReceptionDateYear\">\r\n<PosX>92</PosX>\r\n<PosY>79.5</PosY>\r\n</field>\r\n<field id=\"Entity\">\r\n<list>\r\n<item id=\"200 du CGI\">\r\n<Items>\r\n<value>0</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>47.5</PosX>\r\n<PosY>93.7</PosY>\r\n</item>\r\n<item id=\"238 bis du CGI\">\r\n<Items>\r\n<value>1</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>97.3</PosX>\r\n<PosY>93.7</PosY>\r\n</item>\r\n<item id=\"885-0 V bis A du CGI\">\r\n<Items></Items>\r\n<Text>X</Text>\r\n<PosX>147.5</PosX>\r\n<PosY>93.7</PosY>\r\n</item>\r\n</list>\r\n</field>\r\n<field id=\"Type\">\r\n<Text>X</Text>\r\n<PosX>110</PosX>\r\n<PosY>111.5</PosY>\r\n</field>\r\n<field id=\"Nature\">\r\n<list>\r\n<item id=\"Numéraire\">\r\n<Items>\r\n<value>0</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>9.7</PosX>\r\n<PosY>133.7</PosY>\r\n</item>\r\n<item id=\"Titres de sociétés côtés\">\r\n<Items></Items>\r\n<Text>X</Text>\r\n<PosX>52.3</PosX>\r\n<PosY>133.7</PosY>\r\n</item>\r\n<item id=\"Autres\">\r\n<Items>\r\n<value>1</value>\r\n<value>2</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>110</PosX>\r\n<PosY>133.7</PosY>\r\n</item>\r\n</list>\r\n</field>\r\n<field id=\"PaymentMode\">\r\n<list>\r\n<item id=\"Remise d\'espèces\">\r\n<Items>\r\n<value>0</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>9.7</PosX>\r\n<PosY>156.1</PosY>\r\n</item>\r\n<item id=\"Chèque\">\r\n<Items>\r\n<value>1</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>52.3</PosX>\r\n<PosY>156.1</PosY>\r\n</item>\r\n<item id=\"Virement, prélèvement, carte bancaire\">\r\n<Items>\r\n<value>2</value>\r\n<value>3</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>110</PosX>\r\n<PosY>156.1</PosY>\r\n</item>\r\n</list>\r\n</field>\r\n<field id=\"TaxReceiptDateDay\">\r\n<PosX>134</PosX>\r\n<PosY>236.5</PosY>\r\n</field>\r\n<field id=\"TaxReceiptDateMonth\">\r\n<PosX>142</PosX>\r\n<PosY>236.5</PosY>\r\n</field>\r\n<field id=\"TaxReceiptDateYear\">\r\n<PosX>150</PosX>\r\n<PosY>236.5</PosY>\r\n</field>\r\n<field id=\"SignIn\">\r\n<Text>TamponCalandretaTr.png</Text>\r\n<PosX>123</PosX>\r\n<PosY>237</PosY>\r\n<DimWidth>70</DimWidth>\r\n</field>\r\n</part>\r\n</Page>\r\n</pages>\r\n</year>\r\n<year id=\"2020\">\r\n<Template>fichedescriptiveformulaire_5952.pdf</Template>\r\n<Language>fr-FR</Language>\r\n<Unit>EUR</Unit>\r\n<pages>\r\n<Page id=\"1\">\r\n<part id=\"Recipient\">\r\n<field id=\"Reference\">\r\n<PosX>170</PosX>\r\n<PosY>8</PosY>\r\n</field>\r\n<field id=\"Name\">\r\n<Text>Calandreta Del Païs Murethin</Text>\r\n<PosX>52</PosX>\r\n<PosY>33</PosY>\r\n</field>\r\n<field id=\"StreetNum\">\r\n<Text>Avenue du Maréchal Lyautey</Text>\r\n<PosX>36</PosX>\r\n<PosY>44</PosY>\r\n</field>\r\n<field id=\"ZipCode\">\r\n<Text>31600</Text>\r\n<PosX>31</PosX>\r\n<PosY>50</PosY>\r\n</field>\r\n<field id=\"TownName\">\r\n<Text>Muret</Text>\r\n<PosX>70</PosX>\r\n<PosY>50</PosY>\r\n</field>\r\n<field id=\"Subject\">\r\n<Text>Enseignement en occitan de la TPS au CM2.</Text>\r\n<PosX>25</PosX>\r\n<PosY>60</PosY>\r\n</field>\r\n<field id=\"Organization\">\r\n<Text>X</Text>\r\n<PosX>9.7</PosX>\r\n<PosY>126.3</PosY>\r\n</field>\r\n</part>\r\n</Page>\r\n<Page id=\"2\">\r\n<part id=\"Donator\">\r\n<field id=\"Lastname\">\r\n<PosX>21</PosX>\r\n<PosY>16</PosY>\r\n</field>\r\n<field id=\"Firstname\">\r\n<PosX>119</PosX>\r\n<PosY>16</PosY>\r\n</field>\r\n<field id=\"Address\">\r\n<PosX>26</PosX>\r\n<PosY>29</PosY>\r\n</field>\r\n<field id=\"ZipCode\">\r\n<PosX>31</PosX>\r\n<PosY>35</PosY>\r\n</field>\r\n<field id=\"TownName\">\r\n<PosX>72</PosX>\r\n<PosY>35</PosY>\r\n</field>\r\n<field id=\"Amount\">\r\n<PosX>77</PosX>\r\n<PosY>60.5</PosY>\r\n</field>\r\n<field id=\"AmountInLetters\">\r\n<PosX>54</PosX>\r\n<PosY>70.5</PosY>\r\n</field>\r\n<field id=\"ReceptionDateDay\">\r\n<PosX>62</PosX>\r\n<PosY>79.5</PosY>\r\n</field>\r\n<field id=\"ReceptionDateMonth\">\r\n<PosX>76</PosX>\r\n<PosY>79.5</PosY>\r\n</field>\r\n<field id=\"ReceptionDateYear\">\r\n<PosX>92</PosX>\r\n<PosY>79.5</PosY>\r\n</field>\r\n<field id=\"Entity\">\r\n<list>\r\n<item id=\"200 du CGI\">\r\n<Items>\r\n<value>0</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>47.5</PosX>\r\n<PosY>93.7</PosY>\r\n</item>\r\n<item id=\"238 bis du CGI\">\r\n<Items>\r\n<value>1</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>97.3</PosX>\r\n<PosY>93.7</PosY>\r\n</item>\r\n<item id=\"885-0 V bis A du CGI\">\r\n<Items></Items>\r\n<Text>X</Text>\r\n<PosX>147.5</PosX>\r\n<PosY>93.7</PosY>\r\n</item>\r\n</list>\r\n</field>\r\n<field id=\"Type\">\r\n<Text>X</Text>\r\n<PosX>110</PosX>\r\n<PosY>111.5</PosY>\r\n</field>\r\n<field id=\"Nature\">\r\n<list>\r\n<item id=\"Numéraire\">\r\n<Items>\r\n<value>0</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>9.7</PosX>\r\n<PosY>133.7</PosY>\r\n</item>\r\n<item id=\"Titres de sociétés côtés\">\r\n<Items></Items>\r\n<Text>X</Text>\r\n<PosX>52.3</PosX>\r\n<PosY>133.7</PosY>\r\n</item>\r\n<item id=\"Autres\">\r\n<Items>\r\n<value>1</value>\r\n<value>2</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>110</PosX>\r\n<PosY>133.7</PosY>\r\n</item>\r\n</list>\r\n</field>\r\n<field id=\"PaymentMode\">\r\n<list>\r\n<item id=\"Remise d\'espèces\">\r\n<Items>\r\n<value>0</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>9.7</PosX>\r\n<PosY>156.1</PosY>\r\n</item>\r\n<item id=\"Chèque\">\r\n<Items>\r\n<value>1</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>52.3</PosX>\r\n<PosY>156.1</PosY>\r\n</item>\r\n<item id=\"Virement, prélèvement, carte bancaire\">\r\n<Items>\r\n<value>2</value>\r\n<value>3</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>110</PosX>\r\n<PosY>156.1</PosY>\r\n</item>\r\n</list>\r\n</field>\r\n<field id=\"TaxReceiptDateDay\">\r\n<PosX>134</PosX>\r\n<PosY>236.5</PosY>\r\n</field>\r\n<field id=\"TaxReceiptDateMonth\">\r\n<PosX>142</PosX>\r\n<PosY>236.5</PosY>\r\n</field>\r\n<field id=\"TaxReceiptDateYear\">\r\n<PosX>150</PosX>\r\n<PosY>236.5</PosY>\r\n</field>\r\n<field id=\"SignIn\">\r\n<Text>TamponCalandretaTr.png</Text>\r\n<PosX>123</PosX>\r\n<PosY>237</PosY>\r\n<DimWidth>70</DimWidth>\r\n</field>\r\n</part>\r\n</Page>\r\n</pages>\r\n</year>\r\n<year id=\"2021\">\r\n<Template>fichedescriptiveformulaire_5952.pdf</Template>\r\n<Language>fr-FR</Language>\r\n<Unit>EUR</Unit>\r\n<pages>\r\n<Page id=\"1\">\r\n<part id=\"Recipient\">\r\n<field id=\"Reference\">\r\n<PosX>170</PosX>\r\n<PosY>8</PosY>\r\n</field>\r\n<field id=\"Name\">\r\n<Text>Calandreta Del Païs Murethin</Text>\r\n<PosX>52</PosX>\r\n<PosY>33</PosY>\r\n</field>\r\n<field id=\"StreetNum\">\r\n<Text>Avenue du Maréchal Lyautey</Text>\r\n<PosX>36</PosX>\r\n<PosY>44</PosY>\r\n</field>\r\n<field id=\"ZipCode\">\r\n<Text>31600</Text>\r\n<PosX>31</PosX>\r\n<PosY>50</PosY>\r\n</field>\r\n<field id=\"TownName\">\r\n<Text>Muret</Text>\r\n<PosX>70</PosX>\r\n<PosY>50</PosY>\r\n</field>\r\n<field id=\"Subject\">\r\n<Text>Enseignement en occitan de la TPS au CM2.</Text>\r\n<PosX>25</PosX>\r\n<PosY>60</PosY>\r\n</field>\r\n<field id=\"Organization\">\r\n<Text>X</Text>\r\n<PosX>9.7</PosX>\r\n<PosY>126.3</PosY>\r\n</field>\r\n</part>\r\n</Page>\r\n<Page id=\"2\">\r\n<part id=\"Donator\">\r\n<field id=\"Lastname\">\r\n<PosX>21</PosX>\r\n<PosY>16</PosY>\r\n</field>\r\n<field id=\"Firstname\">\r\n<PosX>119</PosX>\r\n<PosY>16</PosY>\r\n</field>\r\n<field id=\"Address\">\r\n<PosX>26</PosX>\r\n<PosY>29</PosY>\r\n</field>\r\n<field id=\"ZipCode\">\r\n<PosX>31</PosX>\r\n<PosY>35</PosY>\r\n</field>\r\n<field id=\"TownName\">\r\n<PosX>72</PosX>\r\n<PosY>35</PosY>\r\n</field>\r\n<field id=\"Amount\">\r\n<PosX>77</PosX>\r\n<PosY>60.5</PosY>\r\n</field>\r\n<field id=\"AmountInLetters\">\r\n<PosX>54</PosX>\r\n<PosY>70.5</PosY>\r\n</field>\r\n<field id=\"ReceptionDateDay\">\r\n<PosX>62</PosX>\r\n<PosY>79.5</PosY>\r\n</field>\r\n<field id=\"ReceptionDateMonth\">\r\n<PosX>76</PosX>\r\n<PosY>79.5</PosY>\r\n</field>\r\n<field id=\"ReceptionDateYear\">\r\n<PosX>92</PosX>\r\n<PosY>79.5</PosY>\r\n</field>\r\n<field id=\"Entity\">\r\n<list>\r\n<item id=\"200 du CGI\">\r\n<Items>\r\n<value>0</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>47.5</PosX>\r\n<PosY>93.7</PosY>\r\n</item>\r\n<item id=\"238 bis du CGI\">\r\n<Items>\r\n<value>1</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>97.3</PosX>\r\n<PosY>93.7</PosY>\r\n</item>\r\n<item id=\"885-0 V bis A du CGI\">\r\n<Items></Items>\r\n<Text>X</Text>\r\n<PosX>147.5</PosX>\r\n<PosY>93.7</PosY>\r\n</item>\r\n</list>\r\n</field>\r\n<field id=\"Type\">\r\n<Text>X</Text>\r\n<PosX>110</PosX>\r\n<PosY>111.5</PosY>\r\n</field>\r\n<field id=\"Nature\">\r\n<list>\r\n<item id=\"Numéraire\">\r\n<Items>\r\n<value>0</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>9.7</PosX>\r\n<PosY>133.7</PosY>\r\n</item>\r\n<item id=\"Titres de sociétés côtés\">\r\n<Items></Items>\r\n<Text>X</Text>\r\n<PosX>52.3</PosX>\r\n<PosY>133.7</PosY>\r\n</item>\r\n<item id=\"Autres\">\r\n<Items>\r\n<value>1</value>\r\n<value>2</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>110</PosX>\r\n<PosY>133.7</PosY>\r\n</item>\r\n</list>\r\n</field>\r\n<field id=\"PaymentMode\">\r\n<list>\r\n<item id=\"Remise d\'espèces\">\r\n<Items>\r\n<value>0</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>9.7</PosX>\r\n<PosY>156.1</PosY>\r\n</item>\r\n<item id=\"Chèque\">\r\n<Items>\r\n<value>1</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>52.3</PosX>\r\n<PosY>156.1</PosY>\r\n</item>\r\n<item id=\"Virement, prélèvement, carte bancaire\">\r\n<Items>\r\n<value>2</value>\r\n<value>3</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>110</PosX>\r\n<PosY>156.1</PosY>\r\n</item>\r\n</list>\r\n</field>\r\n<field id=\"TaxReceiptDateDay\">\r\n<PosX>134</PosX>\r\n<PosY>236.5</PosY>\r\n</field>\r\n<field id=\"TaxReceiptDateMonth\">\r\n<PosX>142</PosX>\r\n<PosY>236.5</PosY>\r\n</field>\r\n<field id=\"TaxReceiptDateYear\">\r\n<PosX>150</PosX>\r\n<PosY>236.5</PosY>\r\n</field>\r\n<field id=\"SignIn\">\r\n<Text>TamponCalandretaTr.png</Text>\r\n<PosX>123</PosX>\r\n<PosY>237</PosY>\r\n<DimWidth>70</DimWidth>\r\n</field>\r\n</part>\r\n</Page>\r\n</pages>\r\n</year>\r\n<year id=\"2022\">\r\n<Template>fichedescriptiveformulaire_5952.pdf</Template>\r\n<Language>fr-FR</Language>\r\n<Unit>EUR</Unit>\r\n<pages>\r\n<Page id=\"1\">\r\n<part id=\"Recipient\">\r\n<field id=\"Reference\">\r\n<PosX>170</PosX>\r\n<PosY>8</PosY>\r\n</field>\r\n<field id=\"Name\">\r\n<Text>Calandreta Del Païs Murethin</Text>\r\n<PosX>52</PosX>\r\n<PosY>33</PosY>\r\n</field>\r\n<field id=\"StreetNum\">\r\n<Text>Avenue du Maréchal Lyautey</Text>\r\n<PosX>36</PosX>\r\n<PosY>44</PosY>\r\n</field>\r\n<field id=\"ZipCode\">\r\n<Text>31600</Text>\r\n<PosX>31</PosX>\r\n<PosY>50</PosY>\r\n</field>\r\n<field id=\"TownName\">\r\n<Text>Muret</Text>\r\n<PosX>70</PosX>\r\n<PosY>50</PosY>\r\n</field>\r\n<field id=\"Subject\">\r\n<Text>Enseignement en occitan de la TPS au CM2.</Text>\r\n<PosX>25</PosX>\r\n<PosY>60</PosY>\r\n</field>\r\n<field id=\"Organization\">\r\n<Text>X</Text>\r\n<PosX>9.7</PosX>\r\n<PosY>126.3</PosY>\r\n</field>\r\n</part>\r\n</Page>\r\n<Page id=\"2\">\r\n<part id=\"Donator\">\r\n<field id=\"Lastname\">\r\n<PosX>21</PosX>\r\n<PosY>16</PosY>\r\n</field>\r\n<field id=\"Firstname\">\r\n<PosX>119</PosX>\r\n<PosY>16</PosY>\r\n</field>\r\n<field id=\"Address\">\r\n<PosX>26</PosX>\r\n<PosY>29</PosY>\r\n</field>\r\n<field id=\"ZipCode\">\r\n<PosX>31</PosX>\r\n<PosY>35</PosY>\r\n</field>\r\n<field id=\"TownName\">\r\n<PosX>72</PosX>\r\n<PosY>35</PosY>\r\n</field>\r\n<field id=\"Amount\">\r\n<PosX>77</PosX>\r\n<PosY>60.5</PosY>\r\n</field>\r\n<field id=\"AmountInLetters\">\r\n<PosX>54</PosX>\r\n<PosY>70.5</PosY>\r\n</field>\r\n<field id=\"ReceptionDateDay\">\r\n<PosX>62</PosX>\r\n<PosY>79.5</PosY>\r\n</field>\r\n<field id=\"ReceptionDateMonth\">\r\n<PosX>76</PosX>\r\n<PosY>79.5</PosY>\r\n</field>\r\n<field id=\"ReceptionDateYear\">\r\n<PosX>92</PosX>\r\n<PosY>79.5</PosY>\r\n</field>\r\n<field id=\"Entity\">\r\n<list>\r\n<item id=\"200 du CGI\">\r\n<Items>\r\n<value>0</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>47.5</PosX>\r\n<PosY>93.7</PosY>\r\n</item>\r\n<item id=\"238 bis du CGI\">\r\n<Items>\r\n<value>1</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>97.3</PosX>\r\n<PosY>93.7</PosY>\r\n</item>\r\n<item id=\"885-0 V bis A du CGI\">\r\n<Items></Items>\r\n<Text>X</Text>\r\n<PosX>147.5</PosX>\r\n<PosY>93.7</PosY>\r\n</item>\r\n</list>\r\n</field>\r\n<field id=\"Type\">\r\n<Text>X</Text>\r\n<PosX>110</PosX>\r\n<PosY>111.5</PosY>\r\n</field>\r\n<field id=\"Nature\">\r\n<list>\r\n<item id=\"Numéraire\">\r\n<Items>\r\n<value>0</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>9.7</PosX>\r\n<PosY>133.7</PosY>\r\n</item>\r\n<item id=\"Titres de sociétés côtés\">\r\n<Items></Items>\r\n<Text>X</Text>\r\n<PosX>52.3</PosX>\r\n<PosY>133.7</PosY>\r\n</item>\r\n<item id=\"Autres\">\r\n<Items>\r\n<value>1</value>\r\n<value>2</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>110</PosX>\r\n<PosY>133.7</PosY>\r\n</item>\r\n</list>\r\n</field>\r\n<field id=\"PaymentMode\">\r\n<list>\r\n<item id=\"Remise d\'espèces\">\r\n<Items>\r\n<value>0</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>9.7</PosX>\r\n<PosY>156.1</PosY>\r\n</item>\r\n<item id=\"Chèque\">\r\n<Items>\r\n<value>1</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>52.3</PosX>\r\n<PosY>156.1</PosY>\r\n</item>\r\n<item id=\"Virement, prélèvement, carte bancaire\">\r\n<Items>\r\n<value>2</value>\r\n<value>3</value>\r\n</Items>\r\n<Text>X</Text>\r\n<PosX>110</PosX>\r\n<PosY>156.1</PosY>\r\n</item>\r\n</list>\r\n</field>\r\n<field id=\"TaxReceiptDateDay\">\r\n<PosX>134</PosX>\r\n<PosY>236.5</PosY>\r\n</field>\r\n<field id=\"TaxReceiptDateMonth\">\r\n<PosX>142</PosX>\r\n<PosY>236.5</PosY>\r\n</field>\r\n<field id=\"TaxReceiptDateYear\">\r\n<PosX>150</PosX>\r\n<PosY>236.5</PosY>\r\n</field>\r\n<field id=\"SignIn\">\r\n<Text>TamponCalandretaTr.png</Text>\r\n<PosX>123</PosX>\r\n<PosY>237</PosY>\r\n<DimWidth>70</DimWidth>\r\n</field>\r\n</part>\r\n</Page>\r\n</pages>\r\n</year>\r\n</config-parameters>'),
(10, 'CONF_NURSERY_OTHER_TIMESLOTS', 'xml', '<config-parameters>\r\n<school-year id=\"2020\">\r\n<other-timeslot id=\"T1\" label=\"11h55-13h25\" check-canteen=\"1\" linked-to-canteen=\"1\" check-nursery=\"T2\">TRUE, TRUE, FALSE, TRUE, TRUE, FALSE, FALSE</other-timeslot>\r\n<other-timeslot id=\"T2\" label=\"13h25-13h55\" linked-to-canteen=\"1\">TRUE, TRUE, FALSE, TRUE, TRUE, FALSE, FALSE</other-timeslot>\r\n</school-year>\r\n<school-year id=\"2021\">\r\n<other-timeslot id=\"T1\" label=\"11h55-13h25\" check-canteen=\"1\" linked-to-canteen=\"1\" check-nursery=\"T2\">TRUE, TRUE, FALSE, TRUE, TRUE, FALSE, FALSE</other-timeslot>\r\n<other-timeslot id=\"T2\" label=\"13h25-13h55\" linked-to-canteen=\"1\">TRUE, TRUE, FALSE, TRUE, TRUE, FALSE, FALSE</other-timeslot>\r\n</school-year>\r\n<school-year id=\"2022\">\r\n<other-timeslot id=\"T1\" label=\"11h55-13h25\" check-canteen=\"1\" linked-to-canteen=\"1\" check-nursery=\"T2\">TRUE, TRUE, FALSE, TRUE, TRUE, FALSE, FALSE</other-timeslot>\r\n<other-timeslot id=\"T2\" label=\"13h25-13h55\" linked-to-canteen=\"1\">TRUE, TRUE, FALSE, TRUE, TRUE, FALSE, FALSE</other-timeslot>\r\n</school-year>\r\n</config-parameters>');

CREATE TABLE `DiscountsFamilies` (
  `DiscountFamilyID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `DiscountFamilyType` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `DiscountFamilyReasonType` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `DiscountFamilyReason` varchar(255) DEFAULT NULL,
  `DiscountFamilyDate` datetime NOT NULL,
  `DiscountFamilyAmount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `FamilyID` smallint(5) UNSIGNED NOT NULL,
  KEY `FamilyID` (`FamilyID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Table which contains discounts of families';

CREATE TABLE `DocumentsApprovals` (
  `DocumentApprovalID` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `DocumentApprovalDate` datetime NOT NULL,
  `DocumentApprovalName` varchar(255) NOT NULL,
  `DocumentApprovalFile` varchar(255) NOT NULL,
  `DocumentApprovalType` tinyint(3) UNSIGNED NOT NULL,
  KEY `DocumentApprovalType` (`DocumentApprovalType`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Table which contains documents to approve';

CREATE TABLE `DocumentsFamiliesApprovals` (
  `DocumentFamilyApprovalID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `DocumentFamilyApprovalDate` datetime NOT NULL,
  `DocumentFamilyApprovalComment` varchar(255) DEFAULT NULL,
  `DocumentApprovalID` smallint(5) UNSIGNED NOT NULL,
  `SupportMemberID` smallint(5) UNSIGNED NOT NULL,
  KEY `SupportMemberID` (`SupportMemberID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Table which contains approvals of families';

CREATE TABLE `Donations` (
  `DonationID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `DonationReference` varchar(20) NOT NULL,
  `DonationEntity` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `DonationLastname` varchar(100) NOT NULL,
  `DonationFirstname` varchar(25) NOT NULL,
  `DonationAddress` varchar(255) NOT NULL,
  `DonationPhone` varchar(30) DEFAULT NULL,
  `DonationMainEmail` varchar(100) DEFAULT NULL,
  `DonationSecondEmail` varchar(100) DEFAULT NULL,
  `DonationFamilyRelationship` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `DonationReceptionDate` date NOT NULL,
  `DonationType` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `DonationNature` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `DonationValue` decimal(10,2) NOT NULL,
  `DonationReason` varchar(255) DEFAULT NULL,
  `DonationPaymentMode` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `DonationPaymentCheckNb` varchar(30) DEFAULT NULL,
  `BankID` smallint(5) UNSIGNED DEFAULT NULL,
  `TownID` smallint(5) UNSIGNED NOT NULL,
  `FamilyID` smallint(5) UNSIGNED DEFAULT NULL,
  KEY `TownID` (`TownID`),
  KEY `FamilyID` (`FamilyID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `EventRegistrations` (
  `EventRegistrationID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `EventRegistrationDate` datetime NOT NULL,
  `EventRegistrationValided` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `EventRegistrationComment` varchar(255) DEFAULT NULL,
  `EventID` mediumint(8) UNSIGNED NOT NULL,
  `FamilyID` smallint(5) UNSIGNED NOT NULL,
  `SupportMemberID` smallint(5) UNSIGNED NOT NULL,
  KEY `EventID` (`EventID`),
  KEY `FamilyID` (`FamilyID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `Events` (
  `EventID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `EventDate` datetime NOT NULL,
  `EventTitle` varchar(100) NOT NULL,
  `EventStartDate` date NOT NULL,
  `EventStartTime` time DEFAULT NULL,
  `EventEndDate` date NOT NULL,
  `EventEndTime` time DEFAULT NULL,
  `EventDescription` mediumtext NOT NULL,
  `EventMaxParticipants` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `EventRegistrationDelay` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `EventClosingDate` date DEFAULT NULL,
  `ParentEventID` mediumint(8) UNSIGNED DEFAULT NULL,
  `EventTypeID` tinyint(3) UNSIGNED NOT NULL,
  `TownID` smallint(5) UNSIGNED NOT NULL,
  `SupportMemberID` smallint(5) UNSIGNED NOT NULL,
  KEY `ParentEventID` (`ParentEventID`),
  KEY `EventTypeID` (`EventTypeID`),
  KEY `SupportMemberID` (`SupportMemberID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `EventSwappedRegistrations` (
  `EventSwappedRegistrationID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `EventSwappedRegistrationDate` datetime NOT NULL,
  `EventSwappedRegistrationClosingDate` datetime DEFAULT NULL,
  `RequestorFamilyID` smallint(5) UNSIGNED NOT NULL,
  `RequestorEventID` mediumint(8) UNSIGNED NOT NULL,
  `AcceptorFamilyID` smallint(5) UNSIGNED DEFAULT NULL,
  `AcceptorEventID` mediumint(8) UNSIGNED DEFAULT NULL,
  `SupportMemberID` smallint(5) UNSIGNED NOT NULL,
  KEY `RequestorEventID` (`RequestorEventID`),
  KEY `AcceptorEventID` (`AcceptorEventID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `EventTypes` (
  `EventTypeID` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `EventTypeName` varchar(25) NOT NULL,
  `EventTypeCategory` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  KEY `EvenTypeCategory` (`EventTypeCategory`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `EventTypes` (`EventTypeID`, `EventTypeName`, `EventTypeCategory`) VALUES
(1, 'Journée entretien/travaux', 1),
(2, 'Bal', 0),
(3, 'Vide-grenier', 0),
(4, 'Dictée', 0),
(5, 'Manifestation', 0),
(6, 'Remplacement cantine', 1),
(7, 'Remplacement garderie', 1),
(8, 'Journée ménage', 1),
(9, 'Réunion', 2),
(10, 'Action', 2);

CREATE TABLE `ExitPermissions` (
  `ExitPermissionID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ExitPermissionDate` date NOT NULL,
  `ExitPermissionName` varchar(100) NOT NULL,
  `ExitPermissionAuthorizedPerson` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `ChildID` smallint(5) UNSIGNED NOT NULL,
  KEY `ChildID` (`ChildID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `Families` (
  `FamilyID` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `FamilyLastname` varchar(100) NOT NULL,
  `FamilyMainEmail` varchar(100) DEFAULT NULL,
  `FamilyMainEmailContactAllowed` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `FamilyMainEmailInCommittee` tinyint(3) UNSIGNED DEFAULT '0',
  `FamilyMainPicture` varchar(255) DEFAULT NULL,
  `FamilySecondEmail` varchar(100) DEFAULT NULL,
  `FamilySecondEmailContactAllowed` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `FamilySecondEmailInCommittee` tinyint(3) UNSIGNED DEFAULT '0',
  `FamilySecondPicture` varchar(255) DEFAULT NULL,
  `FamilyDate` date NOT NULL,
  `FamilyDesactivationDate` date DEFAULT NULL,
  `FamilyNbMembers` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `FamilyNbPoweredMembers` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `FamilySpecialAnnualContribution` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `FamilyMonthlyContributionMode` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `FamilyMonthlyNurseryMode` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `FamilyAnnualContributionBalance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `FamilyBalance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `FamilyComment` mediumtext,
  `TownID` smallint(5) UNSIGNED NOT NULL,
  KEY `TownID` (`TownID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ForumCategories` (
`ForumCategoryID` tinyint(3) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ForumCategoryName` varchar(50) NOT NULL,
  `ForumCategoryDescription` varchar(255) DEFAULT NULL,
  `ForumCategoryDefaultLang` char(2) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Table which contains forum categories.';

CREATE TABLE `ForumCategoriesAccess` (
`ForumCategoryAccessID` smallint(5) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ForumCategoryAccess` char(1) NOT NULL,
  `ForumCategoryID` tinyint(3) unsigned NOT NULL,
  `SupportMemberStateID` tinyint(3) unsigned NOT NULL,
  KEY `SupportMemberStateID` (`SupportMemberStateID`), 
  KEY `ForumCategoryID` (`ForumCategoryID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Table which contains forum access to categories.';

CREATE TABLE `ForumMessages` (
`ForumMessageID` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ForumMessageDate` datetime NOT NULL,
  `ForumMessageContent` mediumtext NOT NULL,
  `ForumMessagePicture` varchar(255) NULL,
  `ForumReplyToMessageID` int(10) unsigned DEFAULT NULL,
  `ForumMessageUpdateDate` datetime DEFAULT NULL,
  `ForumTopicID` mediumint(8) unsigned NOT NULL,
  `SupportMemberID` smallint(5) unsigned NOT NULL,
  KEY `ForumTopicID` (`ForumTopicID`), 
  KEY `SupportMemberID` (`SupportMemberID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Table which contains forum messages.';

CREATE TABLE `ForumTopics` (
`ForumTopicID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ForumTopicTitle` varchar(255) NOT NULL,
  `ForumTopicDate` datetime NOT NULL,
  `ForumTopicExpirationDate` date DEFAULT NULL,
  `ForumTopicStatus` tinyint(3) unsigned NOT NULL,
  `ForumTopicIcon` tinyint(3) unsigned NOT NULL,
  `ForumTopicRank` tinyint(3) unsigned DEFAULT NULL,
  `ForumTopicNbViews` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ForumTopicNbAnswers` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ForumCategoryID` tinyint(3) unsigned NOT NULL,
  `SupportMemberID` smallint(5) unsigned NOT NULL,
  KEY `ForumCategoryID` (`ForumCategoryID`), 
  KEY `SupportMemberID` (`SupportMemberID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Table which contains forum topics.';

CREATE TABLE `ForumTopicsLastReads` (
`ForumTopicLastReadID` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ForumTopicLastReadMessageID` int(10) unsigned NOT NULL,
  `ForumTopicID` mediumint(8) unsigned NOT NULL,
  `SupportMemberID` smallint(5) unsigned NOT NULL,
  KEY `ForumTopicID` (`ForumTopicID`), 
  KEY `SupportMemberID` (`SupportMemberID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Table which contains last forum messages read by supporters.';

CREATE TABLE `ForumTopicsSubscribtions` (
`ForumTopicSubscribtionID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ForumTopicSubscribtionEmail` varchar(100) NOT NULL,
  `ForumTopicID` mediumint(8) unsigned NOT NULL,
  `SupportMemberID` smallint(5) unsigned NOT NULL,
  KEY `ForumTopicID` (`ForumTopicID`), 
  KEY `SupportMemberID` (`SupportMemberID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Table which contains subscritions of supporters to forum topics.';

CREATE TABLE `HistoFamilies` (
  `HistoFamilyID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `HistoDate` datetime NOT NULL,
  `HistoFamilyMonthlyContributionMode` tinyint(3) UNSIGNED NOT NULL,
  `HistoFamilyMonthlyNurseryMode` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `HistoFamilyBalance` decimal(10,2) NOT NULL,
  `FamilyID` smallint(5) UNSIGNED NOT NULL,
  `TownID` smallint(5) UNSIGNED NOT NULL,
  KEY `FamilyID` (`FamilyID`),
  KEY `TownID` (`TownID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `HistoLevelsChildren` (
  `HistoLevelChildID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `HistoLevelChildYear` smallint(5) UNSIGNED NOT NULL DEFAULT '2011',
  `HistoLevelChildGrade` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `HistoLevelChildClass` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `HistoLevelChildWithoutPork` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `ChildID` smallint(5) UNSIGNED NOT NULL,
  KEY `HistoLevelChildYear` (`HistoLevelChildYear`),
  KEY `ChildID` (`ChildID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `Holidays` (
  `HolidayID` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `HolidayStartDate` date NOT NULL,
  `HolidayEndDate` date NOT NULL,
  `HolidayDescription` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `Holidays` (`HolidayID`, `HolidayStartDate`, `HolidayEndDate`, `HolidayDescription`) VALUES
(1, '2012-02-13', '2012-02-24', 'Vacances d\'hiver.'),
(2, '2012-04-09', '2012-04-20', 'Vacances de Pâques.'),
(3, '2012-07-06', '2012-09-03', 'Vacances d\'été.'),
(4, '2012-10-29', '2012-11-09', 'Toussaint.'),
(5, '2012-12-24', '2013-01-04', 'Vacances de Noël.'),
(6, '2012-05-07', '2012-05-07', 'Pont.'),
(7, '2012-05-18', '2012-05-18', 'Pont.'),
(8, '2013-02-25', '2013-03-08', 'Vacances d\'hiver.'),
(9, '2013-04-22', '2013-05-03', 'Vacances de Pâques.'),
(10, '2013-07-08', '2013-09-02', 'Vacances d\'été.'),
(11, '2013-10-19', '2013-11-03', 'Toussaint.'),
(12, '2013-12-21', '2014-01-05', 'Vacances de Noël.'),
(13, '2014-03-01', '2014-03-16', 'Vacances d\'hiver.'),
(14, '2014-04-26', '2014-05-11', 'Vacances de Pâques.'),
(15, '2014-07-05', '2014-09-01', 'Vacances d\'été.'),
(16, '2014-05-30', '2014-05-30', 'Congrès regentas.'),
(17, '2014-10-18', '2014-11-02', 'Toussaint.'),
(18, '2014-12-20', '2015-01-04', 'Vacances de Noël.'),
(19, '2015-02-07', '2015-02-22', 'Vacances d\'hiver.'),
(20, '2015-04-11', '2015-04-26', 'Vacances de Pâques.'),
(21, '2015-07-04', '2015-08-31', 'Vacances d\'été.'),
(22, '2015-10-17', '2015-11-01', 'Toussaint.'),
(23, '2015-12-19', '2016-01-03', 'Vacances de Noël.'),
(24, '2016-02-20', '2016-03-06', 'Vacances d\'hiver.'),
(25, '2016-04-16', '2016-05-01', 'Vacances de Pâques.'),
(26, '2016-07-06', '2016-08-31', 'Vacances d\'été.'),
(27, '2015-05-15', '2015-05-15', 'Pont.'),
(28, '2016-10-20', '2016-11-02', 'Toussaint.'),
(29, '2016-12-17', '2017-01-02', 'Vacances de Noël.'),
(30, '2017-02-04', '2017-02-19', 'Vacances d\'hivers.'),
(31, '2017-04-01', '2017-04-17', 'Vacances de Pâques.'),
(32, '2017-07-08', '2017-09-03', 'Vacances d\'été.'),
(33, '2017-05-26', '2017-05-26', 'Pont de mai.'),
(34, '2017-10-23', '2017-11-03', 'Toussaint.'),
(35, '2017-12-23', '2018-01-05', 'Vacances de Noël.'),
(36, '2018-02-17', '2018-03-02', 'Vacances d\'hivers.'),
(37, '2018-04-14', '2018-04-27', 'Vacances de Pâques.'),
(38, '2018-07-09', '2018-08-31', 'Vacances d\'été.'),
(39, '2017-12-01', '2017-12-01', 'Ecole fermée'),
(40, '2018-10-20', '2018-11-04', 'Toussaint.'),
(41, '2018-12-22', '2019-01-06', 'Vacances de Noël.'),
(42, '2019-02-23', '2019-03-10', 'Vacances d\'hiver.'),
(43, '2019-04-20', '2019-05-05', 'Vacances de Pâques.'),
(44, '2019-07-06', '2019-09-01', 'Vacances d\'été.'),
(45, '2019-10-21', '2019-11-01', 'Toussaint.'),
(46, '2019-12-23', '2020-01-03', 'Vacances de Noël.'),
(47, '2020-02-10', '2020-02-21', 'Vacances d\'hiver.'),
(48, '2020-04-06', '2020-04-17', 'Vacances de Pâques.'),
(49, '2020-07-06', '2020-08-31', 'Vacances d\'été.'),
(50, '2020-05-22', '2020-05-22', 'Pont Ascension.'),
(51, '2020-10-17', '2020-10-31', 'Toussaint.'),
(52, '2020-12-19', '2021-01-03', 'Vacances de Noël.'),
(53, '2021-02-13', '2021-02-28', 'Vacances d\'hiver.'),
(54, '2021-04-12', '2021-04-23', 'Vacances de Pâques.'),
(55, '2021-07-07', '2021-09-01', 'Vacances d\'été.'),
(56, '2021-05-14', '2021-05-14', 'Pont de l\'Ascension.'),
(57, '2021-10-25', '2021-11-05', 'Toussaint.'),
(58, '2021-12-20', '2021-12-31', 'Vacances de Noël.'),
(59, '2022-02-20', '2022-03-04', 'Vacances d\'hiver.'),
(60, '2022-04-24', '2022-05-06', 'Vacances de Pâques.'),
(61, '2022-05-27', '2022-05-27', 'Pont de l\'Ascension.'),
(62, '2022-07-07', '2022-09-02', 'Vacances d\'été.');

CREATE TABLE `JobParameters` (
  `JobParameterID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `JobParameterName` varchar(50) NOT NULL,
  `JobParameterValue` mediumblob NOT NULL,
  `JobID` mediumint(8) UNSIGNED NOT NULL,
  KEY `JobID` (`JobID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `Jobs` (
  `JobID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `JobPlannedDate` datetime NOT NULL,
  `JobExecutionDate` datetime DEFAULT NULL,
  `JobType` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `JobNbTries` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `JobResult` varchar(255) DEFAULT NULL,
  `SupportMemberID` smallint(5) UNSIGNED NOT NULL,
  KEY `JobType` (`JobType`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `LaundryRegistrations` (
  `LaundryRegistrationID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `LaundryRegistrationDate` date NOT NULL,
  `FamilyID` smallint(5) UNSIGNED NOT NULL,
  KEY `FamilyID` (`FamilyID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `LogEvents` (
  `LogEventID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `LogEventDate` datetime NOT NULL,
  `LogEventItemID` int(10) UNSIGNED NOT NULL,
  `LogEventItemType` varchar(30) NOT NULL,
  `LogEventService` varchar(30) NOT NULL,
  `LogEventAction` varchar(30) NOT NULL,
  `LogEventLevel` tinyint(3) UNSIGNED NOT NULL DEFAULT '5',
  `LogEventTitle` varchar(255) DEFAULT NULL,
  `LogEventDescription` mediumtext,
  `LogEventLinkedObjectID` int(10) UNSIGNED DEFAULT NULL,
  `SupportMemberID` smallint(5) UNSIGNED NOT NULL,
  KEY `SupportMemberID` (`SupportMemberID`),
  KEY `LogEventItemID` (`LogEventItemID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `MeetingRooms` (
`MeetingRoomID` tinyint(3) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `MeetingRoomName` varchar(100) NOT NULL,
  `MeetingRoomRestrictions` varchar(255) DEFAULT NULL,
  `MeetingRoomEmail` varchar(255) DEFAULT NULL,
  `MeetingRoomActivated` tinyint(3) unsigned NOT NULL DEFAULT '1'
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Table which contains meeting rooms.';

INSERT INTO `MeetingRooms` (`MeetingRoomID`, `MeetingRoomName`, `MeetingRoomRestrictions`, `MeetingRoomEmail`, `MeetingRoomActivated`) VALUES
(1, 'Salle 1', '18:45-23:59#18:45-23:59#18:45-23:59#18:45-23:59#18:45-23:59#08:00-23:59#08:00-23:59', 'email@test.fr', 1),
(2, 'Salle 2', '17:00-23:59#17:00-23:59#17:00-23:59#17:00-23:59#17:00-23:59#08:00-23:59#08:00-23:59', 'email@test.fr', 1);

CREATE TABLE `MeetingRoomsRegistrations` (
`MeetingRoomRegistrationID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `MeetingRoomRegistrationDate` datetime NOT NULL,
  `MeetingRoomRegistrationTitle` varchar(100) NOT NULL,
  `MeetingRoomRegistrationStartDate` datetime NOT NULL,
  `MeetingRoomRegistrationEndDate` datetime NOT NULL,
  `MeetingRoomRegistrationMailingList` varchar(255) DEFAULT NULL,
  `MeetingRoomRegistrationDescription` mediumtext,
  `SupportMemberID` smallint(5) unsigned NOT NULL,
  `MeetingRoomID` tinyint(3) unsigned NOT NULL,
  `EventID` mediumint(8) unsigned DEFAULT NULL,
  KEY `SupportMemberID` (`SupportMemberID`), 
  KEY `EventID` (`EventID`), 
  KEY `MeetingRoomID` (`MeetingRoomID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Table which contains registrations of meeting rooms.';

CREATE TABLE `MoreMeals` (
  `MoreMealID` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `MoreMealDate` date NOT NULL,
  `MoreMealForDate` date NOT NULL,
  `MoreMealQuantity` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `MoreMealWithoutPorkQuantity` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `SupportMemberID` smallint(5) UNSIGNED NOT NULL,
  KEY `SupportMemberID` (`SupportMemberID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `NurseryRegistrations` (
  `NurseryRegistrationID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `NurseryRegistrationDate` date NOT NULL,
  `NurseryRegistrationForDate` date NOT NULL,
  `NurseryRegistrationAdminDate` date DEFAULT NULL,
  `NurseryRegistrationForAM` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `NurseryRegistrationForPM` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `NurseryRegistrationChildGrade` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `NurseryRegistrationChildClass` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `NurseryRegistrationIsLate` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `ChildID` smallint(5) UNSIGNED NOT NULL,
  `SupportMemberID` smallint(5) UNSIGNED NOT NULL,
  KEY `ChildID` (`ChildID`),
  KEY `SupportMemberID` (`SupportMemberID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `OpenedSpecialDays` (
  `OpenedSpecialDayID` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `OpenedSpecialDayDate` date NOT NULL,
  `OpenedSpecialDayDescription` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `Payments` (
  `PaymentID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `PaymentDate` datetime NOT NULL,
  `PaymentReceiptDate` date NOT NULL,
  `PaymentType` tinyint(3) UNSIGNED NOT NULL,
  `PaymentMode` tinyint(3) UNSIGNED NOT NULL,
  `PaymentCheckNb` varchar(30) DEFAULT NULL,
  `PaymentAmount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `PaymentUsedAmount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `BankID` smallint(5) UNSIGNED DEFAULT NULL,
  `FamilyID` smallint(5) UNSIGNED NOT NULL,
  KEY `FamilyID` (`FamilyID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `PaymentsBills` (
  `PaymentBillID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `BillID` mediumint(8) UNSIGNED NOT NULL,
  `PaymentID` mediumint(8) UNSIGNED NOT NULL,
  `PaymentBillPartAmount` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `SnackRegistrations` (
  `SnackRegistrationID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `SnackRegistrationDate` date NOT NULL,
  `SnackRegistrationClass` tinyint(3) UNSIGNED NOT NULL,
  `FamilyID` smallint(5) UNSIGNED NOT NULL,
  KEY `FamilyID` (`FamilyID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `Stats` (
`StatID` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `StatPeriod` varchar(20) NOT NULL,
  `StatType` varchar(30) NOT NULL,
  `StatSubType` varchar(30) DEFAULT NULL,
  `StatValue` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Table which contains some stats about the application.';

CREATE TABLE `SupportMembers` (
  `SupportMemberID` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `SupportMemberLastname` varchar(50) NOT NULL DEFAULT '',
  `SupportMemberFirstname` varchar(25) NOT NULL DEFAULT '',
  `SupportMemberPhone` varchar(30) DEFAULT NULL,
  `SupportMemberEmail` varchar(100) NOT NULL DEFAULT '',
  `SupportMemberLogin` varchar(32) NOT NULL DEFAULT '',
  `SupportMemberPassword` varchar(32) NOT NULL DEFAULT '',
  `SupportMemberActivated` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `SupportMemberOpenIdUrl` varchar(255) DEFAULT NULL,
  `SupportMemberWebServiceKey` varchar(32) DEFAULT NULL,
  `SupportMemberStateID` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `FamilyID` smallint(5) UNSIGNED DEFAULT NULL,
  KEY `SupportMemberLastname` (`SupportMemberLastname`),
  KEY `SupportMemberStateID` (`SupportMemberStateID`),
  KEY `FamilyID` (`FamilyID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `SupportMembers` VALUES (1, 'NomAdmin', 'PrénomAdmin', NULL, 'admin@test.fr', '21232f297a57a5a743894a0e4a801fc3', '21232f297a57a5a743894a0e4a801fc3', 1, NULL, 'e00cf25ad42683b3df678c61f42c6bda', 1, NULL);
INSERT INTO `SupportMembers` VALUES (2, 'NomRF', 'PrénomRF', NULL, 'rf@test.fr', 'bea2f3fe6ec7414cdf0bf233abba7ef0', 'bea2f3fe6ec7414cdf0bf233abba7ef0', 1, NULL, 'a20990097370a570a2caad4ab750050e', 2, NULL);
INSERT INTO `SupportMembers` VALUES (3, 'NomRI', 'PrénomRI', NULL, 'ri@test.fr', '08c7b0daa33b1e5e86a230c1801254c9', '08c7b0daa33b1e5e86a230c1801254c9', 1, NULL, '43b11c8e7713467ebfc35483568956cb', 3, NULL);
INSERT INTO `SupportMembers` VALUES (4, 'NomAjude', 'PrénomAjude', NULL, 'ajude@test.fr', '3b6f421e7550395e28e091c5565ac80a', '3b6f421e7550395e28e091c5565ac80a', 1, NULL, 'e83fe65c5aad6c81d9d227f616eaf11e', 4, NULL);
INSERT INTO `SupportMembers` VALUES (5, 'Famille-Test1', 'PrénomFT1', NULL, 'ft1@test.fr', 'd877a9de8f3d2d5e8720df6a02b3ff11', 'd877a9de8f3d2d5e8720df6a02b3ff11', 1, NULL, 'ed51ca4140e446a6b5430cc42517db19', 5, NULL);
INSERT INTO `SupportMembers` VALUES (6, 'Famille-Test2', 'PrénomFT2', NULL, 'ft2@test.fr', '6323fc22fa46f55f24c2d516802f8c35', '6323fc22fa46f55f24c2d516802f8c35', 1, NULL, '21d8634624ea87dd4e3aad0c097c9a86', 5, NULL);
INSERT INTO `SupportMembers` VALUES (7, 'NomRA', 'PrénomRA', NULL, 'ra@test.fr', 'db26ee047a4c86fbd2fba73503feccb6', 'db26ee047a4c86fbd2fba73503feccb6', 1, NULL, 'ee5be0221b2f8f58735b187268a01154', 6, NULL);
INSERT INTO `SupportMembers` VALUES (8, 'NomRV', 'PrénomRV', NULL, 'rv@test.fr', '108bc7b6961e71b2e770387a378cbc10', '108bc7b6961e71b2e770387a378cbc10', 1, NULL, '4d6951e105a7046766bb400574fb91e7', 7, NULL);

CREATE TABLE `SupportMembersStates` (
  `SupportMemberStateID` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `SupportMemberStateName` varchar(20) NOT NULL DEFAULT '',
  `SupportMemberStateDescription` varchar(255) DEFAULT NULL,
  `SupportMemberStateOptions` tinyint(3) UNSIGNED NOT NULL,
  KEY `SupportMemberStateName` (`SupportMemberStateName`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `SupportMembersStates` (`SupportMemberStateID`, `SupportMemberStateName`, `SupportMemberStateDescription`, `SupportMemberStateOptions`) VALUES
(1, 'Administrateur', "Administrateur de l'outil Planeta.", 0),
(2, 'Resp Facture', "Responsable facturation.", 0),
(3, 'Resp Inscript', "Responsable inscriptions cantine.", 0),
(4, 'Ajude', "Ajude de la Calandreta.", 0),
(5, 'Famille', "Famille de la Calandreta.", 0),
(6, 'Resp Admin', "Responsable Administratif.", 0),
(7, 'Resp Ev', "Responsable événements.", 0),
(8, 'Ancienne famille', "Ancienne famille de la Calandreta.", 0),
(9, 'Regent', "Regent de la Calandreta.", 0),
(10, 'Resp CLAE', "Responsable du CLAE.", 0),
(11, 'Famille Ext', "Famille externe à la Calandreta utilisant l'ALSH.", 0);

CREATE TABLE `Suspensions` (
  `SuspensionID` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `SuspensionStartDate` date NOT NULL,
  `SuspensionEndDate` date DEFAULT NULL,
  `SuspensionReason` varchar(255) DEFAULT NULL,
  `ChildID` smallint(5) UNSIGNED NOT NULL,
  KEY `ChildID` (`ChildID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `Towns` (
  `TownID` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `TownName` varchar(50) NOT NULL,
  `TownCode` varchar(5) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `Towns` (`TownID`, `TownName`, `TownCode`) VALUES
(1, 'Muret', '31600');

CREATE TABLE `UploadedFiles` (
`UploadedFileID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `UploadedFileObjectType` tinyint(3) unsigned NOT NULL,
  `UploadedFileDate` datetime NOT NULL,
  `UploadedFileName` varchar(255) NOT NULL,
  `UploadedFileDescription` varchar(255) DEFAULT NULL,
  `ObjectID` mediumint(8) unsigned NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Table which contains uploaded files linked to application''s objects.';

CREATE TABLE `WorkGroupRegistrations` (
  `WorkGroupRegistrationID` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `WorkGroupRegistrationDate` datetime NOT NULL,
  `WorkGroupRegistrationLastname` varchar(50) NOT NULL,
  `WorkGroupRegistrationFirstname` varchar(25) NOT NULL,
  `WorkGroupRegistrationEmail` varchar(100) NOT NULL,
  `WorkGroupRegistrationReferent` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `WorkGroupID` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `FamilyID` smallint(5) UNSIGNED DEFAULT NULL,
  `SupportMemberID` smallint(5) UNSIGNED NOT NULL,
  KEY `WorkGroupRegistrationReferent` (`WorkGroupRegistrationReferent`),
  KEY `WorkGroupID` (`WorkGroupID`),
  KEY `FamilyID` (`FamilyID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `WorkGroups` (
  `WorkGroupID` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `WorkGroupName` varchar(50) NOT NULL,
  `WorkGroupDescription` varchar(255) DEFAULT NULL,
  `WorkGroupEmail` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

COMMIT;