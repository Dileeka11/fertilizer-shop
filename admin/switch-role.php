<?php
// Dev role-switcher removed. Real password-based login handles role selection.
require_once __DIR__ . '/../config.php';
redirect(BASE_URL . '/login.php');
