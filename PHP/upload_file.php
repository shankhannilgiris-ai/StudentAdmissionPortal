<?php
/* =====================================================================
   File    : upload_file.php
   Purpose : Upload Files - lets the user upload an extra document or
             image (certificate, mark sheet, ID proof ...) for any student.
             GET  request -> shows the upload form
             POST request -> validates and saves the file, then inserts a
                             row into student_files
   Rules   : jpg, jpeg, png, pdf only | max 2 MB | timestamp file names
             images -> uploads/images/   pdf -> uploads/documents/
   ===================================================================== */
require_once 'db.php';

$errors = [];

// =================== Handle the form submission ===================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Upload bigger than post_max_size -> $_POST and $_FILES are empty
    if (empty($_POST)) {
        $errors[] = 'The file is too large. Maximum allowed size is 2 MB.';
    } else {
        $studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);

        // 1. Check that the student exists
        $studentExists = false;
        if ($studentId) {
            try {
                $stmt = $conn->prepare('SELECT student_id FROM students WHERE student_id = ?');
                $stmt->bind_param('i', $studentId);
                $stmt->execute();
                $studentExists = ($stmt->get_result()->num_rows === 1);
                $stmt->close();
            } catch (mysqli_sql_exception $ex) {
                $errors[] = 'Database error: ' . $ex->getMessage();
            }
        }
        if (!$studentExists) {
            $errors[] = 'Please select a valid student.';
        }

        // 2. File validation (type + size)
        $file      = $_FILES['upload_file'] ?? null;
        $fileError = validate_upload($file, ALL_EXTENSIONS, 'File', true);
        if ($fileError !== '') {
            $errors[] = $fileError;
        }

        // 3. Save the file and insert the record
        if (empty($errors)) {
            $path = save_upload($file);   // returns uploads/images/... or uploads/documents/...
            if ($path === false) {
                $errors[] = 'Could not save the file. Check that the uploads folder is writable.';
            } else {
                try {
                    $originalName = basename($file['name']);
                    $fileType     = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    $stmt = $conn->prepare('INSERT INTO student_files (student_id, file_name, file_path, file_type)
                                            VALUES (?, ?, ?, ?)');
                    $stmt->bind_param('isss', $studentId, $originalName, $path, $fileType);
                    $stmt->execute();
                    $stmt->close();

                    set_flash('success', 'File "' . $originalName . '" uploaded successfully.');
                    redirect('student_details.php?id=' . $studentId);
                } catch (mysqli_sql_exception $ex) {
                    delete_upload($path);   // database failed -> remove the saved file
                    $errors[] = 'Database error: ' . $ex->getMessage();
                }
            }
        }
    }
}

// =================== Data for the form ===================
// Student pre-selected when coming from "Upload" button (upload_file.php?student_id=3)
$selectedId = (int)($_POST['student_id'] ?? $_GET['student_id'] ?? 0);

// All students for the dropdown
$students = [];
try {
    $result = $conn->query('SELECT student_id, name, roll_number FROM students ORDER BY name');
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
} catch (mysqli_sql_exception $ex) {
    $errors[] = 'Could not load students: ' . $ex->getMessage();
}

$pageTitle = 'Upload Files';
include 'header.php';
?>

<div class="card form-card">
    <h2>Upload Student Document</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (empty($students)): ?>
        <div class="alert alert-info">No students registered yet. <a href="add_student.php">Add a student</a> first.</div>
    <?php else: ?>

    <form action="upload_file.php" method="post" enctype="multipart/form-data"
          onsubmit="return validateUploadForm()" novalidate>

        <!-- Select the student who owns the file -->
        <div class="form-group">
            <label for="student_id">Student <span class="required">*</span></label>
            <select id="student_id" name="student_id">
                <option value="">-- Select Student --</option>
                <?php foreach ($students as $s): ?>
                    <option value="<?php echo $s['student_id']; ?>"
                        <?php echo ($selectedId === (int)$s['student_id']) ? 'selected' : ''; ?>>
                        <?php echo e($s['roll_number'] . ' - ' . $s['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="error" id="err_student_id"></span>
        </div>

        <!-- Choose the file -->
        <div class="form-group">
            <label for="upload_file">Choose File <span class="required">*</span></label>
            <input type="file" id="upload_file" name="upload_file" accept=".pdf,.jpg,.jpeg,.png">
            <span class="hint">Allowed: PDF, JPG, JPEG, PNG &nbsp;|&nbsp; Maximum size: 2 MB</span>
            <span class="error" id="err_upload_file"></span>
        </div>

        <div class="button-row">
            <input type="submit" class="btn btn-primary" value="Upload">
            <input type="reset" class="btn btn-secondary" value="Reset" onclick="clearErrors()">
        </div>
    </form>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
