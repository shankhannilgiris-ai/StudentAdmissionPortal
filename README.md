# Student Admission Portal with File Management System

A college mini-project that registers students online, stores their details in **MySQL**, and manages their uploaded files (profile photos and certificates). It has three parts:

| Module | Technology | What it does |
|---|---|---|
| **Frontend** | HTML, CSS, JavaScript | Registration form with client-side validation and a responsive design |
| **PHP Module** | PHP, MySQL (mysqli) | Add, view, edit, delete and search students; upload and download files |
| **Servlet Module** | JSP, Servlet, JDBC, MySQL | Admin login with sessions, dashboard, student list, dynamic profile, and file streaming |

---

## Project Overview

Students fill in a registration form with their personal details, a profile photo, and a certificate. The data is checked twice, first in the browser (JavaScript) and again on the server (PHP). Valid data goes into the `student_portal` database. Each file is saved in `uploads/images/` or `uploads/documents/` with a timestamped name, so an existing file is never overwritten.

An administrator can also log in to a separate **Java web application** (`StudentPortalServlet`). It reads the same database through JDBC and shows a dashboard, the student list, and each student's profile. Images and documents are served by `FileServlet`, using URLs like `<img src="FileServlet?id=1">`.

---

## Features

### Frontend
- Registration form with 10 fields, a Submit button, and a Reset button
- Validation for required fields, e-mail format, password strength, a 10-digit mobile number, roll number format, file type, and file size
- Live preview of the chosen profile image
- Card layout centred on the page, styled inputs, buttons, and error messages, a header, and a footer
- Responsive design using media queries at 768px and 480px

### PHP Module
1. **Add Student**: registration form plus server-side validation
2. **View Students**: HTML table with photo thumbnails
3. **Edit Student**: pre-filled form. The password and photo are optional and keep their old values if left empty
4. **Delete Student**: removes the record, its file rows (ON DELETE CASCADE), and the physical files
5. **Search Student**: search by name, roll number, e-mail or course
6. **Upload Files**: upload extra documents for any student
7. **View Uploaded Files**: profile page with an image preview and View/Download links

Security and quality features:
- Prepared statements, which prevent SQL injection
- `htmlspecialchars` on all output, which prevents XSS
- `password_hash()` for passwords
- Database transactions, and SQL exceptions caught with friendly messages
- Uploaded files are checked by both extension and real MIME type
- A `.htaccess` file blocks script execution inside `uploads/`

### Servlet Module
1. **Login system**: `login.jsp` → `LoginServlet`, which creates a session (30-minute timeout)
2. **Dashboard**: total students, total files, students per course, and recent registrations
3. **Student list**: `StudentListServlet`, with search
4. **Student profile**: `StudentProfileServlet`, showing details and every uploaded document
5. **Image and file streaming**: `FileServlet?id=1` returns a profile photo. `FileServlet?fileId=4` returns any uploaded file, and adding `&download=true` downloads it
6. Every page checks the session. Users who are not logged in are redirected to `login.jsp`
7. **Logout**: `LogoutServlet` invalidates the session. Pages are sent with no-cache headers, so the Back button does not show them after logout

---

## Technologies Used

| Layer | Technology |
|---|---|
| Front end | HTML5, CSS3, JavaScript (no frameworks) |
| Server-side scripting | PHP 8.x |
| Java web | JSP, Servlet 4.0 (javax), JDBC |
| Database | MySQL 8 / MariaDB 10 |
| Servers | Apache (XAMPP), Apache Tomcat 9 |
| IDE | VS Code (HTML/PHP), Eclipse IDE for Enterprise Java (Servlet) |
| JDBC driver | MySQL Connector/J 8.x |

---

## Folder Structure

```
StudentAdmissionPortal/
│
├── README.md
├── Frontend/
│   ├── index.html                  Student Registration Form
│   ├── style.css                   Common stylesheet (also used by PHP pages)
│   └── script.js                   Client-side validation
│
├── PHP/
│   ├── index.php                   Redirects to view_students.php
│   ├── db.php                      DB connection + settings + helper functions
│   ├── header.php                  Common header / navigation / flash message
│   ├── footer.php                  Common footer
│   ├── add_student.php             Registration form (server version)
│   ├── insert_student.php          Validates and inserts a student
│   ├── view_students.php           List + search
│   ├── edit_student.php            Edit form
│   ├── update_student.php          Validates and updates a student
│   ├── delete_student.php          Deletes a student and files
│   ├── upload_file.php             Upload extra files
│   ├── student_details.php         Profile + uploaded files
│   └── uploads/
│       ├── .htaccess               Blocks script execution
│       ├── images/                 jpg / jpeg / png files
│       └── documents/              pdf files
│
├── Servlet/
│   └── StudentPortalServlet/       Java Dynamic Web Project
│       ├── src/com/studentportal/
│       │   ├── util/   DBConnection.java, SessionUtil.java
│       │   ├── model/  Admin.java, Student.java, StudentFile.java
│       │   ├── dao/    AdminDAO.java, StudentDAO.java, StudentFileDAO.java
│       │   └── servlet/ LoginServlet, LogoutServlet, DashboardServlet,
│       │               StudentListServlet, StudentProfileServlet, FileServlet
│       └── WebContent/
│           ├── login.jsp
│           ├── error.jsp
│           ├── css/style.css
│           └── WEB-INF/
│               ├── web.xml
│               ├── lib/            (put mysql-connector-j.jar here)
│               └── views/          dashboard.jsp, student_list.jsp,
│                                   student_profile.jsp, header.jspf, footer.jspf
│
├── Database/
│   └── student_portal.sql          CREATE DATABASE, tables, keys, sample data
│
└── Documentation/
    └── Project_Report.md           Full report (Introduction ... Conclusion)
```

---

## Installation Steps

### Prerequisites
1. **XAMPP** (Apache + MySQL + PHP 8): https://www.apachefriends.org
2. **JDK 11 or newer**
3. **Apache Tomcat 9.x**: https://tomcat.apache.org. The code uses the `javax.servlet` package. On Tomcat 10 or later, replace `javax.` with `jakarta.` in the Java files.
4. **Eclipse IDE for Enterprise Java and Web Developers**
5. **MySQL Connector/J 8.x** JAR: https://dev.mysql.com/downloads/connector/j/

### Copy the project
Copy the whole `StudentAdmissionPortal` folder into the XAMPP web root:
```
C:\xampp\htdocs\StudentAdmissionPortal\
```

---

## Database Setup

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Open http://localhost/phpmyadmin.
3. Click **Import**, choose `Database/student_portal.sql`, then click **Go**.
4. The script creates the `student_portal` database and the `students`, `student_files` and `admin_users` tables, with 3 sample students and 6 sample files.

You can also use the command line:
```bash
mysql -u root -p < Database/student_portal.sql
```

If your MySQL user or password is not `root` with an empty password, change it in:
- `PHP/db.php`: `DB_USER`, `DB_PASS`
- `Servlet/.../util/DBConnection.java`: `USER`, `PASSWORD`

---

## Running the PHP Module

1. Make sure Apache and MySQL are running.
2. Open http://localhost/StudentAdmissionPortal/Frontend/index.html for the registration form,
   or http://localhost/StudentAdmissionPortal/PHP/ for the student list.
3. Register a student, view the list, search, edit, upload a file, and delete.

> The `PHP/uploads/images` and `PHP/uploads/documents` folders must be writable. They are writable by default on Windows/XAMPP.
> On Linux, run `chmod -R 775 PHP/uploads`.

---

## Running the Servlet Module

1. In Eclipse, go to **File → New → Dynamic Web Project**.
   - Project name: `StudentPortalServlet`
   - Target runtime: **Apache Tomcat v9.0**
   - Click **Next** and set **Source folder** to `src`. Click **Next** again and set **Content directory** to `WebContent`. Tick **Generate web.xml**.
2. Copy `Servlet/StudentPortalServlet/src` and `Servlet/StudentPortalServlet/WebContent` into the new project, replacing the generated files.
3. Copy `mysql-connector-j-8.x.x.jar` into `WebContent/WEB-INF/lib/`.
4. Open `WebContent/WEB-INF/web.xml` and set **`uploadBaseDir`** to the PHP folder that contains `uploads`, for example:
   ```xml
   <param-value>C:/xampp/htdocs/StudentAdmissionPortal/PHP</param-value>
   ```
5. Right-click the project and choose **Run As → Run on Server → Tomcat 9**.
6. Open http://localhost:8080/StudentPortalServlet/.
7. Log in with **admin** / **admin123**.

> XAMPP's MySQL (port 3306) must stay running while Tomcat (port 8080) runs.

---

## Screenshots

All screenshots were taken from the running project.

### Question 1: HTML, CSS and JavaScript

**Registration form**

![Registration form](screenshots/fig1_1_registration_form.png)

**Validation error messages**

![Validation error messages](screenshots/fig1_2_validation_errors.png)

**Profile image preview**

![Profile image preview](screenshots/fig1_3_image_preview.png)

**Successful submission**

![Successful submission](screenshots/fig1_4_successful_submission.png)

**Mobile view**

![Mobile view](screenshots/fig1_5_mobile_view.png)

### Question 2: PHP and MySQL

**Student directory**

![Student directory](screenshots/fig2_1_student_directory.png)

**Add student form**

![Add student form](screenshots/fig2_2_add_student_form.png)

**File upload**

![File upload](screenshots/fig2_3_file_upload.png)

**Student profile (PHP)**

![Student profile (PHP)](screenshots/fig2_4_student_profile.png)

**Edit student**

![Edit student](screenshots/fig2_5_edit_student.png)

**Delete student**

![Delete student](screenshots/fig2_6_delete_student.png)

**Search result**

![Search result](screenshots/fig2_7_search_result.png)

### Question 3: Servlet, JSP and JDBC

**Login page (JSP)**

![Login page (JSP)](screenshots/fig3_1_login_page.png)

**Invalid login warning**

![Invalid login warning](screenshots/fig3_2_invalid_login.png)

**Dashboard**

![Dashboard](screenshots/fig3_3_dashboard.png)

**Student list (Servlet)**

![Student list (Servlet)](screenshots/fig3_4_student_list.png)

**Student profile (Servlet)**

![Student profile (Servlet)](screenshots/fig3_5_student_profile.png)

**Document streaming (FileServlet)**

![Document streaming (FileServlet)](screenshots/fig3_6_document_streaming.png)

---

## Default Credentials

| Module | Username | Password |
|---|---|---|
| Servlet admin login | `admin` | `admin123` |
| Sample students (stored only) | n/a | `password` |

---

## Author Details

| | |
|---|---|
| **Name** | Mohammed Ihsan I |
| **Register No.** | 24CS120 |
| **Course** | B.E Computer Science and Engineering |
| **College** | KPR Institute of Engineering and Technology |
| **Subject** | U21CS501 – Web Technology (Assignment II) |
| **Guide** | — |
| **Academic Year** | 2026–2027 |

---

## License
This project was made for academic purposes. You may use and modify it for learning.
