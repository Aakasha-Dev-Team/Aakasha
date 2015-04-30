<?php
// File: db.php

/* Settings */
define('DB_HOST', 'localhost');
define('DB_USER', 'aakashac_app');
define('DB_PASS', 'I]-w-qW,K4&C');
define('DB_NAME', 'aakashac_app');

/* make connection*/
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

/* check connection */
if($db->connect_errno) {
    die('Sorry, we have some problems');
}

/* change character set to utf8 */
$db->set_charset(DB_CHARSET);
?>