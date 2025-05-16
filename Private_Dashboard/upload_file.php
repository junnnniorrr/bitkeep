<?php
if (isset($_POST['upload'])) {
    $folder_id = $_POST['folder_id']; // Folder ID passed from the URL
    $email = $_POST['email'];
    
    // File data
    $file = $_FILES['myfile'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // Extract file extension

    // Allowed file types
    $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'txt']; // Add as needed

    if (in_array($fileType, $allowed)) {
        if ($fileError === 0) {
            if ($fileSize <= 5 * 1024 * 1024) { // Limit to 5MB
                // Create a unique file name and define the target folder path
                $uniqueFileName = uniqid('', true) . "." . $fileType;
                $uploadDir = "uploads/" . $folder_id;  // Using folder_id from the URL
                $fileDestination = $uploadDir . "/" . $uniqueFileName; // Save file in the folder for the folder_id

                // Ensure the folder exists and create it if it doesn't
                if (!file_exists($uploadDir)) {
                    if (mkdir($uploadDir, 0777, true)) {
                        file_put_contents("upload_debug.log", "Folder $uploadDir created successfully.\n", FILE_APPEND); // Log success
                    } else {
                        file_put_contents("upload_debug.log", "Failed to create folder $uploadDir.\n", FILE_APPEND); // Log failure
                        echo '<script>alert("Failed to create folder!");</script>';
                        exit;
                    }
                }

                // Attempt to move the file to the correct folder
                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    file_put_contents("upload_debug.log", "File uploaded to $fileDestination.\n", FILE_APPEND); // Log success
                    // Insert file details into the database
                    $sql = "INSERT INTO `upload_files` (NAME, SIZE, DOWNLOAD, TIMERS, ADMIN_STATUS, EMAIL, folder_id, file_type, file_path) 
                            VALUES (?, ?, 0, NOW(), 'Admin', ?, ?, ?, ?)";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssiss", $fileName, $fileSize, $email, $folder_id, $fileType, $fileDestination);

                    if ($stmt->execute()) {
                        echo '
                            <script type="text/javascript">
                                alert("File uploaded successfully!");
                                window.location = "folders.php?folder_id=' . $folder_id . '";
                            </script>
                        ';
                    } else {
                        echo '<script>alert("Database error: ' . $conn->error . '");</script>';
                    }
                } else {
                    file_put_contents("upload_debug.log", "Failed to move uploaded file!\n", FILE_APPEND); // Log failure
                    echo '<script>alert("Failed to move uploaded file!");</script>';
                }
            } else {
                echo '<script>alert("File size exceeds 5MB!");</script>';
            }
        } else {
            echo '<script>alert("File upload error! Code: ' . $fileError . '");</script>';
        }
    } else {
        echo '<script>alert("Invalid file type! Allowed: ' . implode(", ", $allowed) . '");</script>';
    }
}
?>
