<?php
/* =====================================================================
   File    : add_student.php
   Purpose : Shows the Student Registration Form (Add Student feature).
             The form is submitted to insert_student.php.
             If the server finds errors, insert_student.php sends the user
             back here and the errors + previously typed values are shown.
   ===================================================================== */
require_once 'db.php';

// Errors and old values saved by insert_student.php (if any)
$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['old_input'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_input']);   // show only once

// Small helper: returns the previously typed value of a field (escaped)
function old($old, $field)
{
    return e($old[$field] ?? '');
}

$pageTitle = 'Add Student';
include 'header.php';
?>

<div class="card form-card">
    <h2>Student Registration Form</h2>

    <?php if (!empty($errors)): ?>
        <!-- Server-side validation errors -->
        <div class="alert alert-error">
            <strong>Please correct the following errors:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <p class="hint">Fields marked with <span class="required">*</span> are mandatory.</p>
    <br>

    <!-- enctype="multipart/form-data" is required for sending files -->
    <form action="insert_student.php" method="post" enctype="multipart/form-data"
          onsubmit="return validateForm('add')" onreset="return resetForm()" novalidate>

        <!-- 1. Student Name -->
        <div class="form-group">
            <label for="name">Student Name <span class="required">*</span></label>
            <input type="text" id="name" name="name" maxlength="100" placeholder="Enter full name"
                   value="<?php echo old($old, 'name'); ?>">
            <span class="error" id="err_name"></span>
        </div>

        <!-- 2. Roll Number -->
        <div class="form-group">
            <label for="roll_number">Roll Number <span class="required">*</span></label>
            <input type="text" id="roll_number" name="roll_number" maxlength="20" placeholder="e.g. 22CSE001"
                   value="<?php echo old($old, 'roll_number'); ?>">
            <span class="hint">Format: Year (2 digits) + Department (2-4 letters) + Number (3 digits)</span>
            <span class="error" id="err_roll_number"></span>
        </div>

        <!-- 3. Email -->
        <div class="form-group">
            <label for="email">Email <span class="required">*</span></label>
            <input type="email" id="email" name="email" maxlength="100" placeholder="name@example.com"
                   value="<?php echo old($old, 'email'); ?>">
            <span class="error" id="err_email"></span>
        </div>

        <!-- 4. Password (never re-filled for security) -->
        <div class="form-group">
            <label for="password">Password <span class="required">*</span></label>
            <input type="password" id="password" name="password" maxlength="50" placeholder="Enter a strong password">
            <span class="hint">Minimum 8 characters, one uppercase, one lowercase and one number.</span>
            <span class="error" id="err_password"></span>
        </div>

        <!-- 5. Course Dropdown - options generated from the COURSES list in db.php -->
        <div class="form-group">
            <label for="course">Course <span class="required">*</span></label>
            <select id="course" name="course">
                <option value="">-- Select Course --</option>
                <?php foreach (COURSES as $course): ?>
                    <option value="<?php echo e($course); ?>"
                        <?php echo (($old['course'] ?? '') === $course) ? 'selected' : ''; ?>>
                        <?php echo e($course); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="error" id="err_course"></span>
        </div>

        <!-- 6. Mobile Number -->
        <div class="form-group">
            <label for="mobile">Mobile Number <span class="required">*</span></label>
            <input type="text" id="mobile" name="mobile" maxlength="10" placeholder="10 digit mobile number"
                   value="<?php echo old($old, 'mobile'); ?>">
            <span class="error" id="err_mobile"></span>
        </div>

        <!-- 7. Gender Radio Buttons -->
        <div class="form-group">
            <label>Gender <span class="required">*</span></label>
            <div class="radio-group">
                <?php foreach (GENDERS as $gender): ?>
                    <label>
                        <input type="radio" name="gender" value="<?php echo $gender; ?>"
                            <?php echo (($old['gender'] ?? '') === $gender) ? 'checked' : ''; ?>>
                        <?php echo $gender; ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <span class="error" id="err_gender"></span>
        </div>

        <!-- 8. Address Textarea -->
        <div class="form-group">
            <label for="address">Address <span class="required">*</span></label>
            <textarea id="address" name="address" rows="3"
                      placeholder="Door no, street, city, pincode"><?php echo old($old, 'address'); ?></textarea>
            <span class="error" id="err_address"></span>
        </div>

        <!-- 9. Profile Image Upload -->
        <div class="form-group">
            <label for="profile_image">Profile Image <span class="required">*</span></label>
            <input type="file" id="profile_image" name="profile_image" accept=".jpg,.jpeg,.png"
                   onchange="previewImage(this, 'imagePreview')">
            <span class="hint">Allowed: JPG, JPEG, PNG &nbsp;|&nbsp; Maximum size: 2 MB</span>
            <img id="imagePreview" class="preview-img" src="" alt="Image preview">
            <span class="error" id="err_profile_image"></span>
        </div>

        <!-- 10. Certificate Upload -->
        <div class="form-group">
            <label for="certificate">Certificate <span class="required">*</span></label>
            <input type="file" id="certificate" name="certificate" accept=".pdf,.jpg,.jpeg,.png">
            <span class="hint">Allowed: PDF, JPG, JPEG, PNG &nbsp;|&nbsp; Maximum size: 2 MB</span>
            <span class="error" id="err_certificate"></span>
        </div>

        <!-- 11. Submit and 12. Reset -->
        <div class="button-row">
            <input type="submit" class="btn btn-primary" value="Submit">
            <input type="reset" class="btn btn-secondary" value="Reset">
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
