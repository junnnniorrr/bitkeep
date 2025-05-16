<?php
// connect to the database
require_once("include/connection.php");

// Uploads files
if (isset($_POST['save'])) {

    $user = $_POST['email'];
    $filename = $_FILES['myfile']['name'];
    $destination = '../uploads/' . $filename;

    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $file = $_FILES['myfile']['tmp_name'];
    $size = $_FILES['myfile']['size'];

    // Allowed extensions
    $allowed_extensions = ['pdf', 'doc', 'docx', 'xlsx', 'xls', 'ppt', 'pptx', 'zip', 'rar', 'jpg', 'jpeg', 'png', 'txt'];

    // Max file size (50MB)
    $max_file_size = 50 * 1024 * 1024; // 50MB in bytes

    if (!in_array($extension, $allowed_extensions)) {
        echo '<script type="text/javascript">
                alert("Invalid file type! Allowed extensions: ' . implode(', ', $allowed_extensions) . '");
                window.location = "add_file.php";
              </script>';
    } elseif ($size > $max_file_size) {
        echo '<script type="text/javascript">
                alert("File too large! Max allowed size is 50MB.");
                window.location = "add_file.php";
              </script>';
    } else {

        $query = mysqli_query($conn, "SELECT * FROM `upload_files` WHERE `name` = '$filename'") or die(mysqli_error($conn));
        $counter = mysqli_num_rows($query);

        if ($counter == 1) {
            echo '<script type="text/javascript">
                    alert("File already exists!");
                    window.location = "add_document.php";
                  </script>';
        } else {

            date_default_timezone_set("Asia/Manila");
            $time = date("M-d-Y h:i A");

            if (move_uploaded_file($file, $destination)) {
                $sql = "INSERT INTO upload_files (name, size, download, timers, admin_status, email)
                        VALUES ('$filename', $size, 0, '$time', 'Admin', '$user')";

                if (mysqli_query($conn, $sql)) {
                    echo '<script type="text/javascript">
                            alert("File Uploaded Successfully!");
                            window.location = "add_document.php";
                          </script>';
                } else {
                    echo "Database insert failed!";
                }
            } else {
                echo "Failed to upload file!";
            }
        }
    }
}
?>
