<?php

include_once "./intern/data/sw_constants.php";

// Profiles

$profiles = [
	"Dummy" => "./intern/data/dummy.fac"	
];

// WWS Config

$vonFiliale = 915;
$zuFiliale = 1;

$options  = null;
$wwsserver	= "pgsql:host=;port=5432;dbname=";
$wwsuser='';
$wwspass='';

$wwsAdminUsers = [ 999, 998 ];
$wwsChiefGroups = [ 1,2 ];

// Scandesktop intern 

date_default_timezone_set("Europe/Berlin");

$DEBUG=0;
$error_qna1=0;
$error_qort=0;
$error_osdt=0;
$devel=0;

$docpath = "docs/";


			
######## Menu  ##############
$menu_name['root']['Startseite']  = './converter.php';
$menu_name['root']['Logout']  = './logout.php';

if (isset($_SESSION["uid"])) {
	if ($_SESSION['level'] >= 0) { $menu_name['user']['Startseite']  = './converter.php'; }
}

$menu_name['user']['Logout']  = './logout.php';


# 

if (php_sapi_name() == 'cli') {
	if( empty($_SESSION["level"])) { $_SESSION["level"] = 5; }
	if( empty($_SESSION["user"])) { $_SESSION["user"] = 'cli'; }
}


?>
