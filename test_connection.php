<?php
require_once 'includes/config.php';
if ($conn) {
    echo "Database connection successful!";
} else {
    echo "Connection failed.";
}
?>