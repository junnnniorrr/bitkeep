<?php
require_once("../include/connection.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];
    $admin_email = $_POST['admin_email'];
    $assigned_folder_id = !empty($_POST['assigned_folder_id']) ? $_POST['assigned_folder_id'] : null;

    $status = $action === 'approve' ? 'approved' : 'rejected';

    $stmt = $conn->prepare("UPDATE folder_requests SET status = ?, admin_email = ?, assigned_folder_id = ? WHERE id = ?");
    $stmt->bind_param("ssii", $status, $admin_email, $assigned_folder_id, $request_id);

    if ($stmt->execute()) {
        header("Location: manage_requests.php?success=1");
    } else {
        echo "Error updating request: " . $stmt->error;
    }
}
?>
