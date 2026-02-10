<?php
/**
 * Admin Logout
 */

require_once '../config/auth.php';

// Logout and redirect to login
adminLogout();
header('Location: login.php');
exit();
?>