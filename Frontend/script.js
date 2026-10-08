/* =====================================================================
   File    : script.js
   Purpose : Client-side (browser) validation for the Student
             Registration Form and the Edit Student form.
             The same rules are checked again on the server in PHP,
             because JavaScript can be switched off by the user.
   ===================================================================== */

// ---------------- Validation rules (regular expressions) ----------------

// Name: only letters, spaces and dots, 3 to 100 characters
var NAME_PATTERN = /^[A-Za-z][A-Za-z .]{2,99}$/;

// Roll number format: 2-digit year + 2 to 4 letter department + 3-digit number
// Examples: 22CSE001, 23IT045, 21ECE120
var ROLL_PATTERN = /^[0-9]{2}[A-Z]{2,4}[0-9]{3}$/;

// Simple e-mail format: something@something.domain
var EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[A-Za-z]{2,}$/;

// Password: min 8 chars, at least one uppercase, one lowercase and one digit
var PASSWORD_PATTERN = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}$/;

// Mobile: exactly 10 digits
var MOBILE_PATTERN = /^[0-9]{10}$/;

// File rules
var MAX_FILE_SIZE = 2 * 1024 * 1024;                       // 2 MB in bytes
var IMAGE_EXTENSIONS = ["jpg", "jpeg", "png"];             // profile photo
var DOCUMENT_EXTENSIONS = ["jpg", "jpeg", "png", "pdf"];   // certificate

// ---------------- Helper functions ----------------

// Show an error message below a field and mark the field red
function showError(fieldId, message) {
    var errorSpan = document.getElementById("err_" + fieldId);
    if (errorSpan) {
        errorSpan.innerHTML = message;
    }
    var field = document.getElementById(fieldId);
    if (field) {
        field.classList.add("input-error");
    }
}

// Remove all previous error messages before validating again
function clearErrors() {
    var spans = document.querySelectorAll(".error");
    for (var i = 0; i < spans.length; i++) {
        spans[i].innerHTML = "";
    }
    var fields = document.querySelectorAll(".input-error");
    for (var j = 0; j < fields.length; j++) {
        fields[j].classList.remove("input-error");
    }
}

// Return the extension of a file name in lower case, e.g. "Photo.JPG" -> "jpg"
function getExtension(fileName) {
    var dotPosition = fileName.lastIndexOf(".");
    if (dotPosition === -1) {
        return "";
    }
    return fileName.substring(dotPosition + 1).toLowerCase();
}

// Validate one file input. Returns an error message or "" when valid.
function checkFile(fieldId, allowedExtensions, isRequired) {
    var input = document.getElementById(fieldId);
    if (!input) {
        return "";
    }

    // No file chosen
    if (input.files.length === 0) {
        return isRequired ? "Please choose a file." : "";
    }

    var file = input.files[0];
    var ext = getExtension(file.name);

    // File type validation
    if (allowedExtensions.indexOf(ext) === -1) {
        return "Only " + allowedExtensions.join(", ").toUpperCase() + " files are allowed.";
    }

    // File size validation
    if (file.size > MAX_FILE_SIZE) {
        return "File size must not exceed 2 MB (selected: " +
               (file.size / (1024 * 1024)).toFixed(2) + " MB).";
    }

    // Empty file
    if (file.size === 0) {
        return "The selected file is empty.";
    }
    return "";
}

// ---------------- Main validation function ----------------
// mode = "add"  -> every field is required (registration form)
// mode = "edit" -> password and files are optional (edit form)
function validateForm(mode) {
    clearErrors();
    var isValid = true;                // becomes false when any rule fails
    var isAdd = (mode !== "edit");

    // 1. Student Name
    var name = document.getElementById("name").value.trim();
    if (name === "") {
        showError("name", "Student name is required.");
        isValid = false;
    } else if (!NAME_PATTERN.test(name)) {
        showError("name", "Name must contain only letters/spaces (minimum 3 characters).");
        isValid = false;
    }

    // 2. Roll Number (converted to upper case before checking)
    var rollField = document.getElementById("roll_number");
    rollField.value = rollField.value.trim().toUpperCase();
    if (rollField.value === "") {
        showError("roll_number", "Roll number is required.");
        isValid = false;
    } else if (!ROLL_PATTERN.test(rollField.value)) {
        showError("roll_number", "Invalid format. Use YY + DEPT + 3 digits, e.g. 22CSE001.");
        isValid = false;
    }

    // 3. Email
    var email = document.getElementById("email").value.trim();
    if (email === "") {
        showError("email", "Email is required.");
        isValid = false;
    } else if (!EMAIL_PATTERN.test(email)) {
        showError("email", "Please enter a valid email address (e.g. name@gmail.com).");
        isValid = false;
    }

    // 4. Password (optional while editing - blank means "keep old password")
    var password = document.getElementById("password").value;
    if (password === "" && isAdd) {
        showError("password", "Password is required.");
        isValid = false;
    } else if (password !== "" && !PASSWORD_PATTERN.test(password)) {
        showError("password", "Password must be at least 8 characters with one uppercase, one lowercase and one number.");
        isValid = false;
    }

    // 5. Course dropdown
    var course = document.getElementById("course").value;
    if (course === "") {
        showError("course", "Please select a course.");
        isValid = false;
    }

    // 6. Mobile number
    var mobile = document.getElementById("mobile").value.trim();
    if (mobile === "") {
        showError("mobile", "Mobile number is required.");
        isValid = false;
    } else if (!MOBILE_PATTERN.test(mobile)) {
        showError("mobile", "Mobile number must contain exactly 10 digits.");
        isValid = false;
    }

    // 7. Gender radio buttons - at least one must be checked
    var genderChecked = document.querySelector('input[name="gender"]:checked');
    if (!genderChecked) {
        document.getElementById("err_gender").innerHTML = "Please select gender.";
        isValid = false;
    }

    // 8. Address
    var address = document.getElementById("address").value.trim();
    if (address === "") {
        showError("address", "Address is required.");
        isValid = false;
    } else if (address.length < 10) {
        showError("address", "Address must be at least 10 characters long.");
        isValid = false;
    }

    // 9. Profile image (jpg, jpeg, png only)
    var imageError = checkFile("profile_image", IMAGE_EXTENSIONS, isAdd);
    if (imageError !== "") {
        showError("profile_image", imageError);
        isValid = false;
    }

    // 10. Certificate (pdf, jpg, jpeg, png) - only present on the registration form
    if (document.getElementById("certificate")) {
        var certError = checkFile("certificate", DOCUMENT_EXTENSIONS, isAdd);
        if (certError !== "") {
            showError("certificate", certError);
            isValid = false;
        }
    }

    // Scroll to the first error so the user can see it
    if (!isValid) {
        var firstError = document.querySelector(".input-error");
        if (firstError) {
            firstError.focus();
        }
        alert("Please correct the highlighted errors.");
    }

    // Returning false stops the form from being submitted
    return isValid;
}

// ---------------- Validation for the separate Upload File form ----------------
function validateUploadForm() {
    clearErrors();
    var isValid = true;

    if (document.getElementById("student_id").value === "") {
        showError("student_id", "Please select a student.");
        isValid = false;
    }

    var fileError = checkFile("upload_file", DOCUMENT_EXTENSIONS, true);
    if (fileError !== "") {
        showError("upload_file", fileError);
        isValid = false;
    }
    return isValid;
}

// ---------------- Image preview ----------------
// Shows the chosen profile photo before uploading
function previewImage(input, previewId) {
    var preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        var ext = getExtension(input.files[0].name);
        if (IMAGE_EXTENSIONS.indexOf(ext) !== -1) {
            var reader = new FileReader();          // reads the file inside the browser
            reader.onload = function (event) {
                preview.src = event.target.result;  // data URL of the image
                preview.style.display = "block";
            };
            reader.readAsDataURL(input.files[0]);
            return;
        }
    }
    // Not an image -> hide preview
    preview.src = "";
    preview.style.display = "none";
}

// ---------------- Reset button ----------------
// Clears error messages and hides the preview when the form is reset
function resetForm() {
    clearErrors();
    var preview = document.getElementById("imagePreview");
    if (preview) {
        preview.src = "";
        preview.style.display = "none";
    }
    return true;   // allow the normal reset to continue
}

// ---------------- Delete confirmation ----------------
function confirmDelete(studentName) {
    return confirm("Are you sure you want to delete the record of " + studentName +
                   "?\nAll uploaded files of this student will also be deleted.");
}
