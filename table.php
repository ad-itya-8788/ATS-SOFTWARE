<?php
include 'dbconnect.php';  // Including the database connection

// Starting HTML with Bootstrap included
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumes</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
    body
{
    color:white;}
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>Sr.No</th>
                        <th>Resume Name</th>
                        <th>Matched Skills</th>
                        <th>Score</th>
                        <th>Experience</th>
                        <th>Projects</th>
                        <th>Download</th>
                    </tr>
                </thead>
                <tbody id="resumeTable">';

// Database query to fetch resumes and their details
$query = "SELECT r.id, r.file_path, s.score, s.matched_skills, s.experience_info, s.project_info
          FROM resume r
          JOIN score s ON r.id = s.resume_id
          ORDER BY s.score DESC";
$result = pg_query($conn, $query);

// Checking for any query execution error
if (!$result) {
    die("Error in query execution: " . pg_last_error());
}

// Fetching all rows of resumes
$resumes = pg_fetch_all($result);
$index = 1;
foreach ($resumes as $resume) {
    // Extracting resume details
    $resumeId = $resume['id'];
    $resumePath = $resume['file_path'];
    $resumeName = basename($resumePath); // Extracts the filename from file path
    $score = $resume['score'];
    $matchedSkills = $resume['matched_skills'];
    $experienceInfo = $resume['experience_info'];
    $projectInfo = $resume['project_info'];

    // Creating a table row for each resume
    echo "<tr>";
    echo "<td>{$index}</td>";
    echo "<td>{$resumeName}</td>";
    echo "<td>{$matchedSkills}</td>";
    echo "<td>{$score}%</td>";
    echo "<td>{$experienceInfo}</td>";
    echo "<td>{$projectInfo}</td>";
    echo "<td><a href='{$resumePath}' class='btn btn-primary' download>Download</a></td>";
    echo "</tr>";

    $index++; // Incrementing the index
}

// Closing table
echo '</tbody>
     </table>
     </div>
    </div>';

echo '</body>';
echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>';
echo '</html>';
?>
