<?php
/* =====================================================================
   File    : header.php
   Purpose : Common top part of every PHP page - HTML head, header,
             navigation bar and the one-time (flash) success/error message.
   Usage   : $pageTitle = 'View Students';  include 'header.php';
   Note    : db.php must be included before this file.
   ===================================================================== */

// Default title when a page does not set one
if (!isset($pageTitle)) {
    $pageTitle = 'Student Admission Portal';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?> | Student Admission Portal</title>
    <!-- The PHP module re-uses the stylesheet and script of the Frontend folder -->
    <link rel="stylesheet" href="../Frontend/style.css">
</head>
<body>

    <!-- ===== Simple header ===== -->
    <header class="site-header">
        <h1>Student Admission Portal</h1>
        <p>Online Registration with File Management System</p>
    </header>

    <!-- ===== Navigation bar ===== -->
    <nav class="nav-bar">
        <a href="add_student.php">Add Student</a>
        <a href="view_students.php">View Students</a>
        <a href="upload_file.php">Upload Files</a>
        <a href="../Frontend/index.html">Registration Page (HTML)</a>
    </nav>

    <main class="main-content">
<?php
// ---------- Show the flash message once, then remove it from the session ----------
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    // type is either "success" or "error"
    $cssClass = ($flash['type'] === 'success') ? 'alert-success' : 'alert-error';
    echo '<div class="alert ' . $cssClass . '">' . e($flash['message']) . '</div>';
}
?>
