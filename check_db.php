<?php
require_once("Private_Dashboard/include/connection.php");

// Check the structure of the upload_files table
$query = "DESCRIBE upload_files";
$result = mysqli_query($conn, $query);

echo "<h2>Upload Files Table Structure</h2>";
echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}

echo "</table>";

// Check a sample record
$query = "SELECT * FROM upload_files LIMIT 1";
$result = mysqli_query($conn, $query);

echo "<h2>Sample Record</h2>";
if ($row = mysqli_fetch_assoc($result)) {
    echo "<pre>";
    print_r($row);
    echo "</pre>";
} else {
    echo "No records found in upload_files table.";
}
?> 