<?php

require_once("include/connection.php");

$id = mysqli_real_escape_string($conn, $_GET['id']);

if (isset($_GET['confirm']) && $_GET['confirm'] == 'true') {
    mysqli_query($conn, "DELETE FROM admin_login WHERE id='$id'") or die(mysqli_error($conn));
    echo "<script type='text/javascript'>alert('Deleted Admin!');document.location='view_admin.php'</script>";
} else {
    echo "<script type='text/javascript'>
    if (confirm('Are you sure you want to delete this admin?')) {
        window.location.href = 'delete_admin.php?id=$id&confirm=true';
    } else {
        window.location.href = 'view_admin.php';
    }
    </script>";
}
?>
