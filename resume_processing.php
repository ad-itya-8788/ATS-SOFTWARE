<?php

include 'dbconnect.php';

function upload_file($file, $skills, $conn)
{
    $pdf_tool_path = __DIR__ . '/xpdf-tools-win-4.05/bin64/pdftotext.exe';
    $exp_keywords = ['worked', 'experience', 'working', 'responsible', 'executed', 'managed'];
    $learn_keywords = ['learned', 'learning', 'studied', 'studying', 'courses', 'education', 'training'];
    $no_exp_keywords = ['no experience', 'fresher', 'no work experience', "haven't worked", 'no previous experience'];
    $no_proj_keywords = ['no projects', 'no academic projects', 'did not work on projects', 'no project experience'];
    $proj_keywords = ['project', 'developed', 'designed', 'built', 'implemented', 'created', 'enhanced', 'led', 'optimized'];

    $file_name = $file['name'];
    $tmp_name = $file['tmp_name'];
    $upload_dir = __DIR__ . '/uploads/';
    $file_path = $upload_dir . basename($file_name);

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_type = mime_content_type($tmp_name);
    if ($file_type !== "application/pdf") {
        return "Only PDF files are allowed.";
    }

    if ($file['size'] > 10 * 1024 * 1024) {
        return "File is too large.";
    }

    if (move_uploaded_file($tmp_name, $file_path)) {
        $text_content = shell_exec("\"$pdf_tool_path\" \"$file_path\" -");

        if (!$text_content) {
            return "Error extracting text from PDF.";
        }

        $text_content = strtolower(trim($text_content));

        $has_exp = check_keywords($text_content, $exp_keywords);
        $has_no_exp = check_keywords($text_content, $no_exp_keywords);
        $has_learning_exp = (!$has_exp && !$has_no_exp) ? check_keywords($text_content, $learn_keywords) : false;

        $has_proj = check_keywords($text_content, $proj_keywords);
        $has_no_proj = check_keywords($text_content, $no_proj_keywords);

        list($matched_skills, $skill_match_percent) = match_skills($skills, $text_content);

        $exp_result = get_exp_result($has_no_exp, $has_exp, $has_learning_exp, $skill_match_percent, $text_content);

        $proj_result = get_proj_result($has_no_proj, $has_proj, $skill_match_percent);

        save_to_db($file_path, $skill_match_percent, $matched_skills, $exp_result, $proj_result, $conn);

        return "Resume uploaded and processed successfully.";
    } else {
        return "File upload failed.";
    }
}

function check_keywords($text, $keywords)
{
    foreach ($keywords as $keyword) {
        if (stripos($text, $keyword) !== false) {
            return true;
        }
    }
    return false;
}

function match_skills($skills, $text)
{
    $matched_skills = [];
    $match_count = 0;
    foreach ($skills as $skill) {
        $pattern = '/(?:^|\s|[^\w])' . preg_quote($skill, '/') . '(?=$|\s|[^\w])/i';
        if (preg_match($pattern, $text)) {
            $matched_skills[] = $skill;
            $match_count++;
        }
    }
    $match_percent = count($skills) > 0 ? (int)($match_count / count($skills) * 100) : 0;
    return [$matched_skills, $match_percent];
}

function get_exp_result($has_no_exp, $has_exp, $has_learning_exp, $match_percent, $text_content)
{
    if (strpos($text_content, 'student') !== false || strpos($text_content, 'fresher') !== false) {
        return "No experience.";
    }

    if ($has_exp || $match_percent > 0) {
        return "Experienced.";
    }

    if ($has_learning_exp) {
        return "Learning experience.";
    } else {
        return "No clear experience information.";
    }
}

function get_proj_result($has_no_proj, $has_proj, $match_percent)
{
    if ($has_no_proj || $match_percent == 0)
     {
        return "No projects explicitly mentioned.";
    } elseif ($has_proj) {
        return "Projects found.";
    } else {
        return "No clear projects information.";
    }
}

function save_to_db($file_path, $match_percent, $matched_skills, $exp_result, $proj_result, $conn)
{
    $escaped_path = pg_escape_string($file_path);
    $skills_text = pg_escape_string(implode(', ', $matched_skills));
    $exp_result = pg_escape_string($exp_result);
    $proj_result = pg_escape_string($proj_result);

    $query = "INSERT INTO resumes (file_path, score, matched_skills, experience_info, project_info) VALUES ('$escaped_path', $match_percent, '$skills_text', '$exp_result', '$proj_result')";
    $result = pg_query($conn, $query);

    if (!$result) {
        die("Error saving resume to database.");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['resumes'])) {
    if (isset($_POST['skills'])) {
        $skills = array_map('strtolower', array_map('trim', explode(',', $_POST['skills'])));
    } else {
        die("Skills are required.");
    }

    $files = $_FILES['resumes'];
    $total_files = count($files['name']);

    $success_count = 0;
    $error_messages = [];

    for ($i = 0; $i < $total_files; $i++) {
        $file = ['name' => $files['name'][$i], 'tmp_name' => $files['tmp_name'][$i], 'size' => $files['size'][$i]];

        $result = upload_file($file, $skills, $conn);
        if (strpos($result, 'successfully') !== false) {
            $success_count++;
        } else {
            $error_messages[] = $result;
        }
    }

    if (!empty($error_messages)) {
        $error_msg = implode("\n", $error_messages);
        echo "<script>alert('Errors encountered:\n{$error_msg}');</script>";
    }

    echo '<script>window.location.href = "index.php";</script>';
}

?>
