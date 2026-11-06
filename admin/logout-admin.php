<?php
session_start();
session_unset();
session_destroy();
header('Location: ../php-customer/Login.php');
exit;
