<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Tahoma, sans-serif;
            background-color:rgb(246, 248, 248);
            color: #333;
        }

        header, footer {
            background-color: #181818;
            color: #f7f7f7;
            text-align: center;
            padding: 20px;
            font-family: Trebuchet MS;
            letter-spacing: 1.8px;
            border-bottom: 3px solid #007bff;
            box-shadow: 4px 4px 4px rgba(41, 237, 15, 0.99);
        }

        header {
            border-bottom: none;
        }

        footer {
            border-top: 2px solid green;
            width: 100%;
        }

        .container {
            margin-top: 20px;
        }

        .card {
            margin-bottom: 20px;
            background-color: rgba(255, 255, 255, 0.9);
            color: #333;
        }

        .card-header {
            background-color: #0b0b0b;
            color: #fff;
            text-transform: uppercase;
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }

        th, td {
            border: 2px solid #333;
            text-align: center;
            padding: 10px;
        }

        .btn-primary {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
        }

        .table-container {
            overflow-x: auto;
            margin: 13px;
        }

        .custom-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .custom-card {
            margin: 10px;
            flex: 1;
            min-width: 280px;
        }

        img {
            height: 50px;
            margin-bottom: 5px;
        }

        a {
            text-decoration: none;
            color: rgb(61, 118, 223);
            font-weight: bold;
            margin: 4px;
        }

        #coming {
            color: red;
        }

        tr:hover {
            background-color: #f5f5;
            cursor: pointer;
        }

        table {

                        width: 100%;

        }
    </style>
</head>

<body>
    <header>
        <h1>
            Applicant Tracking System
            <img src="Photos/background.png" alt="background">
        </h1>
    </header>

    <div class="container">
        <div class="custom-container">
            <!-- Upload Form Section -->
            <div class="custom-card">
                <div class="card">
                    <div class="card-header">Upload Resumes ⬆️</div>
                    <center><img src="Photos/uploadimg.png" alt="upload"></center>
                    <div class="card-body">
                        <form enctype="multipart/form-data" method="POST" id="fileForm" action="resume_processing.php">
                            <div class="mb-3">
                                <input class="form-control" type="file" name="resumes[]" accept=".pdf" multiple required>
                            </div>
                            <div class="mb-3">
                                <label>Enter Skills (comma-separated):</label>
                                <input class="form-control" type="text" placeholder="Eg: Java, Python, C, Communication, Coding" name="skills" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Instructions Section -->
            <div class="custom-card">
                <div class="card">
                    <div class="card-header">How to Use..</div>
                    <center><img src="Photos/tick.png" alt="tick"></center>
                    <div class="card-body">
                        <h5>Read Me..</h5>
                        <p>Upload your resumes with skills you want to highlight. The system will analyze and match resumes to the provided skills. You can upload multiple resumes for efficient processing.</p>
                        <b>Templates for Your Reference:</b>
                        <a href="atstemplete/templete1.pdf" title="Click for Template 1">First</a>
                        <a href="atstemplete/templete2.pdf" title="Click for Template 2">Second</a>
                        <a href="atstemplete/templete3.pdf" title="Click for Template 3">Third</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="custom-card">
            <div class="card">
                <div class="card-header">Processing Resumes
                    <img src="Photos/rtick.png" alt="processing">
                           <button type="button" class="btn btn-danger" onclick="confirmDeletion()" style="float: right;">Delete All</button>
                </div>
                <div class="card-body">
             
                    <div class="table-container">
                      <table>
    <thead>
        <tr style="background-color:#ccffcc">
            <th>Sr. No.</th>
            <th>Resume Name</th>
            <th>Matched Skills</th>
            <th>Score</th>
            <th id="coming">Experience<pre>Tracking coming soon</pre></th>
            <th id="coming">Projects<pre>Tracking coming soon</pre></th>
            <th>Download</th>
        </tr>
    </thead>
    <tbody>
        <?php
        include 'dbconnect.php';

        // Query to fetch all data from the single table
        $query = "SELECT * FROM resumes ORDER BY score DESC";
        $result = pg_query($conn, $query);

        if (!$result) {
            die("Error in query execution: " . pg_last_error());
        }

        $resumes = pg_fetch_all($result);
        if (!$resumes) {
            echo "<tr>";
            echo "<td colspan='8' style='text-align:center; color:red;'><img src='Photos/warning.png' alt='warning' style='vertical-align: middle;'> <b>No resumes found.</b></td>";
            echo "</tr>";
        } else {
          $resumeCount = count($resumes);
         echo "<b style='color:green'>Total Resumes: $resumeCount.<img src='Photos/correct.png' ></br>";
    
            $index = 1;
            foreach ($resumes as $resume) {
                $resumeId = $resume['id'];
                $resumePath = $resume['file_path'];
                $resumeName = basename($resumePath);
                $score = $resume['score'];
                $matchedSkills = $resume['matched_skills'];
                $experienceInfo = $resume['experience_info'];
                $projectInfo = $resume['project_info'];
             
                echo "<tr>";
                echo "<td>{$index}</td>";
                echo "<td>{$resumeName}</td>";
                echo "<td>{$matchedSkills}</td>";
                echo "<td>{$score}%</td>";
                echo "<td id='coming'>{$experienceInfo}</td>";
                echo "<td id='coming'>{$projectInfo}</td>";
                echo "<td><a href='uploads/{$resumeName}'><img src='Photos/download.png' class='download-img' alt='download'></a></td>";
                echo "</tr>";
                $index++;
            }
        }
        ?>
    </tbody>
</table>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDeletion() {
            if (confirm("Are you sure you want to delete all uploaded resumes?")) {
                window.location.href = "delete_resumes.php";
            }
        }
    </script>

    <footer>
        <b>&copy; 2025 Aditya Chavan. All Rights Reserved.</b>
    </footer>
</body>

</html>
