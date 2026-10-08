<?php
/* =====================================================================
   File    : update_student.php
   Purpose : Receives the Edit Student form, validates it on the server
             and updates the record. If a new profile image is uploaded,
             the old image file is deleted and replaced.
   ===================================================================== */
require_once 'db.php';

// Only POST requests are accepted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('view_students.php');
}

// If the upload is bigger than post_max_size, PHP gives an empty $_POST
if (empty($_POST)) {
    set_flash('error', 'The uploaded data is too large. Image must be 2 MB or less.');
    redirect('view_students.php');
}

$id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    set_flash('error', 'Invalid student id.');
    redirect('view_students.php');
}

// Load the current record (we need the old image path)
try {
    $stmt = $conn->prepare('SELECT * FROM students WHERE student_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $current = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} catch (mysqli_sql_exception $ex) {
    set_flash('error', 'Database error: ' . $ex->getMessage());
    redirect('view_students.php');
}
if (!$current) {
    set_flash('error', 'Student not found.');
    redirect('view_students.php');
}

// ---------------- 1. Server-side validation ----------------
$data   = read_student_form();
$errors = validate_student($data, true);     // true = password optional

// ---------------- 2. Unique roll number / email (ignore this student) ----------------
if (empty($errors)) {
    try {
        $errors = array_merge($errors, find_duplicates($conn, $data['roll_number'], $data['email'], $id));
    } catch (mysqli_sql_exception $ex) {
        $errors[] = 'Database error while checking duplicates: ' . $ex->getMessage();
    }
}

// ---------------- 3. Optional new profile image ----------------
$imageFile  = $_FILES['profile_image'] ?? null;
$imageError = validate_upload($imageFile, IMAGE_EXTENSIONS, 'Profile image', false);
if ($imageError !== '') {
    $errors[] = $imageError;
}

if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $old = $data;
    unset($old['password']);
    $_SESSION['old_input'] = $old;
    redirect('edit_student.php?id=' . $id);
}

// Save the new image if one was chosen
$newImagePath = null;
if ($imageFile !== null && $imageFile['error'] === UPLOAD_ERR_OK) {
    $newImagePath = save_upload($imageFile);
    if ($newImagePath === false) {
        $_SESSION['form_errors'] = ['Could not save the new image. Check folder permissions.'];
        redirect('edit_student.php?id=' . $id);
    }
}

// ---------------- 4. Update the database ----------------
$conn->begin_transaction();
try {
    // Keep old values when password / image are not changed
    $imagePath    = $newImagePath ?? $current['profile_image'];
    $passwordHash = ($data['password'] !== '')
                    ? password_hash($data['password'], PASSWORD_DEFAULT)
                    : $current['password'];

    $stmt = $conn->prepare('UPDATE students SET name = ?, roll_number = ?, email = ?, password = ?,
                            course = ?, mobile = ?, gender = ?, address = ?, profile_image = ?
                            WHERE student_id = ?');
    $stmt->bind_param('sssssssssi',
        $data['name'], $data['roll_number'], $data['email'], $passwordHash,
        $data['course'], $data['mobile'], $data['gender'], $data['address'], $imagePath, $id);
    $stmt->execute();
    $stmt->close();

    if ($newImagePath !== null) {
        // Remove the old photo's record from student_files ...
        if (!empty($current['profile_image'])) {
            $del = $conn->prepare('DELETE FROM student_files WHERE student_id = ? AND file_path = ?');
            $del->bind_param('is', $id, $current['profile_image']);
            $del->execute();
            $del->close();
        }
        // ... and add the new photo's record
        $originalName = basename($imageFile['name']);
        $fileType     = strtolower(pathinfo($newImagePath, PATHINFO_EXTENSION));
        $ins = $conn->prepare('INSERT INTO student_files (student_id, file_name, file_path, file_type)
                               VALUES (?, ?, ?, ?)');
        $ins->bind_param('isss', $id, $originalName, $newImagePath, $fileType);
        $ins->execute();
        $ins->close();
    }

    $conn->commit();

    // Delete the old image from the disk only after the database was updated
    if ($newImagePath !== null) {
        delete_upload($current['profile_image']);
    }

    set_flash('success', 'Student details updated successfully.');
    redirect('student_details.php?id=' . $id);

} catch (mysqli_sql_exception $ex) {
    $conn->rollback();
    if ($newImagePath !== null) {
        delete_upload($newImagePath);       // remove the unused new file
    }
    $message = ($ex->getCode() == 1062) ? 'Roll number or email already exists.'
                                        : 'Database error: ' . $ex->getMessage();
    $_SESSION['form_errors'] = [$message];
    $old = $data;
    unset($old['password']);
    $_SESSION['old_input'] = $old;
    redirect('edit_student.php?id=' . $id);
}
