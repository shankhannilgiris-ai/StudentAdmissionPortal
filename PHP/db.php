<?php
/* =====================================================================
   File    : db.php
   Purpose : 1) Connects to the MySQL database "student_portal".
             2) Holds common settings (upload folders, size limit).
             3) Holds small helper functions used by every page
                (escaping output, flash messages, validation, uploads).
   Usage   : require_once 'db.php';  at the top of every PHP page.
   ===================================================================== */

// Start the session once - used to carry success / error messages between pages.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------------- Database settings (change if your MySQL is different) ----------------
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // XAMPP default user
define('DB_PASS', '');              // XAMPP default password is empty
define('DB_NAME', 'student_portal');

// ---------------- Upload settings ----------------
define('UPLOAD_IMAGE_DIR', 'uploads/images/');        // profile photos and image files
define('UPLOAD_DOC_DIR', 'uploads/documents/');       // pdf certificates / documents
define('MAX_FILE_SIZE', 2 * 1024 * 1024);             // 2 MB in bytes
define('IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png']);
define('ALL_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);

// List of courses shown in the dropdown (also used for server-side checking)
define('COURSES', [
    'B.E Computer Science and Engineering',
    'B.E Electronics and Communication Engineering',
    'B.E Electrical and Electronics Engineering',
    'B.E Mechanical Engineering',
    'B.E Civil Engineering',
    'B.Tech Information Technology',
]);

define('GENDERS', ['Male', 'Female', 'Other']);

// ---------------- Connect to MySQL ----------------
// Make mysqli throw exceptions on every SQL error so we can catch them.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');   // support all characters
} catch (mysqli_sql_exception $ex) {
    // SQL error handling: show a friendly message instead of a PHP crash.
    http_response_code(500);
    echo '<div style="font-family:Arial;max-width:600px;margin:60px auto;padding:20px;'
       . 'border:1px solid #e57373;background:#fdecea;color:#b71c1c;border-radius:6px;">'
       . '<h3>Database connection failed</h3>'
       . '<p>Please make sure MySQL is running and the database <b>student_portal</b> '
       . 'has been imported from <b>Database/student_portal.sql</b>.</p>'
       . '<p><small>Error: ' . htmlspecialchars($ex->getMessage()) . '</small></p></div>';
    exit;
}

/* =====================================================================
   Helper functions
   ===================================================================== */

// Escape text before printing it in HTML - prevents XSS attacks.
function e($text)
{
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

// Save a message in the session; it is shown once by header.php.
function set_flash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Redirect to another page and stop the script.
function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

// Server-side validation of the student form fields.
// $isEdit = true -> password may be blank (keep old password).
// Returns an array of error messages (empty array = valid).
function validate_student($data, $isEdit = false)
{
    $errors = [];

    // Name: letters, spaces, dots, 3-100 characters
    if ($data['name'] === '') {
        $errors[] = 'Student name is required.';
    } elseif (!preg_match('/^[A-Za-z][A-Za-z .]{2,99}$/', $data['name'])) {
        $errors[] = 'Name must contain only letters and spaces (minimum 3 characters).';
    }

    // Roll number format, e.g. 22CSE001
    if ($data['roll_number'] === '') {
        $errors[] = 'Roll number is required.';
    } elseif (!preg_match('/^[0-9]{2}[A-Z]{2,4}[0-9]{3}$/', $data['roll_number'])) {
        $errors[] = 'Roll number format is invalid. Example: 22CSE001.';
    }

    // Email format using PHP built-in filter
    if ($data['email'] === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email address is not valid.';
    }

    // Password strength
    if ($data['password'] === '' && !$isEdit) {
        $errors[] = 'Password is required.';
    } elseif ($data['password'] !== '' &&
              !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}$/', $data['password'])) {
        $errors[] = 'Password must be at least 8 characters with one uppercase, one lowercase and one number.';
    }

    // Course must be one of the allowed values
    if (!in_array($data['course'], COURSES, true)) {
        $errors[] = 'Please select a valid course.';
    }

    // Mobile: exactly 10 digits
    if (!preg_match('/^[0-9]{10}$/', $data['mobile'])) {
        $errors[] = 'Mobile number must contain exactly 10 digits.';
    }

    // Gender must be one of the radio values
    if (!in_array($data['gender'], GENDERS, true)) {
        $errors[] = 'Please select gender.';
    }

    // Address
    if (strlen($data['address']) < 10) {
        $errors[] = 'Address is required (minimum 10 characters).';
    }

    return $errors;
}

// Read the student form fields from $_POST and trim them.
function read_student_form()
{
    return [
        'name'        => trim($_POST['name'] ?? ''),
        'roll_number' => strtoupper(trim($_POST['roll_number'] ?? '')),
        'email'       => strtolower(trim($_POST['email'] ?? '')),
        'password'    => $_POST['password'] ?? '',
        'course'      => $_POST['course'] ?? '',
        'mobile'      => trim($_POST['mobile'] ?? ''),
        'gender'      => $_POST['gender'] ?? '',
        'address'     => trim($_POST['address'] ?? ''),
    ];
}

// Check whether a roll number or e-mail is already used by another student.
// $excludeId is used while editing so the student does not clash with himself.
function find_duplicates($conn, $roll, $email, $excludeId = 0)
{
    $errors = [];
    $stmt = $conn->prepare('SELECT roll_number, email FROM students
                            WHERE (roll_number = ? OR email = ?) AND student_id <> ?');
    $stmt->bind_param('ssi', $roll, $email, $excludeId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if ($row['roll_number'] === $roll) {
            $errors[] = "Roll number $roll is already registered.";
        }
        if (strtolower($row['email']) === $email) {
            $errors[] = "Email $email is already registered.";
        }
    }
    $stmt->close();
    return array_unique($errors);
}

// Validate an uploaded file (type + size + upload errors).
// Returns an error message, or '' when the file is valid / optional and empty.
function validate_upload($file, $allowedExt, $label, $required = true)
{
    // Field missing or no file chosen
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return $required ? "$label is required." : '';
    }

    // PHP level upload errors (e.g. larger than upload_max_filesize)
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return "$label must not exceed 2 MB.";
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return "$label could not be uploaded (error code {$file['error']}).";
    }

    // File size validation
    if ($file['size'] <= 0) {
        return "$label is empty.";
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        return "$label must not exceed 2 MB.";
    }

    // File type validation (1): extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return "$label: only " . strtoupper(implode(', ', $allowedExt)) . ' files are allowed.';
    }

    // File type validation (2): real content (MIME type) so a renamed .exe is rejected
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $allowedMime = [
            'jpg'  => ['image/jpeg', 'image/pjpeg'],
            'jpeg' => ['image/jpeg', 'image/pjpeg'],
            'png'  => ['image/png'],
            'pdf'  => ['application/pdf'],
        ];
        if (!in_array($mime, $allowedMime[$ext], true)) {
            return "$label: file content does not match its extension.";
        }
    }
    return '';
}

// Move an already validated file into uploads/images or uploads/documents.
// A timestamp is added to the name so an existing file is never overwritten.
// Returns the relative path stored in the database, e.g. uploads/images/1712345678_4821_photo.jpg
function save_upload($file)
{
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Images go to uploads/images/, PDFs go to uploads/documents/
    $folder = in_array($ext, IMAGE_EXTENSIONS, true) ? UPLOAD_IMAGE_DIR : UPLOAD_DOC_DIR;

    // Create the folder if it does not exist yet
    if (!is_dir(__DIR__ . '/' . $folder)) {
        mkdir(__DIR__ . '/' . $folder, 0755, true);
    }

    // Clean the original name: keep only letters, digits, - and _
    $base = pathinfo($file['name'], PATHINFO_FILENAME);
    $base = preg_replace('/[^A-Za-z0-9_-]/', '_', $base);
    $base = substr($base, 0, 50);

    // timestamp + random number + clean name  -> unique file name
    do {
        $newName = time() . '_' . mt_rand(1000, 9999) . '_' . $base . '.' . $ext;
    } while (file_exists(__DIR__ . '/' . $folder . $newName));

    // move_uploaded_file() also checks that the file really came from an HTTP upload
    if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/' . $folder . $newName)) {
        return false;
    }
    return $folder . $newName;
}

// Delete a stored file from the disk (used when a student / image is removed).
function delete_upload($relativePath)
{
    if ($relativePath === null || $relativePath === '') {
        return;
    }
    // Only allow deleting inside the uploads folder (safety check)
    if (strpos($relativePath, 'uploads/') !== 0 || strpos($relativePath, '..') !== false) {
        return;
    }
    $fullPath = __DIR__ . '/' . $relativePath;
    if (is_file($fullPath)) {
        unlink($fullPath);
    }
}
