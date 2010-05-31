<?php

include('common.php');

session_start();

unset($_SESSION['uid']);
unset($_SESSION['password']);

header("Location: {$page_uri_base}");
