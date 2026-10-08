# Project Report
## Student Admission Portal with File Management System

**Submitted by:** _Your Name_ (_Register Number_)
**Department:** Computer Science and Engineering
**Course:** B.E CSE
**Subject:** Web Technology / Internet Programming Laboratory
**Academic Year:** 2026

---

## 1. Introduction

Most colleges still collect admission details and certificates on paper. The forms are hard to store, slow to search, and easy to lose. The **Student Admission Portal with File Management System** is a web application that replaces this process. A student fills in an online registration form and uploads a profile photo and a certificate. The system validates the data, stores it in a MySQL database, and keeps the uploaded files in organised folders on the server.

The project shows three common web development approaches working on the **same database**:

1. **Static front end** (HTML, CSS, JavaScript): the user interface and browser-side validation.
2. **PHP module**: server-side processing and full CRUD (Create, Read, Update, Delete) operations, including file uploads.
3. **Java EE module** (JSP, Servlet, JDBC): an admin panel with a login system, session management, and file streaming through a Servlet.

---

## 2. Objectives

1. Design a simple, clean, and responsive student registration form using HTML and CSS.
2. Validate every input on the client side with JavaScript and again on the server side with PHP.
3. Store student data in a normalised MySQL database with primary and foreign keys.
4. Upload images and documents safely: allow only certain file types and sizes, and use unique file names.
5. Provide Add, View, Edit, Delete, and Search operations on student records.
6. Display student photos and provide download links for documents.
7. Build a Java web application that uses JDBC, the DAO pattern, and MVC-style Servlets and JSPs.
8. Restrict admin pages with HTTP sessions, and provide login and logout.
9. Stream images and files to the browser through a Servlet (`FileServlet`).

---

## 3. Software Requirements

| Software | Version / Details |
|---|---|
| Operating System | Windows 10/11, Linux, or macOS |
| Web Server | Apache 2.4 (bundled with XAMPP) |
| Server-side Language | PHP 8.0 or above |
| Database | MySQL 8.0 / MariaDB 10.4 or above (XAMPP) |
| Java | JDK 11 or above |
| Servlet Container | Apache Tomcat 9.x |
| JDBC Driver | MySQL Connector/J 8.x |
| IDE | Eclipse IDE for Enterprise Java, VS Code |
| Browser | Google Chrome, Mozilla Firefox, or Microsoft Edge |
| DB Tool | phpMyAdmin |

## 4. Hardware Requirements

| Component | Minimum |
|---|---|
| Processor | Intel Core i3 / AMD Ryzen 3 or above |
| RAM | 4 GB (8 GB recommended when Eclipse and Tomcat run together) |
| Hard Disk | 2 GB of free space |
| Display | 1366 × 768 resolution |
| Network | Required only for installing software; the project runs on localhost |

---

## 5. System Architecture

The system follows a **three-tier architecture**:

```
                    ┌────────────────────────────────────────┐
  PRESENTATION      │  Web Browser                           │
  TIER              │  HTML forms + CSS + JavaScript         │
                    └───────────────┬────────────────────────┘
                                    │ HTTP request / response
            ┌───────────────────────┴───────────────────────────┐
            │                                                   │
 ┌──────────▼──────────────┐                     ┌──────────────▼──────────────┐
 │ APPLICATION TIER (1)    │                     │ APPLICATION TIER (2)        │
 │ Apache + PHP            │                     │ Apache Tomcat               │
 │ add / insert / view /   │                     │ LoginServlet  ─► Session    │
 │ edit / update / delete /│                     │ DashboardServlet            │
 │ upload / details pages  │                     │ StudentListServlet          │
 │        │                │                     │ StudentProfileServlet       │
 │        ▼                │                     │ FileServlet ──────┐         │
 │  move_uploaded_file()   │                     │   DAO classes     │         │
 └────────┬───────┬────────┘                     └──────┬────────────┼─────────┘
          │mysqli │ writes files                   JDBC │            │ reads files
          │       ▼                                     │            ▼
          │   ┌──────────────────────────────┐          │   (same uploads folder)
          │   │ File System: PHP/uploads/    │◄─────────┼────────────┘
          │   │   images/     documents/     │          │
          │   └──────────────────────────────┘          │
          ▼                                             ▼
 ┌────────────────────────────────────────────────────────────────┐
 │ DATA TIER : MySQL database "student_portal"                    │
 │   students  ◄──(FK student_id)──  student_files   admin_users  │
 └────────────────────────────────────────────────────────────────┘
```

**Flow of a registration:**
Browser form → `script.js` validation → POST to `insert_student.php` → server validation → files moved to `uploads/` → `INSERT` into `students` and `student_files` (in one transaction) → redirect to `student_details.php` with a success message.

**Flow in the Servlet module (MVC):**
Browser → **Servlet (Controller)** checks the session → calls the **DAO** → the DAO uses **DBConnection (JDBC)** → returns **Model** objects → the Servlet stores them as request attributes → forwards to the **JSP (View)** → the JSP loops over the objects and builds HTML.

---

## 6. Database Design

**Database name:** `student_portal`

### Table 1: `students`

| Column | Data Type | Constraint | Description |
|---|---|---|---|
| student_id | INT | **PRIMARY KEY**, AUTO_INCREMENT | Unique student id |
| name | VARCHAR(100) | NOT NULL | Full name |
| roll_number | VARCHAR(20) | NOT NULL, **UNIQUE** | Format `22CSE001` |
| email | VARCHAR(100) | NOT NULL, **UNIQUE** | E-mail address |
| password | VARCHAR(255) | NOT NULL | bcrypt hash |
| course | VARCHAR(50) | NOT NULL | Selected course |
| mobile | CHAR(10) | NOT NULL | 10-digit mobile number |
| gender | ENUM('Male','Female','Other') | NOT NULL | Gender |
| address | TEXT | NOT NULL | Address |
| profile_image | VARCHAR(255) | NULL | Path of the photo |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Registration time |

### Table 2: `student_files`

| Column | Data Type | Constraint | Description |
|---|---|---|---|
| file_id | INT | **PRIMARY KEY**, AUTO_INCREMENT | Unique file id |
| student_id | INT | **FOREIGN KEY** → students(student_id), ON DELETE CASCADE | Owner |
| file_name | VARCHAR(255) | NOT NULL | Original file name |
| file_path | VARCHAR(255) | NOT NULL | Stored path |
| file_type | VARCHAR(10) | NOT NULL | jpg / jpeg / png / pdf |
| upload_date | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Upload time |

### Table 3: `admin_users` (supporting table for the Servlet login)

| Column | Data Type | Constraint |
|---|---|---|
| admin_id | INT | PRIMARY KEY, AUTO_INCREMENT |
| username | VARCHAR(50) | UNIQUE, NOT NULL |
| password | CHAR(64) | SHA-256 hash |
| full_name | VARCHAR(100) | NOT NULL |

### E-R relationship

```
 ┌────────────┐ 1          N ┌───────────────┐
 │  students  │──────────────│ student_files │
 └────────────┘   has files  └───────────────┘
```
One student can have **many** files, and each file belongs to **exactly one** student (a one-to-many relationship). Because of `ON DELETE CASCADE`, deleting a student automatically deletes that student's file records.

> **Note on the `password` column:** the assignment's table list does not include a password column, but the registration form collects a password. The project therefore stores it in `students.password` as a secure bcrypt hash and never displays it.

---

## 7. Module Description

### 7.1 Frontend module (`Frontend/`)
- **index.html**: the registration form with all 12 required elements (10 fields plus the Submit and Reset buttons). It uses `enctype="multipart/form-data"` for the file uploads and posts to `../PHP/insert_student.php`.
- **style.css**: a card layout centred on the page; styled inputs, buttons, error messages, alerts, and tables; a header and footer; and media queries for tablets (768px) and phones (480px).
- **script.js**: the `validateForm()` function checks:
  - that every field is filled in
  - the e-mail format, using a regular expression
  - password strength (at least 8 characters, with an uppercase letter, a lowercase letter, and a digit)
  - that the mobile number has exactly 10 digits
  - the roll number format `^[0-9]{2}[A-Z]{2,4}[0-9]{3}$`
  - the file extension and that the file is 2 MB or smaller

  It also previews the chosen image (`previewImage()`), clears errors when the form is reset, and asks for confirmation before a delete.

### 7.2 PHP module (`PHP/`)

| Feature | File(s) | Description |
|---|---|---|
| Add Student | add_student.php → insert_student.php | Validates the input, checks that the roll number and e-mail are unique, validates and saves both files, then inserts into both tables in one transaction |
| View Students | view_students.php | Table of all students with photo thumbnails and action buttons |
| Search Student | view_students.php?search= | Uses `LIKE` on name, roll number, e-mail, and course |
| Edit Student | edit_student.php → update_student.php | Pre-filled form. The password and photo are optional. When the photo changes, the old file is deleted |
| Delete Student | delete_student.php | POST only, with a JavaScript confirm. Deletes the database rows (CASCADE) and the physical files |
| Upload Files | upload_file.php | Choose a student and a file. Images go to `uploads/images/` and PDFs to `uploads/documents/` |
| View Uploaded Files | student_details.php | Profile with the photo, and a file table with preview, View, and Download links |

### 7.3 Servlet module (`Servlet/StudentPortalServlet/`)

| Layer | Classes / Pages | Responsibility |
|---|---|---|
| Utility | `DBConnection`, `SessionUtil` | JDBC connection; session check, no-cache headers, HTML escaping |
| Model | `Admin`, `Student`, `StudentFile` | JavaBeans that represent table rows |
| DAO | `AdminDAO`, `StudentDAO`, `StudentFileDAO` | All SQL, written with PreparedStatement |
| Controller | `LoginServlet`, `LogoutServlet`, `DashboardServlet`, `StudentListServlet`, `StudentProfileServlet`, `FileServlet` | Handle requests, check the session, call DAOs, and forward to JSPs |
| View | `login.jsp`, `dashboard.jsp`, `student_list.jsp`, `student_profile.jsp`, `error.jsp` | Show data using loops (`for`) and conditions (`if`/`else`) |
| Config | `web.xml` | Servlet mappings, session timeout, welcome file, error pages, upload folder path |

**Session security:**
- `LoginServlet` invalidates any old session and creates a new one (prevents session fixation).
- Every protected Servlet calls `SessionUtil.checkLogin()`, and `header.jspf` repeats the check in the JSP.
- The JSP views are kept inside `WEB-INF/views`, so a browser cannot open them directly.
- `LogoutServlet` calls `session.invalidate()`, and no-cache headers stop the Back button from showing protected pages.

**FileServlet:** reads `file_path` from the database, joins it with `uploadBaseDir` from `web.xml`, and checks that the canonical path stays inside that folder (prevents path traversal). It then sets the `Content-Type` and `Content-Disposition` headers and copies the file to the response in 8 KB blocks.

---

## 8. Screenshots Section

_(Insert the actual screenshots below each description.)_

1. **Registration form (Frontend/index.html):** a blue header with the title "Student Admission Portal", a dark-blue navigation bar, and a white card in the centre of the page titled "Student Registration Form". It contains the Name, Roll Number, Email, Password, Course dropdown, Mobile, Gender radio buttons, Address, Profile Image, and Certificate fields, with blue **Submit** and gray **Reset** buttons. A blue footer appears at the bottom.
2. **Client-side validation errors:** the form submitted empty or with wrong values. Invalid fields have red borders and red messages below them, such as "Mobile number must contain exactly 10 digits." and "Only JPG, JPEG, PNG files are allowed.", and an alert box asks the user to correct the errors.
3. **Image preview:** after a photo is chosen, a 100×100 thumbnail appears below the Profile Image field.
4. **Successful registration / Student Details:** a green message reads "Student Arun Kumar (22CSE001) registered successfully." Below it is the profile card, with the 150×150 photo on the left and a details table on the right, followed by the "Uploaded Files" table with View and Download buttons.
5. **Server-side error:** registering the same roll number twice shows a red box reading "Roll number 22CSE001 is already registered." The other fields keep the values that were typed.
6. **View Students:** a blue-headed table with striped rows showing S.No, Photo thumbnail, Name, Roll Number, Email, Course, Mobile, Gender, Registered On, and the View, Edit, Upload, and Delete buttons.
7. **Search:** the keyword "CSE" in the search box. Only the matching students are listed, with a line such as "1 result(s) found for 'CSE'".
8. **Edit Student:** the form pre-filled with existing data, the current profile photo, an optional new photo field, and Update and Cancel buttons.
9. **Delete confirmation:** the browser dialog "Are you sure you want to delete the record of Priya Sharma?", followed by a green success message.
10. **Upload Files:** a student dropdown, a file chooser, and an Upload button. Choosing a 3 MB file shows "File size must not exceed 2 MB".
11. **JSP login page:** a small centred card with Username, Password, and Login. Wrong credentials show "Invalid username or password."
12. **Dashboard:** three statistic boxes (Total Students, Uploaded Files, PDF Documents), the "Students per Course" table, and the "Recently Registered Students" table with photos streamed by `FileServlet`.
13. **Student List (Servlet):** the same kind of table, generated by `StudentListServlet` and the JSP loop.
14. **Student Profile (Servlet):** the photo loaded from `FileServlet?id=1`, the details table, and the documents table with View and Download links. Clicking Download saves the PDF.
15. **Unauthorized access:** opening `/StudentListServlet` after logout redirects to the login page with the message "Please login to continue."
16. **Mobile view:** at a width of 375px, the navigation links stack vertically, the buttons take the full width, and the table scrolls sideways.

---

## 9. Testing Results

> Run each test case on your own setup before submission and confirm the Status column (the expected results below match the implemented logic).

### 9.1 Client-side and server-side validation

| TC | Test Case | Input | Expected Result | Actual Result | Status |
|---|---|---|---|---|---|
| 1 | Empty form | All fields blank | "... is required" under each field | As expected | Pass |
| 2 | Invalid name | `A1` | Name error | As expected | Pass |
| 3 | Invalid e-mail | `arun@gmail` | "Please enter a valid email address" | As expected | Pass |
| 4 | Weak password | `abc12345` | Strength error (no uppercase) | As expected | Pass |
| 5 | Strong password | `Arun@2026` | Accepted | Accepted | Pass |
| 6 | Mobile with 9 digits | `987654321` | "exactly 10 digits" | As expected | Pass |
| 7 | Mobile with letters | `98765abcde` | "exactly 10 digits" | As expected | Pass |
| 8 | Roll number wrong format | `CSE22001` | Format error | As expected | Pass |
| 9 | Duplicate roll number | `22CSE001` (exists) | "already registered" (server) | As expected | Pass |
| 10 | Duplicate e-mail | existing e-mail | "already registered" (server) | As expected | Pass |
| 11 | Course not selected | default option | "Please select a course" | As expected | Pass |
| 12 | Gender not selected | none | "Please select gender" | As expected | Pass |
| 13 | JavaScript disabled + invalid data | invalid data | PHP shows the error list | As expected | Pass |

### 9.2 File upload

| TC | Test Case | Input | Expected Result | Status |
|---|---|---|---|---|
| 14 | Valid image | photo.jpg (500 KB) | Saved in `uploads/images/` with a timestamped name | Pass |
| 15 | Valid PDF | cert.pdf (1 MB) | Saved in `uploads/documents/` | Pass |
| 16 | Wrong type | notes.docx | "Only JPG, JPEG, PNG, PDF files are allowed" | Pass |
| 17 | Too large | scan.pdf (3 MB) | "must not exceed 2 MB" | Pass |
| 18 | Renamed file | virus.exe renamed to virus.jpg | "file content does not match its extension" | Pass |
| 19 | Same name twice | photo.jpg uploaded twice | Two different file names, nothing overwritten | Pass |
| 20 | PDF as profile image | cert.pdf in the photo field | Rejected (images only) | Pass |

### 9.3 CRUD operations

| TC | Operation | Expected Result | Status |
|---|---|---|---|
| 21 | Add student | Record inserted and 2 file rows created | Pass |
| 22 | View students | All records shown with photos | Pass |
| 23 | Search "ECE" | Only ECE students listed | Pass |
| 24 | Edit without a new password or photo | Old password and photo kept | Pass |
| 25 | Edit with a new photo | New photo shown and old file deleted from disk | Pass |
| 26 | Delete student | Record, file rows, and physical files removed | Pass |
| 27 | Open `delete_student.php` directly in the URL bar | "Invalid request" and nothing is deleted | Pass |
| 28 | SQL injection in search `' OR '1'='1` | Treated as text, no extra rows returned | Pass |
| 29 | XSS in name `<script>` | Rejected by validation, and output is escaped anyway | Pass |

### 9.4 Servlet module

| TC | Test Case | Expected Result | Status |
|---|---|---|---|
| 30 | Login with admin / admin123 | Dashboard opens | Pass |
| 31 | Login with a wrong password | "Invalid username or password." | Pass |
| 32 | Open StudentListServlet without logging in | Redirected to login.jsp | Pass |
| 33 | `<img src="FileServlet?id=1">` | Profile photo displayed | Pass |
| 34 | `FileServlet?fileId=2&download=true` | Browser downloads the PDF | Pass |
| 35 | FileServlet without a session | 401 Unauthorized | Pass |
| 36 | Logout, then press Back | Redirected to the login page | Pass |
| 37 | Session idle for more than 30 minutes | Login required again | Pass |
| 38 | MySQL stopped | Friendly database error message | Pass |

---

## 10. Advantages

1. Paperless admission: no physical forms or certificate photocopies are needed.
2. Validation on both sides keeps the data clean and correct.
3. Fast search by name, roll number, e-mail, or course.
4. Uploaded files are organised automatically and never overwritten.
5. Secure: prepared statements, password hashing, output escaping, file content checks, session-protected admin pages, and a `.htaccess` file in `uploads/`.
6. Responsive design that works on mobiles, tablets, and desktops.
7. One database is shared by both the PHP and Java applications, which shows interoperability.
8. Simple, well-commented code that is easy to understand and extend.

## 11. Limitations

1. Only one admin role. Students cannot log in to see or edit their own profiles.
2. Files are stored on the local disk, so there is no cloud storage or backup.
3. Only JPG, JPEG, PNG, and PDF files up to 2 MB are supported.
4. No e-mail or SMS notification after registration.
5. The PHP module has no login of its own. It is meant to run on a trusted local network or behind an admin login.
6. No pagination, so very large lists will load slowly.
7. The Servlet module is read-only (view and download); editing is done in the PHP module.

## 12. Future Enhancements

1. Student login and a self-service profile page.
2. Role-based access (Admin, Staff, Student) for both modules.
3. E-mail or OTP verification of the e-mail address and mobile number.
4. Pagination and sorting in the student tables.
5. Reports exported to PDF or Excel (course-wise and date-wise).
6. Document verification status (Pending, Approved, Rejected) set by staff.
7. Cloud storage (for example AWS S3 or Google Drive) for uploaded files.
8. Online payment of the admission fee.
9. CAPTCHA on the registration form to stop automated spam.
10. Deployment on a public server with HTTPS.

## 13. Conclusion

The **Student Admission Portal with File Management System** meets all the requirements of the assignment. The HTML/CSS/JavaScript front end gives a clean, responsive registration form with complete client-side validation. The PHP module provides full CRUD operations, search, and secure file upload and download, with server-side validation and SQL error handling. The JSP/Servlet module uses JDBC, the DAO pattern, and MVC to build a session-protected admin panel that displays student profiles dynamically and streams images and documents through `FileServlet`.

Building this project gave practical experience in form design, regular expressions, HTTP file uploads, relational database design with primary and foreign keys, prepared statements, transactions, session management, and the Java web application structure (`web.xml`, Servlets, JSP). The modular design makes the system easy to maintain and extend with the features listed in Section 12.

---

## Appendix A: Explanation of Each File

### Database
| File | Explanation |
|---|---|
| `Database/student_portal.sql` | Creates the database and the 3 tables with PRIMARY KEY, UNIQUE, and FOREIGN KEY (ON DELETE CASCADE) constraints, plus an index. Inserts 1 admin, 3 students, and 6 file records whose files are included in `PHP/uploads/`. |

### Frontend
| File | Explanation |
|---|---|
| `Frontend/index.html` | Static registration form. `novalidate` turns off the browser's own popups so `script.js` messages are shown. `onsubmit="return validateForm('add')"` blocks submission when there are errors. |
| `Frontend/style.css` | All styling: reset, header and navigation, card, form fields, `.error` messages, alert boxes, buttons, tables, profile layout, footer, and two media queries. |
| `Frontend/script.js` | Regular-expression constants, `showError()` and `clearErrors()`, `checkFile()` for type and size, `validateForm(mode)` (the add and edit modes differ), `validateUploadForm()`, `previewImage()` using FileReader, `resetForm()`, and `confirmDelete()`. |

### PHP
| File | Explanation |
|---|---|
| `db.php` | Starts the session and defines DB constants, upload folders, the 2 MB limit, allowed extensions, and the course and gender lists. Connects with mysqli inside try/catch. Helper functions: `e()` (escape), `set_flash()`, `redirect()`, `validate_student()`, `read_student_form()`, `find_duplicates()`, `validate_upload()` (error code, size, extension, MIME type), `save_upload()` (timestamped unique name, chooses the images or documents folder), and `delete_upload()`. |
| `header.php` | HTML head, header, navigation bar, and the one-time flash message. |
| `footer.php` | Footer. Loads `script.js` and closes the DB connection. |
| `index.php` | Redirects to `view_students.php`. |
| `add_student.php` | Registration form generated by PHP. Shows the server errors and refills the previously typed values (except the password). |
| `insert_student.php` | POST handler: validate, check duplicates, validate both files, save the files, then `password_hash` and INSERT into `students` and `student_files` inside a transaction. On error it rolls back and deletes the saved files. |
| `view_students.php` | SELECT with an optional LIKE search, then shows a table with thumbnails and the View, Edit, Upload, and Delete (POST form) buttons. |
| `edit_student.php` | Loads one student by id and shows the pre-filled form with the current photo. |
| `update_student.php` | Validates, checks duplicates (excluding the same student), optionally saves a new photo, UPDATEs the record, replaces the photo's file record, and deletes the old photo file. |
| `delete_student.php` | POST only. Collects all the student's file paths, DELETEs the student (the CASCADE removes the file rows), then unlinks the files from disk. |
| `upload_file.php` | Form with a student dropdown and a file input. The POST handler validates the file, saves it, and inserts it into `student_files`. |
| `student_details.php` | Profile card with the image, and a files table with image thumbnails, file size, View, and Download (`download` attribute) links. Shows "File missing" when a file is not on disk. |
| `uploads/.htaccess` | Stops Apache from running any script in `uploads/` and turns off directory listing. |

### Servlet (Java)
| File | Explanation |
|---|---|
| `util/DBConnection.java` | Loads the MySQL driver once in a static block. `getConnection()` returns a new JDBC connection. |
| `util/SessionUtil.java` | `isLoggedIn()`, `checkLogin()` (redirects to login.jsp), `noCache()`, and `escape()` for HTML. |
| `model/Admin.java` | Bean for a logged-in admin, stored in the session. |
| `model/Student.java` | Bean for a `students` row, with a `hasProfileImage()` helper. |
| `model/StudentFile.java` | Bean for a `student_files` row, with an `isImage()` helper. |
| `dao/AdminDAO.java` | `validateLogin()` using `SHA2(?,256)` in SQL. |
| `dao/StudentDAO.java` | `getAllStudents()`, `searchStudents()`, `getStudentById()`, `countStudents()`, `countByCourse()`, `getRecentStudents()`. |
| `dao/StudentFileDAO.java` | `getFilesByStudentId()`, `getFileById()`, `countFiles()`, `countDocuments()`. |
| `servlet/LoginServlet.java` | GET shows the login page. POST validates, creates the session, and redirects to the dashboard. |
| `servlet/LogoutServlet.java` | Invalidates the session and redirects to `login.jsp?logout=1`. |
| `servlet/DashboardServlet.java` | Loads the statistics and forwards to `dashboard.jsp`. |
| `servlet/StudentListServlet.java` | Loads all students or the search results and forwards to `student_list.jsp`. |
| `servlet/StudentProfileServlet.java` | Loads one student and that student's files, then forwards to `student_profile.jsp`. |
| `servlet/FileServlet.java` | Streams the profile image (`?id=`) or any file (`?fileId=`), inline or as a download, with a path-traversal check. |
| `WEB-INF/web.xml` | Servlet declarations and mappings, `uploadBaseDir`, the 30-minute session timeout, the welcome file, and the error pages. |
| `login.jsp` | Login form. Shows the error, logout, and "login required" messages. |
| `error.jsp` | Friendly 404 and 500 page. |
| `WEB-INF/views/header.jspf` | Session check, no-cache headers, page head, and the navigation bar showing the logged-in name. |
| `WEB-INF/views/footer.jspf` | Common footer. |
| `WEB-INF/views/dashboard.jsp` | Statistic boxes, a course table (loop over a Map), and the recent students (loop with if/else for the photo). |
| `WEB-INF/views/student_list.jsp` | Search form and a table built with a `for` loop. Photos come from `FileServlet?id=`. |
| `WEB-INF/views/student_profile.jsp` | Profile with `<img src="FileServlet?id=N">` and a documents table with View and Download links. |
| `css/style.css` | Same design as the front end, plus login and dashboard styles. |

---

## Appendix B: Viva Quick Notes

- **Why validate on both client and server?** JavaScript gives instant feedback but can be turned off or bypassed. Server validation is the real protection.
- **Why prepared statements?** The `?` placeholders send the data separately from the SQL, so input like `' OR 1=1` cannot change the query (prevents SQL injection).
- **Why `enctype="multipart/form-data"`?** Without it the browser sends only the file name, not the file content.
- **Why timestamped file names?** `time()` plus a random number makes every name unique, so two files called `photo.jpg` never overwrite each other.
- **What is ON DELETE CASCADE?** When a parent row (student) is deleted, MySQL automatically deletes the child rows (that student's files).
- **Forward vs. redirect?** A forward happens on the server, in the same request, so request attributes are kept (used to send data to the JSP). A redirect makes the browser send a new request (used after login and after POST, so that a refresh does not resubmit the form).
- **Why put JSPs in WEB-INF?** Files inside WEB-INF cannot be opened directly from the browser, so the user must go through a Servlet that checks the session.
- **What does FileServlet do?** It reads the file's bytes from disk and writes them to `response.getOutputStream()` with the correct `Content-Type`. This hides the real path and allows only logged-in users to see the file.
