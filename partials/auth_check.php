<?php
require_once __DIR__ . '/../config.php';
Auth::requireStaff();
$role         = $_SESSION['admin_role'];
$current_page = basename($_SERVER['PHP_SELF']);
