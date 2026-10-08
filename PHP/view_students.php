<?php
/* =====================================================================
   File    : view_students.php
   Purpose : View Students + Search Student.
             Lists every student in an HTML table with a photo preview and
             View / Edit / Upload / Delete buttons. A search box filters
             the list by name, roll number, email or course.
   ===================================================================== */
require_once 'db.php';

// Search keyword from the URL, e.g. view_students.php?search=CSE
$search   = trim($_GET['search'] ?? '');
$students = [];
$dbError  = '';

try {
    if ($search !== '') {
        // LIKE with % on both sides = "contains". Prepared statement keeps it safe.
        $like = '%' . $search . '%';
        $stmt = $conn->prepare('SELECT * FROM students
                                WHERE name LIKE ? OR roll_number LIKE ? OR email LIKE ? OR course LIKE ?
                                ORDER BY student_id DESC');
        $stmt->bind_param('ssss', $like, $like, $like, $like);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        // No search -> show all students, newest first
        $result = $conn->query('SELECT * FROM students ORDER BY student_id DESC');
    }

    // Copy every row into an array
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
} catch (mysqli_sql_exception $ex) {
    $dbError = 'Could not load students: ' . $ex->getMessage();
}

$pageTitle = 'View Students';
include 'header.php';
?>

<div class="card">
    <div class="top-bar">
        <h2>Registered Students</h2>
        <a href="add_student.php" class="btn btn-primary">+ Add Student</a>
    </div>

    <!-- ===== Search Student (GET form so the keyword stays in the URL) ===== -->
    <form class="search-form" method="get" action="view_students.php">
        <input type="text" name="search" placeholder="Search by name, roll number, email or course"
               value="<?php echo e($search); ?>">
        <input type="submit" class="btn btn-primary" value="Search">
        <a href="view_students.php" class="btn btn-secondary">Clear</a>
    </form>

    <?php if ($dbError !== ''): ?>
        <div class="alert alert-error"><?php echo e($dbError); ?></div>
    <?php endif; ?>

    <?php if ($search !== ''): ?>
        <p class="muted"><?php echo count($students); ?> result(s) found for "<?php echo e($search); ?>"</p><br>
    <?php endif; ?>

    <!-- ===== Student records table ===== -->
    <div class="table-wrapper">
        <table class="data-table">
            <tr>
                <th>S.No</th>
                <th>Photo</th>
                <th>Name</th>
                <th>Roll Number</th>
                <th>Email</th>
                <th>Course</th>
                <th>Mobile</th>
                <th>Gender</th>
                <th>Registered On</th>
                <th>Actions</th>
            </tr>

            <?php if (empty($students)): ?>
                <tr>
                    <td colspan="10" class="text-center muted">No student records found.</td>
                </tr>
            <?php else: ?>
                <?php $serial = 1; ?>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo $serial++; ?></td>
                        <td>
                            <?php if (!empty($student['profile_image']) && is_file(__DIR__ . '/' . $student['profile_image'])): ?>
                                <!-- Student image preview -->
                                <img src="<?php echo e($student['profile_image']); ?>" class="thumb" alt="Photo">
                            <?php else: ?>
                                <span class="no-image">No Image</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($student['name']); ?></td>
                        <td><?php echo e($student['roll_number']); ?></td>
                        <td><?php echo e($student['email']); ?></td>
                        <td><?php echo e($student['course']); ?></td>
                        <td><?php echo e($student['mobile']); ?></td>
                        <td><?php echo e($student['gender']); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($student['created_at'])); ?></td>
                        <td class="actions">
                            <a href="student_details.php?id=<?php echo $student['student_id']; ?>" class="btn btn-primary btn-small">View</a>
                            <a href="edit_student.php?id=<?php echo $student['student_id']; ?>" class="btn btn-secondary btn-small">Edit</a>
                            <a href="upload_file.php?student_id=<?php echo $student['student_id']; ?>" class="btn btn-secondary btn-small">Upload</a>
                            <!-- Delete uses POST (not a link) so it cannot be triggered by just visiting a URL -->
                            <form action="delete_student.php" method="post" class="inline-form"
                                  data-name="<?php echo e($student['name']); ?>"
                                  onsubmit="return confirmDelete(this.getAttribute('data-name'))">
                                <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                                <input type="submit" class="btn btn-danger btn-small" value="Delete">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>
    <p class="muted"><br>Total records shown: <?php echo count($students); ?></p>
</div>

<?php include 'footer.php'; ?>
