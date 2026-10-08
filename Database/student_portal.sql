-- =====================================================================
-- Project   : Student Admission Portal with File Management System
-- File      : student_portal.sql
-- Purpose   : Creates the database, all tables, keys and sample data.
-- How to run: Open phpMyAdmin -> Import -> choose this file -> Go
--             (or)  mysql -u root -p < student_portal.sql
-- =====================================================================

-- 1. CREATE DATABASE ---------------------------------------------------
-- utf8mb4 supports every character (including Tamil/Hindi names, emoji).
CREATE DATABASE IF NOT EXISTS student_portal
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

-- Select the database so that the following statements run inside it.
USE student_portal;

-- Drop old tables (child table first because of the foreign key) so the
-- script can be re-run safely during testing.
DROP TABLE IF EXISTS student_files;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS admin_users;

-- 2. CREATE TABLE : students -------------------------------------------
-- Stores the personal and academic details of each registered student.
CREATE TABLE students (
    student_id    INT AUTO_INCREMENT,                 -- unique id generated automatically
    name          VARCHAR(100)  NOT NULL,             -- student full name
    roll_number   VARCHAR(20)   NOT NULL,             -- format: 22CSE001
    email         VARCHAR(100)  NOT NULL,             -- login / contact e-mail
    password      VARCHAR(255)  NOT NULL,             -- bcrypt hash (never plain text)
    course        VARCHAR(50)   NOT NULL,             -- selected course
    mobile        CHAR(10)      NOT NULL,             -- exactly 10 digits
    gender        ENUM('Male','Female','Other') NOT NULL,
    address       TEXT          NOT NULL,             -- full postal address
    profile_image VARCHAR(255)  DEFAULT NULL,         -- relative path e.g. uploads/images/x.jpg
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP, -- registration time

    -- 3. PRIMARY KEY
    CONSTRAINT pk_students PRIMARY KEY (student_id),

    -- UNIQUE constraints: the database itself refuses duplicate roll numbers / e-mails
    CONSTRAINT uq_students_roll  UNIQUE (roll_number),
    CONSTRAINT uq_students_email UNIQUE (email)
) ENGINE = InnoDB;   -- InnoDB is required for foreign keys and transactions

-- 2. CREATE TABLE : student_files --------------------------------------
-- Stores information about every file (image / certificate) uploaded
-- for a student. The real file lives in the uploads/ folder; only the
-- path is stored here.
CREATE TABLE student_files (
    file_id     INT AUTO_INCREMENT,                   -- unique file id
    student_id  INT          NOT NULL,                -- owner of the file
    file_name   VARCHAR(255) NOT NULL,                -- original file name shown to user
    file_path   VARCHAR(255) NOT NULL,                -- saved path e.g. uploads/documents/1712_cert.pdf
    file_type   VARCHAR(10)  NOT NULL,                -- extension: jpg, jpeg, png, pdf
    upload_date TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- 3. PRIMARY KEY
    CONSTRAINT pk_student_files PRIMARY KEY (file_id),

    -- 4. FOREIGN KEY: every file must belong to an existing student.
    --    ON DELETE CASCADE -> deleting a student removes his/her file records.
    CONSTRAINT fk_files_student FOREIGN KEY (student_id)
        REFERENCES students (student_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE = InnoDB;

-- Index to make "files of one student" queries faster.
CREATE INDEX idx_files_student ON student_files (student_id);

-- Supporting table : admin_users ---------------------------------------
-- Used only by the JSP/Servlet module for the Login System.
-- Password is stored as a SHA-256 hash (generated with MySQL SHA2()).
CREATE TABLE admin_users (
    admin_id   INT AUTO_INCREMENT,
    username   VARCHAR(50)  NOT NULL,
    password   CHAR(64)     NOT NULL,                 -- SHA-256 hex = 64 characters
    full_name  VARCHAR(100) NOT NULL,
    CONSTRAINT pk_admin_users PRIMARY KEY (admin_id),
    CONSTRAINT uq_admin_username UNIQUE (username)
) ENGINE = InnoDB;

-- 5. SAMPLE INSERT STATEMENTS ------------------------------------------

-- Admin login for the Servlet module ->  username: admin   password: admin123
INSERT INTO admin_users (username, password, full_name)
VALUES ('admin', SHA2('admin123', 256), 'Portal Administrator');

-- Sample students.
-- The password hash below is the PHP password_hash() value of "password"
-- (sample data only - new students get their own hash from insert_student.php).
INSERT INTO students (name, roll_number, email, password, course, mobile, gender, address, profile_image) VALUES
('Arun Kumar',     '22CSE001', 'arun.kumar@example.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'B.E Computer Science and Engineering', '9876543210', 'Male',
 '12, Anna Nagar, Chennai - 600040', 'uploads/images/1700000001_arun.png'),

('Priya Sharma',   '22ECE014', 'priya.sharma@example.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'B.E Electronics and Communication Engineering', '9123456780', 'Female',
 '45, Gandhi Road, Coimbatore - 641001', 'uploads/images/1700000002_priya.png'),

('Mohammed Faizal','22IT007',  'faizal.m@example.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'B.Tech Information Technology', '9988776655', 'Male',
 '7, Lake View Street, Madurai - 625001', 'uploads/images/1700000003_faizal.png');

-- Sample uploaded-file records (the matching files are shipped inside PHP/uploads/).
INSERT INTO student_files (student_id, file_name, file_path, file_type) VALUES
(1, 'arun.png',                    'uploads/images/1700000001_arun.png',               'png'),
(1, 'arun_12th_certificate.pdf',   'uploads/documents/1700000001_arun_certificate.pdf', 'pdf'),
(2, 'priya.png',                   'uploads/images/1700000002_priya.png',              'png'),
(2, 'priya_12th_certificate.pdf',  'uploads/documents/1700000002_priya_certificate.pdf','pdf'),
(3, 'faizal.png',                  'uploads/images/1700000003_faizal.png',             'png'),
(3, 'faizal_12th_certificate.pdf', 'uploads/documents/1700000003_faizal_certificate.pdf','pdf');

-- Quick check queries (optional) ---------------------------------------
-- SELECT * FROM students;
-- SELECT * FROM student_files;
-- SELECT s.name, f.file_name FROM students s JOIN student_files f ON s.student_id = f.student_id;
