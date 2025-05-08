<?php
session_start();
unset($_SESSION['agency_id']);
unset($_SESSION['agency_name']);
header('Location: ../index.php');
exit();
?>