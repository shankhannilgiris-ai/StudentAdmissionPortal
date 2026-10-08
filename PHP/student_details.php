<?php
/* =====================================================================
   File    : student_details.php
   Purpose : Shows the complete profile of one student (with photo) and
             the list of all uploaded files (View Uploaded Files feature)
             with preview / download links.
   URL     : student_details.php?id=1
   ===================================================================== */
require_once 'db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    set_flash('error', 'Invalid student id.');
    redirect('view_students.php');
}

$student = null;
$files   = [];
try {
    // 1. Student details
    $stmt = $conn->prepare('SELECT * FROM students WHERE student_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // 2. All uploaded files of this student (latest first)
    $stmt = $conn->prepare('SELECT * FROM student_files WHERE student_id = ? ORDER BY upload_date DESC, file_id DESC');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $files[] = $row;
    }
    $stmt->close();
} catch (mysqli_sql_exception $ex) {
    set_flash('error', 'Database error: ' . $ex->getMessage());
    redirect('view_students.php');
}

if (!$student) {
    set_flash('error', 'Student not found.');
    redirect('view_students.php');
}

// Converts bytes to a readable size, e.g. 153600 -> "150.0 KB"
function readable_size($bytes)
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    }
    return number_format($bytes / 1024, 1) . ' KB';
}

$pageTitle = 'Student Details';
include 'header.php';
?>

<!-- ===================== Profile card ===================== -->
<div class="card">
    <div class="top-bar">
        <h2>Student Profile</h2>
        <a href="view_students.php" class="btn btn-secondary">&laquo; Back to List</a>
    </div>

    <div class="profile-layout">
        <!-- Student image preview -->
        <div class="profile-photo">
            <?php if (!empty($student['profile_image']) && is_file(__DIR__ . '/' . $student['profile_image'])): ?>
                <img src="<?php echo e($student['profile_image']); ?>" class="profile-img" alt="Profile photo">
            <?php else: ?>
                <span class="no-image large">No Image</span>
            <?php endif; ?>
            <p><strong><?php echo e($student['roll_number']); ?></strong></p>
        </div>

        <!-- Student information -->
        <div class="profile-info">
            <table class="details-table">
                <tr><th>Student ID</th><td><?php echo $student['student_id']; ?></td></tr>
                <tr><th>Name</th><td><?php echo e($student['name']); ?></td></tr>
                <tr><th>Roll Number</th><td><?php echo e($student['roll_number']); ?></td></tr>
                <tr><th>Email</th><td><?php echo e($student['email']); ?></td></tr>
                <tr><th>Course</th><td><?php echo e($student['course']); ?></td></tr>
                <tr><th>Mobile</th><td><?php echo e($student['mobile']); ?></td></tr>
                <tr><th>Gender</th><td><?php echo e($student['gender']); ?></td></tr>
                <tr><th>Address</th><td><?php echo nl2br(e($student['address'])); ?></td></tr>
                <tr><th>Registered On</th><td><?php echo date('d-m-Y h:i A', strtotime($student['created_at'])); ?></td></tr>
            </table>

            <div class="button-row">
                <a href="edit_student.php?id=<?php echo $student['student_id']; ?>" class="btn btn-primary">Edit</a>
                <a href="upload_file.php?student_id=<?php echo $student['student_id']; ?>" class="btn btn-secondary">Upload File</a>
                <form action="delete_student.php" method="post" class="inline-form"
                      data-name="<?php echo e($student['name']); ?>"
                      onsubmit="return confirmDelete(this.getAttribute('data-name'))">
                    <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                    <input type="submit" class="btn btn-danger" value="Delete">
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ===================== Uploaded files ===================== -->
<div class="card">
    <h2>Uploaded Files (<?php echo count($files); ?>)</h2>

    <div class="table-wrapper">
        <table class="data-table">
            <tr>
                <th>S.No</th>
                <th>Preview</th>
                <th>File Name</th>
                <th>Type</th>
                <th>Size</th>
                <th>Uploaded On</th>
                <th>Action</th>
            </tr>

            <?php if (empty($files)): ?>
                <tr><td colspan="7" class="text-center muted">No files uploaded yet.</td></tr>
            <?php else: ?>
                <?php foreach ($files as $index => $file): ?>
                    <?php
                        // Check whether the real file still exists on the disk
                        $fullPath = __DIR__ . '/' . $file['file_path'];
                        $exists   = is_file($fullPath);
                        $isImage  = in_array($file['file_type'], IMAGE_EXTENSIONS, true);
                    ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td>
                            <?php if ($exists && $isImage): ?>
                                <img src="<?php echo e($file['file_path']); ?>" class="thumb" alt="Preview">
                            <?php else: ?>
                                <span class="no-image"><?php echo strtoupper(e($file['file_type'])); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($file['file_name']); ?></td>
                        <td><?php echo strtoupper(e($file['file_type'])); ?></td>
                        <td><?php echo $exists ? readable_size(filesize($fullPath)) : '-'; ?></td>
                        <td><?php echo date('d-m-Y h:i A', strtotime($file['upload_date'])); ?></td>
                        <td>
                            <?php if ($exists): ?>
                                <!-- target=_blank opens in a new tab; download attribute saves the file -->
                                <a href="<?php echo e($file['file_path']); ?>" target="_blank" class="btn btn-secondary btn-small">View</a>
                                <a href="<?php echo e($file['file_path']); ?>" download="<?php echo e($file['file_name']); ?>" class="btn btn-primary btn-small">Download</a>
                            <?php else: ?>
                                <span class="error">File missing</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
