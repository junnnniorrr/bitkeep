<?php
require_once("include/connection.php");

// Ensure ID is set and is numeric
if (!isset($_GET['ID']) || empty($_GET['ID'])) {
    echo "<script type='text/javascript'>
        alert('Invalid file ID!');
        window.location.href = 'add_document.php';
    </script>";
    exit;
}

$id = filter_var($_GET['ID'], FILTER_VALIDATE_INT);
if ($id === false) {
    echo "<script type='text/javascript'>
        alert('Invalid file ID format!');
        window.location.href = 'add_document.php';
    </script>";
    exit;
}

if (isset($_GET['confirmed']) && $_GET['confirmed'] == 'true') {
    // First get the specific file info
    $stmt = $conn->prepare("SELECT * FROM upload_files WHERE ID = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Get the file path - adjust column name if needed
        $file_path = $row['file_name']; // or whatever your column name is
        
        // Delete the physical file if it exists
        $upload_path = "uploads/"; // adjust this to your upload directory
        $full_path = $upload_path . $file_path;
        
        if (file_exists($full_path)) {
            unlink($full_path);
        }
        
        // Delete only this specific record
        $delete_stmt = $conn->prepare("DELETE FROM upload_files WHERE ID = ? LIMIT 1");
        $delete_stmt->bind_param("i", $id);
        
        if ($delete_stmt->execute()) {
            echo "<script type='text/javascript'>
                alert('File successfully deleted!');
                window.location.href = 'add_document.php';
            </script>";
        } else {
            echo "<script type='text/javascript'>
                alert('Error deleting file!');
                window.location.href = 'add_document.php';
            </script>";
        }
    } else {
        echo "<script type='text/javascript'>
            alert('File not found!');
            window.location.href = 'add_document.php';
        </script>";
    }
} else {
    // Show confirmation dialog
    echo "<script type='text/javascript'>
        var result = confirm('Are you sure you want to delete this file?');
        if (result) {
            window.location.href = 'delete.php?confirmed=true&ID=" . $id . "';
        } else {
            window.location.href = 'add_document.php';
        }
    </script>";
}
?>              