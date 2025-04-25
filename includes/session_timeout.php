<?php
function checkSessionTimeout() {
    $timeout = 1800; // 30 minutes
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        session_unset();
        session_destroy();
        header("Location: ../login.php?error=expired");
        exit();
    }
    
    $_SESSION['last_activity'] = time();
}