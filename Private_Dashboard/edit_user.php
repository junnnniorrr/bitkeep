<?php 
require_once("include/connection.php");

if(isset($_POST['edit'])){
    $id = $_POST['id'];
    $user_name = mysqli_real_escape_string($conn, $_POST['name']);
    $email_address = mysqli_real_escape_string($conn, $_POST['email_address']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    
    // Check if the password field is not empty
    if (!empty($_POST['user_password'])) {
        $user_password = mysqli_real_escape_string($conn, $_POST['user_password']);
        $hashed_password = password_hash($user_password, PASSWORD_DEFAULT, array('cost' => 12));
        $query = "UPDATE `login_user` SET `name` = '$user_name', `email_address` = '$email_address', `user_password` = '$hashed_password', `department` = '$department' WHERE `id` = '$id'";
    } else {
        $query = "UPDATE `login_user` SET `name` = '$user_name', `email_address` = '$email_address', `department` = '$department' WHERE `id` = '$id'";
    }
    
    if(mysqli_query($conn, $query)){
        echo "<script type='text/javascript'>alert('Successfully Edited'); window.location='view_user.php';</script>";
    } else {
        echo "<script type='text/javascript'>alert('Error updating record: ".mysqli_error($conn)."'); window.location='view_user.php';</script>";
    }
}

if(isset($_POST['edit2'])){
    $id = $_POST['id'];
    $user_name = mysqli_real_escape_string($conn, $_POST['name']);
    $admin_user = mysqli_real_escape_string($conn, $_POST['admin_user']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    
    // Check if the password field is not empty
    if (!empty($_POST['admin_password'])) {
        $admin_password = mysqli_real_escape_string($conn, $_POST['admin_password']);
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT, array('cost' => 12));
        $query = "UPDATE `admin_login` SET `name` = '$user_name', `admin_user` = '$admin_user', `admin_password` = '$hashed_password', `department` = '$department' WHERE `id` = '$id'";
    } else {
        $query = "UPDATE `admin_login` SET `name` = '$user_name', `admin_user` = '$admin_user', `department` = '$department' WHERE `id` = '$id'";
    }
    
    if(mysqli_query($conn, $query)){
        echo "<script type='text/javascript'>alert('Successfully Edited'); window.location='view_admin.php';</script>";
    } else {
        echo "<script type='text/javascript'>alert('Error updating record: ".mysqli_error($conn)."'); window.location='view_admin.php';</script>";
    }
}
?>
