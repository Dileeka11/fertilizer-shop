<?php
// Legacy entry point — registration now handled directly by register.php via Auth::registerCustomer().
require_once __DIR__ . '/../config.php';
redirect(BASE_URL . '/public/register.php');
