<?php
include 'dbconnect.php';

// Start a database transaction
pg_query($conn, "BEGIN");

// Query to delete all rows from the 'resumes' table
$deleteResumeQuery = "DELETE FROM resumes";
$deleteResumeResult = pg_query($conn, $deleteResumeQuery);

if ($deleteResumeResult) {
    // Path to the uploads directory
    $dirPath = 'uploads/';
    $files = glob($dirPath . '*'); // Get all files in the directory

    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file); // Delete each file
        }
    }

    // Commit the transaction
    pg_query($conn, "COMMIT");

    // Redirect to the homepage or another page
    header("Location: index.php");
    exit;
} else {
    // Roll back the transaction in case of an error
    pg_query($conn, "ROLLBACK");
    echo "Error deleting resumes from the database: " . pg_last_error($conn);
}
?>
