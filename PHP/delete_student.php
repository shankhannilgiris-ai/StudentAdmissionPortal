<?php
/* =====================================================================
   File    : delete_student.php
   Purpose : Delete Student - removes a student record, all of his/her
             file records (by ON DELETE CASCADE) and the real files
             stored inside the uploads folder.
   Called  : by the Delete button (POST form) in view_students.php
             and student_details.php
   ===================================================================== */
require_once 'db.php';

// Delete is allowed only through the POST form (not by typing a URL)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('error', 'Invalid request.');
    redirect('view_students.php');
}

$id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    set_flash('error', 'Invalid student id.');
    redirect('view_students.php');
}

try {
    // 1. Find the student (for the message and the profile image path)
    $stmt = $conn->prepare('SELECT name, profile_image FROM students WHERE student_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$student) {
        set_flash('error', 'Student not found or already deleted.');
        redirect('view_students.php');
    }

    // 2. Collect all file paths of this student before deleting the rows
    $paths = [];
    if (!empty($student['profile_image'])) {
        $paths[] = $student['profile_image'];
    }
    $stmt = $conn->prepare('SELECT file_path FROM student_files WHERE student_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $paths[] = $row['file_path'];
    }
    $stmt->close();

    // 3. Delete the student. student_files rows are deleted automatically
    //    because of "ON DELETE CASCADE" on the foreign key.
    $stmt = $conn->prepare('DELETE FROM students WHERE student_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    // 4. Delete the physical files from the uploads folder
    foreach (array_unique($paths) as $path) {
        delete_upload($path);
    }

    set_flash('success', 'Student "' . $student['name'] . '" and all uploaded files were deleted.');

} catch (mysqli_sql_exception $ex) {
    set_flash('error', 'Could not delete the student: ' . $ex->getMessage());
}

redirect('view_students.php');
