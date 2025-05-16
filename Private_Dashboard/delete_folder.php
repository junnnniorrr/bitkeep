<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once("include/connection.php");
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_user'])) {
    header("Location: index.html");
    exit;
}

// Check if folder ID is set in the URL
if (isset($_GET['folder_id'])) {
    $folder_id = $_GET['folder_id'];  // Get the folder ID from the URL
    
    // Validate the folder ID
    if (is_numeric($folder_id)) {
        // Sanitize the folder ID to prevent SQL Injection
        $folder_id = mysqli_real_escape_string($conn, $folder_id);
        
        // Check if action is set (confirm or cancel)
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            
            if ($action == 'confirm') {
                // If the action is 'confirm', proceed with deleting the folder
                // First, check if the folder exists
                $query = mysqli_query($conn, "SELECT * FROM folders WHERE FOLDER_ID = '$folder_id'");
                
                if (mysqli_num_rows($query) > 0) {
                    // Get folder details
                    $folder = mysqli_fetch_assoc($query);
                    
                    // Function to recursively delete files in a folder
                    function deleteFilesInFolder($conn, $folder_id) {
                        // Check if folder_files table exists and has records for this folder
                        $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'folder_files'");
                        
                        if (mysqli_num_rows($check_table) > 0) {
                            // Get all files in this folder
                            $files_query = mysqli_query($conn, "SELECT * FROM folder_files WHERE FOLDER_ID = '$folder_id'");
                            
                            if ($files_query && mysqli_num_rows($files_query) > 0) {
                                while ($file = mysqli_fetch_assoc($files_query)) {
                                    // Delete physical file if it exists
                                    $filepath = 'uploads/' . $file['NAME'];
                                    if (file_exists($filepath)) {
                                        unlink($filepath);
                                    }
                                    
                                    // Delete file record
                                    mysqli_query($conn, "DELETE FROM folder_files WHERE ID = '" . $file['ID'] . "'");
                                }
                            }
                        }
                        
                        // Get all subfolders
                        $subfolders_query = mysqli_query($conn, "SELECT * FROM folders WHERE PARENT_ID = '$folder_id'");
                        
                        if ($subfolders_query && mysqli_num_rows($subfolders_query) > 0) {
                            // Recursively delete each subfolder
                            while ($subfolder = mysqli_fetch_assoc($subfolders_query)) {
                                deleteFilesInFolder($conn, $subfolder['FOLDER_ID']);
                                mysqli_query($conn, "DELETE FROM folders WHERE FOLDER_ID = '" . $subfolder['FOLDER_ID'] . "'");
                            }
                        }
                    }
                    
                    // Delete all files and subfolders
                    deleteFilesInFolder($conn, $folder_id);
                    
                    // Finally, delete the folder itself
                    $delete_query = "DELETE FROM folders WHERE FOLDER_ID = '$folder_id'";
                    
                    if (mysqli_query($conn, $delete_query)) {
                        // Redirect to folders list with a success message
                        echo '
                        <script type="text/javascript">
                            alert("Folder and all its contents deleted successfully!");
                            window.location = "folder_management.php";
                        </script>';
                    } else {
                        // If there was an error with the deletion
                        echo '
                        <script type="text/javascript">
                            alert("Error deleting folder: ' . mysqli_error($conn) . '");
                            window.location = "folder_management.php";
                        </script>';
                    }
                } else {
                    echo '
                    <script type="text/javascript">
                        alert("Folder not found.");
                        window.location = "folder_management.php";
                    </script>';
                }
            } elseif ($action == 'cancel') {
                // If the action is 'cancel', just redirect to the folder list
                header("Location: folder_management.php");
                exit;
            }
        } else {
            // If no action is specified, show confirmation page
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Confirm Folder Deletion</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
                <style>
                    body {
                        background-color: #f8f9fa;
                        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
                    }
                    .card {
                        border-radius: 10px;
                        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    }
                    .card-header {
                        background-color: #dc3545;
                        color: white;
                        border-radius: 10px 10px 0 0;
                    }
                    .btn-danger {
                        background-color: #dc3545;
                        border-color: #dc3545;
                    }
                    .btn-secondary {
                        background-color: #6c757d;
                        border-color: #6c757d;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-6 mt-5">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Folder Deletion</h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    // Get folder name
                                    $folder_query = mysqli_query($conn, "SELECT FOLDER_NAME FROM folders WHERE FOLDER_ID = '$folder_id'");
                                    $folder_name = "this folder";
                                    
                                    if ($folder_query && mysqli_num_rows($folder_query) > 0) {
                                        $folder_data = mysqli_fetch_assoc($folder_query);
                                        $folder_name = "<strong>" . htmlspecialchars($folder_data['FOLDER_NAME']) . "</strong>";
                                    }
                                    ?>
                                    <p class="text-danger"><strong>Warning:</strong> This action cannot be undone.</p>
                                    <p>Are you sure you want to delete <?php echo $folder_name; ?> and all its contents?</p>
                                    <p>This will permanently delete:</p>
                                    <ul>
                                        <li>All files in this folder</li>
                                        <li>All subfolders and their contents</li>
                                        <li>The folder itself</li>
                                    </ul>
                                    <div class="d-flex justify-content-between mt-4">
                                        <a href="delete_folder.php?folder_id=<?php echo $folder_id; ?>&action=confirm" class="btn btn-danger">
                                            <i class="fas fa-trash me-2"></i>Yes, Delete Everything
                                        </a>
                                        <a href="delete_folder.php?folder_id=<?php echo $folder_id; ?>&action=cancel" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i>No, Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            </body>
            </html>
            <?php
        }
    } else {
        // If the folder ID is not valid
        echo '
        <script type="text/javascript">
            alert("Invalid folder ID.");
            window.location = "folder_management.php";
        </script>';
    }
} else {
    // If no folder ID is provided
    echo '
    <script type="text/javascript">
        alert("No folder ID provided.");
        window.location = "folder_management.php";
    </script>';
}
?>