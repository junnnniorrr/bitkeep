<?php
require_once("include/connection.php");
session_start();

if (isset($_GET['confirmed']) && $_GET['confirmed'] == 'true') {
    date_default_timezone_set("GMT");
    $time = date("M-d-Y h:i A", strtotime("+1 HOURS"));

    $email = $_SESSION['admin_user'];

    mysqli_query($conn, "UPDATE history_log1 SET `logout_time` = '$time' WHERE `id` = '$email'");

    $_SESSION = NULL;
    $_SESSION = [];
    session_unset();
    session_destroy();

    echo "<script type='text/javascript'>alert('LogOut Successfully!');
        document.location='index.html'</script>";
} else {
    echo "<script type='text/javascript'>
        var result = confirm('Are you sure you want to log out?');
        if (result) {
            window.location.href = 'logout.php?confirmed=true';
        } else {
            window.location.href = 'dashboard.php'; // Replace 'dashboard.php' with your actual system page URL
        }
    </script>";
}
?>
