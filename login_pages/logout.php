<?php

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // Neprihlaseny pouzivatel, zobraz odkaz na Login alebo Register stranku.
header("location: ../web_pages/admin.php");
} 
// Uvolnenie session premennych. Tieto dva prikazy su ekvivalentne.
$_SESSION = array();
session_unset();

// Vymazanie session.
session_destroy();

// Presmerovanie na hlavnu stranku.

header("location: ../index.php");
exit;