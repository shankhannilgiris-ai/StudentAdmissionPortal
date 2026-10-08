package com.studentportal.model;

import java.sql.Timestamp;

/**
 * =====================================================================
 * File    : StudentFile.java  (Model class)
 * Purpose : Represents one row of the "student_files" table, i.e. one
 *           uploaded image or document of a student.
 * =====================================================================
 */
public class StudentFile {

    private int fileId;
    private int studentId;      // foreign key -> students.student_id
    private String fileName;    // original name shown to the user
    private String filePath;    // stored path, e.g. uploads/documents/1700000001_arun_certificate.pdf
    private String fileType;    // jpg, jpeg, png or pdf
    private Timestamp uploadDate;

    public StudentFile() {
    }

    /** true for jpg / jpeg / png files - used by the JSP to show a preview */
    public boolean isImage() {
        return "jpg".equalsIgnoreCase(fileType)
                || "jpeg".equalsIgnoreCase(fileType)
                || "png".equalsIgnoreCase(fileType);
    }

    // ---------- Getters and Setters ----------
    public int getFileId() {
        return fileId;
    }

    public void setFileId(int fileId) {
        this.fileId = fileId;
    }

    public int getStudentId() {
        return studentId;
    }

    public void setStudentId(int studentId) {
        this.studentId = studentId;
    }

    public String getFileName() {
        return fileName;
    }

    public void setFileName(String fileName) {
        this.fileName = fileName;
    }

    public String getFilePath() {
        return filePath;
    }

    public void setFilePath(String filePath) {
        this.filePath = filePath;
    }

    public String getFileType() {
        return fileType;
    }

    public void setFileType(String fileType) {
        this.fileType = fileType;
    }

    public Timestamp getUploadDate() {
        return uploadDate;
    }

    public void setUploadDate(Timestamp uploadDate) {
        this.uploadDate = uploadDate;
    }
}
