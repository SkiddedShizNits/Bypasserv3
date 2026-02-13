<?php
/**
 * Bypasserv3 - Logout
 */

session_start();
session_destroy();

header('Location: sign-in.php');
exit;
?>
