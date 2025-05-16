<?php
// Initialize session
session_start();

if (!isset($_SESSION['admin_user'])) {
    header('Location: index.html');
    exit();
}

require_once("include/connection.php");

// Function to sanitize input
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Check if the form is submitted
if (isset($_POST['save'])) {
    // Retrieve and sanitize form input values
    $folder_name = sanitize_input($_POST['folder_name']);
    $parent_id = isset($_POST['parent_id']) && $_POST['parent_id'] != '0' ? sanitize_input($_POST['parent_id']) : NULL;
    
    // Get admin email from session
    $id = mysqli_real_escape_string($conn, $_SESSION['admin_user']);
    $query = mysqli_query($conn, "SELECT admin_user FROM admin_login WHERE id = '$id'") or die(mysqli_error($conn));
    $row = mysqli_fetch_array($query);
    $admin_email = $row['admin_user'];

    // Validate folder name
    if (empty($folder_name)) {
        echo '<script type="text/javascript">
                alert("Folder name cannot be empty!");
                window.location = "folder_management.php";
              </script>';
        exit();
    }

    // Check if folder name already exists at the same level
    $check_sql = "SELECT * FROM folders WHERE FOLDER_NAME = '$folder_name'";
    
    // Add parent folder condition
    if ($parent_id) {
        $check_sql .= " AND PARENT_ID = '$parent_id'";
    } else {
        $check_sql .= " AND (PARENT_ID IS NULL OR PARENT_ID = '0')";
    }
    
    $result = mysqli_query($conn, $check_sql);
    
    if (!$result) {
        echo '<script type="text/javascript">
                alert("Database error: ' . mysqli_error($conn) . '");
                window.location = "folder_management.php";
              </script>';
        exit();
    }
    
    if (mysqli_num_rows($result) > 0) {
        echo '<script type="text/javascript">
                alert("A folder with this name already exists at this level!");
                window.location = "folder_management.php";
              </script>';
        exit();
    }

    // Get the table structure to determine available columns
    $table_info = mysqli_query($conn, "DESCRIBE folders");
    $columns = [];
    while ($col = mysqli_fetch_assoc($table_info)) {
        $columns[] = $col['Field'];
    }

    // Build the INSERT query dynamically based on available columns
    $fields = [];
    $values = [];

    // Always include FOLDER_NAME
    $fields[] = "FOLDER_NAME";
    $values[] = "'$folder_name'";

    // Add PARENT_ID if it exists
    if (in_array("PARENT_ID", $columns)) {
        $fields[] = "PARENT_ID";
        $values[] = $parent_id ? "'$parent_id'" : "NULL";
    }

    // Add TIMERS/created_at if it exists
    if (in_array("TIMERS", $columns)) {
        $fields[] = "TIMERS";
        $values[] = "NOW()";
    } else if (in_array("created_at", $columns)) {
        $fields[] = "created_at";
        $values[] = "NOW()";
    }

    // Add DESCRIPTION if it exists and is provided
    if (in_array("DESCRIPTION", $columns) && isset($_POST['description'])) {
        $description = sanitize_input($_POST['description']);
        $fields[] = "DESCRIPTION";
        $values[] = "'$description'";
    }

    // Add ADMIN_STATUS if it exists
    if (in_array("ADMIN_STATUS", $columns)) {
        $fields[] = "ADMIN_STATUS";
        $values[] = "'Admin'";
    }

    // Add DOWNLOAD if it exists
    if (in_array("DOWNLOAD", $columns)) {
        $fields[] = "DOWNLOAD";
        $values[] = "0";
    }

    // Construct the final query
    $sql = "INSERT INTO folders (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $values) . ")";

    // Debug: Uncomment to see the query
    // echo $sql; exit;

    if (mysqli_query($conn, $sql)) {
        echo '<script type="text/javascript">
                alert("Folder created successfully!");
                window.location = "folder_management.php";
              </script>';
    } else {
        echo '<script type="text/javascript">
                alert("Error creating folder: ' . mysqli_error($conn) . '");
                window.location = "folder_management.php";
              </script>';
    }
    
    exit();
} else {
    // If accessed directly without POST data
    header("Location: folder_management.php");
    exit();
}
?>