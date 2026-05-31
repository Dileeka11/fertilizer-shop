<?php
require_once __DIR__ . '/../../config.php';
Auth::logoutCustomer();
redirect(BASE_URL . '/public/index.php');
