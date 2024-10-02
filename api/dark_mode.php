<?php

// session start
session_start();

$isDark = $_POST['mode'];


if ($isDark == 'true') {
	$_SESSION['darkmode'] = '1';
} else {
	$_SESSION['darkmode'] = '0';
}


?>