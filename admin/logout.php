<?php
session_start();

// Unset admin session variables
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_username']);

// Redirect to admin login
header('Location: /CURATOR/admin/login.php');
exit;
