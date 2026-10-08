<?php
/* =====================================================================
   File    : edit_student.php
   Purpose : Edit Student - shows a form pre-filled with the existing
             details of one student. The form is submitted to
             update_student.php. Password and photo are optional here:
             leaving them empty keeps the old values.
   URL     : edit_student.php?id=5
   ===================================================================== */
require_once 'db.php';

// Read and check the id from the URL (must be a positive number)
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    set_flash('error', 'Invalid student id.');
    redirect('view_students.php');
}

// Fetch the student record
try {
    $stmt = $conn->prepare('SELECT * FROM students WHERE student_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} catch (mysqli_sql_exception $ex) {
    set_flash('error', 'Database error: ' . $ex->getMessage());
    redirect('view_students.php');
}

if (!$student) {
    set_flash('error', 'Student not found.');
    redirect('view_students.php');
}

// If update_student.php found errors, use the values the user typed;
// otherwise use the values from the database.
$errors = $_SESSION['form_errors'] ?? [];
$values = $_SESSION['old_input'] ?? $student;
unset($_SESSION['form_errors'], $_SESSION['old_input']);

$pageTitle = 'Edit Student';
include 'header.php';
?>

<div class="card form-card">
    <h2>Edit Student - <?php echo e($student['roll_number']); ?></h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <strong>Please correct the following errors:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="update_student.php" method="post" enctype="multipart/form-data"
          onsubmit="return validateForm('edit')" novalidate>

        <!-- Hidden field tells update_student.php which record to change -->
        <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">

        <div class="form-group">
            <label for="name">Student Name <span class="required">*</span></label>
            <input type="text" id="name" name="name" maxlength="100" value="<?php echo e($values['name']); ?>">
            <span class="error" id="err_name"></span>
        </div>

        <div class="form-group">
            <label for="roll_number">Roll Number <span class="required">*</span></label>
            <input type="text" id="roll_number" name="roll_number" maxlength="20" value="<?php echo e($values['roll_number']); ?>">
            <span class="error" id="err_roll_number"></span>
        </div>

        <div class="form-group">
            <label for="email">Email <span class="required">*</span></label>
            <input type="email" id="email" name="email" maxlength="100" value="<?php echo e($values['email']); ?>">
            <span class="error" id="err_email"></span>
        </div>

        <div class="form-group">
            <label for="password">New Password</label>
            <input type="password" id="password" name="password" maxlength="50" placeholder="Leave blank to keep the current password">
            <span class="hint">Only fill this if you want to change the password.</span>
            <span class="error" id="err_password"></span>
        </div>

        <div class="form-group">
            <label for="course">Course <span class="required">*</span></label>
            <select id="course" name="course">
                <option value="">-- Select Course --</option>
                <?php foreach (COURSES as $course): ?>
                    <option value="<?php echo e($course); ?>" <?php echo ($values['course'] === $course) ? 'selected' : ''; ?>>
                        <?php echo e($course); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="error" id="err_course"></span>
        </div>

        <div class="form-group">
            <label for="mobile">Mobile Number <span class="required">*</span></label>
            <input type="text" id="mobile" name="mobile" maxlength="10" value="<?php echo e($values['mobile']); ?>">
            <span class="error" id="err_mobile"></span>
        </div>

        <div class="form-group">
            <label>Gender <span class="required">*</span></label>
            <div class="radio-group">
                <?php foreach (GENDERS as $gender): ?>
                    <label>
                        <input type="radio" name="gender" value="<?php echo $gender; ?>"
                            <?php echo ($values['gender'] === $gender) ? 'checked' : ''; ?>>
                        <?php echo $gender; ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <span class="error" id="err_gender"></span>
        </div>

        <div class="form-group">
            <label for="address">Address <span class="required">*</span></label>
            <textarea id="address" name="address" rows="3"><?php echo e($values['address']); ?></textarea>
            <span class="error" id="err_address"></span>
        </div>

        <!-- Current photo + optional new photo -->
        <div class="form-group">
            <label>Current Profile Image</label>
            <?php if (!empty($student['profile_image']) && is_file(__DIR__ . '/' . $student['profile_image'])): ?>
                <img src="<?php echo e($student['profile_image']); ?>" class="profile-img" alt="Current photo">
            <?php else: ?>
                <span class="no-image large">No Image</span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="profile_image">Change Profile Image (optional)</label>
            <input type="file" id="profile_image" name="profile_image" accept=".jpg,.jpeg,.png"
                   onchange="previewImage(this, 'imagePreview')">
            <span class="hint">Allowed: JPG, JPEG, PNG &nbsp;|&nbsp; Maximum size: 2 MB</span>
            <img id="imagePreview" class="preview-img" src="" alt="New image preview">
            <span class="error" id="err_profile_image"></span>
        </div>

        <div class="button-row">
            <input type="submit" class="btn btn-primary" value="Update">
            <a href="student_details.php?id=<?php echo $student['student_id']; ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
