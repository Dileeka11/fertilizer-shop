<?php
// Legacy entry point — forwards to /login.php (the real-auth admin login).
require_once __DIR__ . '/../config.php';
redirect(BASE_URL . '/login.php');
