<?php
session_start();
if (!empty($_GET['_db_fresh'])) { $_SESSION = []; session_regenerate_id(true); }
if (empty($_SESSION['logado'])) { header('Location: login.php'); exit; }
header('Location: dashboard.php');
exit;