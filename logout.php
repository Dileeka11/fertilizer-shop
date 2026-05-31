<?php
require_once __DIR__ . '/config.php';
Auth::logoutStaff();
redirect(BASE_URL . '/login.php');
