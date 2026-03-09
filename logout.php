<?php
// 1. Initialize the session
session_start();

// 2. Unset all session variables
$_SESSION = array();

// 3. Destroy the session cookie if it exists
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// 4. Finally, destroy the session itself
session_destroy();

// 5. Redirect to the landing page or login page
header("Location: index.php");
exit();
?>