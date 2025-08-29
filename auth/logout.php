<?php

session_start();
$_SESSION = [];

session_destroy();
header("Location: ../main/index.php");
exit;
