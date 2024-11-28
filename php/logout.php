<?php

@include '../php/db_connection.php';

session_start();
session_unset();
session_destroy();

header('location:../php/loginPage.php');

?>