<?php
require_once("include/connection.php");

if (isset($_POST['reguser'])) {
    // Retrieve and sanitize form data
    $user_name = mysqli_real_escape_string($conn, $_POST['name']);
    $email_address = mysqli_real_escape_string($conn, $_POST['email_address']);
    $user_password = password_hash($_POST['user_password'], PASSWORD_DEFAULT, ['cost' => 12]);
    $user_status = mysqli_real_escape_string($conn, $_POST['user_status']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);

    try {
        // Check if the email address already exists
        $q_checkadmin = $conn->prepare("SELECT COUNT(*) AS count FROM `login_user` WHERE `email_address` = ?");
        $q_checkadmin->bind_param("s", $email_address);
        $q_checkadmin->execute();
        $result = $q_checkadmin->get_result();
        $row = $result->fetch_assoc();

        if ($row['count'] > 0) {
            // Email already taken
            echo '
                <script type="text/javascript">
                    alert("Email Address already taken");
                    window.location = "dashboard.php";
                </script>
            ';
        } else {
            // Insert the new user into the database
            $insert_query = $conn->prepare("INSERT INTO `login_user` (`name`, `email_address`, `user_password`, `user_status`, `department`) 
                                            VALUES (?, ?, ?, ?, ?)");
            $insert_query->bind_param("sssss", $user_name, $email_address, $user_password, $user_status, $department);

            if ($insert_query->execute()) {
                echo '
                    <script type="text/javascript">
                        alert("Saved Employee Info");
                        window.location = "dashboard.php";
                    </script>
                ';
            } else {
                throw new Exception("Insert failed: " . $conn->error);
            }
        }
    } catch (Exception $e) {
        // Handle errors
        echo '
            <script type="text/javascript">
                alert("Error: ' . addslashes($e->getMessage()) . '");
                window.location = "dashboard.php";
            </script>
        ';
    }
}

// Close the database connection
$conn->close();
?>
