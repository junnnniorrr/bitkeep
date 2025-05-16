<?php
require_once("include/connection.php");

if (isset($_FILES['file'])) {
    $file_name = $_FILES['file']['name'];
    $file_size = $_FILES['file']['size'];
    $folder_id = (int)$_POST['folder_id'];
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $upload_dir = "uploads/" . $folder_id;

    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_path = $upload_dir . "/" . $file_name;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
        $size_in_mb = round($file_size / 1024 / 1024, 2) . "MB";

        // Insert file details into database
        $query = "INSERT INTO upload_files (NAME, SIZE, DOWNLOAD, TIMERS, ADMIN_STATUS, EMAIL) 
                  VALUES ('$file_name', '$size_in_mb', 0, NOW(), 'Admin', '$email')";

        if (mysqli_query($conn, $query)) {
            echo "File uploaded successfully!";
            header("Location: manage_folder.php?folder_id=$folder_id");
        } else {
            echo "Error saving file details to database: " . mysqli_error($conn);
        }
    } else {
        echo "Error uploading file.";
    }
}
?>
