<?php
print("<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>") ;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head><link rel="stylesheet" href="style.css" type="text/css"/>
<title>Phplicensewatcher - configuration</title>
</head><body>

<?php

##############################################################
# We are using PHP Pear stuff ie. pear.php.net
##############################################################
require_once 'DB.php';

$configfile="../config.php" ; // must be writeable by the webserver



#################################################
## Write config file
#################################################
if (!is_writeable($configfile)) {
    die("Cannot open the configfile (config.php) for writing. Please correct the permissions. <a href=\"index.php\">Return to the configuration interface</a></body></html>");
    exit;
};
$handle = fopen ($configfile, "w") ;

fwrite($handle, "<?php \n") ;
fwrite($handle, "/* This is an autogenerated phplicensewatcher config-file */\n") ;
fwrite($handle, "/* A good commented config-file (sample-config.php) is included in the distribution (in case you want to modify it manually). */\n") ;

fwrite ($handle, "\$lmutil_loc=\"".addslashes($_POST['lmutil_loc'])."\";\n");
fwrite ($handle, "\$i4blt_loc=\"".addslashes($_POST['i4blt_loc'])."\";\n");
fwrite ($handle, "\$LUM_timeout=".addslashes($_POST['LUM_timeout']).";\n");
fwrite ($handle, "\$notify_address=\"".addslashes($_POST['notify_address'])."\";\n");
fwrite ($handle, "\$lead_time=".addslashes($_POST['lead_time']).";\n");
fwrite ($handle, "\$disable_autorefresh=".addslashes($_POST['disable_autorefresh']).";\n");
fwrite ($handle, "\$disable_license_removal=".addslashes($_POST['disable_license_removal']).";\n");
fwrite ($handle, "\$collection_interval=".addslashes($_POST['collection_interval']).";\n");

$db_type=$_POST['db_type'] ;
$db_hostname=$_POST['db_hostname'];
$db_username=$_POST['db_username'];
$db_password=$_POST['db_password'];
$db_database=$_POST['db_database'];

$dsn = "$db_type://$db_username:$db_password@$db_hostname/$db_database";

$db_adminusername=$_POST['db_adminusername'];
$db_adminpassword=$_POST['db_adminpassword'];
fwrite ($handle, "\$db_type=\"".addslashes($db_type)."\";\n");
fwrite ($handle, "\$db_hostname=\"".addslashes($db_hostname)."\";\n");
fwrite ($handle, "\$db_username=\"".addslashes($db_username)."\";\n");
fwrite ($handle, "\$db_password=\"".addslashes($db_password)."\";\n");
fwrite ($handle, "\$db_database=\"".addslashes($db_database)."\";\n");

fwrite ($handle, "\$dsn=\"".addslashes($dsn)."\";\n");

fwrite ($handle, "\$colors=\"".addslashes($_POST['colors'])."\";\n");
fwrite ($handle, "\$smallgraph=\"".addslashes($_POST['smallgraph'])."\";\n");
fwrite ($handle, "\$largegraph=\"".addslashes($_POST['largegraph'])."\";\n");
fwrite ($handle, "\$legendpoints=\"".addslashes($_POST['legendpoints'])."\";\n");

for ( $i = 0 ; $i < sizeof($_POST['servers']); $i++ ) {
    if ($_POST['servers'][$i] != "") {
        fwrite ($handle, "\$servers[]=\"".addslashes($_POST['servers'][$i])."\";\n");
 	fwrite ($handle, "\$description[]=\"".addslashes($_POST['description'][$i])."\";\n");
        fwrite ($handle, "\$log_file[]=\"".addslashes($_POST['$log_file'][$i])."\";\n");
    } ;
} ;


fwrite ($handle, "\$lmstat_loc=\$lmutil_loc . \" lmstat\"; \n");

fwrite($handle, "?>\n") ;
fclose($handle);


#################################################
## Create Database and user
#################################################
if ($POST['create_database']=="yes") {
    $link =  mysql_connect("$db_hostname","$db_adminusername","$db_adminpassword")  or die("Could not connect\n") ;
  $result=mysql_query("create database $db_database") ;
  if (!$result) {
    die('Ungültige Abfrage: ' . mysql_error());
  } ;
  $result=  mysql_query("grant all privileges on $db_database.* to
      '$db_username'@'%' identified by '$db_password'");
  if (!$result) {
    die('Ungültige Abfrage: ' . mysql_error());
  } ;
  $result=mysql_query("flush privileges") ;
  if (!$result) {
    die('Ungültige Abfrage: ' . mysql_error());
  } ;
  mysql_close($link);
 } 

##################################################
## Create Tables
##################################################
if ($_POST['create_tables']=="yes") {
  $createtablestring = implode('', file('../phplicensewatcher.sql'));

  $sqlarray = explode(";",$createtablestring); // Split in seperate sql commands (one cannot execute two or more commands seperated by a ';')
  $dsn = "mysql://$db_username:$db_password@$db_hostname/$db_database";
  $db = DB::connect($dsn, true);
  
  foreach ($sqlarray as $sqlquery)
    {
      $result = $db->query($sqlquery) ;
    }
  
  $db->disconnect();
 }


?>
<h1>PHPlicensewatcher web configuration interface</h1>
<p>
<!-- TODO: better response (did the creation of the database work, ...) -->
If you requested it, the databases and tables should now be created and the config-File is modified. For security reasons you should now remove the write permissions to config.php.
</p>
<h3>Links</h3>
<ul>
<li> <a href="index.php">Return to the configuration interface</a></li>
<li> <a href="../">Go to this Phplicensewatcher installation directory</a></li>
<li> <a href="http://phplicensewatch.sourceforge.net/">Phplicensewatcher homepage</a></li>
</ul>
</body></html>

