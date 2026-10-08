package com.studentportal.dao;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.List;

import com.studentportal.model.StudentFile;
import com.studentportal.util.DBConnection;

/**
 * =====================================================================
 * File    : StudentFileDAO.java  (Data Access Object)
 * Purpose : Fetches uploaded-file information from "student_files".
 * =====================================================================
 */
public class StudentFileDAO {

    /** All files that belong to one student, latest first. */
    public List<StudentFile> getFilesByStudentId(int studentId) throws SQLException {
        String sql = "SELECT * FROM student_files WHERE student_id = ? "
                   + "ORDER BY upload_date DESC, file_id DESC";
        List<StudentFile> files = new ArrayList<>();

        try (Connection con = DBConnection.getConnection();
             PreparedStatement ps = con.prepareStatement(sql)) {

            ps.setInt(1, studentId);
            try (ResultSet rs = ps.executeQuery()) {
                while (rs.next()) {
                    files.add(mapRow(rs));
                }
            }
        }
        return files;
    }

    /** One file by its id (used by FileServlet), or null. */
    public StudentFile getFileById(int fileId) throws SQLException {
        String sql = "SELECT * FROM student_files WHERE file_id = ?";

        try (Connection con = DBConnection.getConnection();
             PreparedStatement ps = con.prepareStatement(sql)) {

            ps.setInt(1, fileId);
            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next()) {
                    return mapRow(rs);
                }
            }
        }
        return null;
    }

    /** Total number of uploaded files (dashboard). */
    public int countFiles() throws SQLException {
        try (Connection con = DBConnection.getConnection();
             Statement st = con.createStatement();
             ResultSet rs = st.executeQuery("SELECT COUNT(*) FROM student_files")) {
            return rs.next() ? rs.getInt(1) : 0;
        }
    }

    /** Number of PDF documents (dashboard). */
    public int countDocuments() throws SQLException {
        try (Connection con = DBConnection.getConnection();
             Statement st = con.createStatement();
             ResultSet rs = st.executeQuery("SELECT COUNT(*) FROM student_files WHERE file_type = 'pdf'")) {
            return rs.next() ? rs.getInt(1) : 0;
        }
    }

    /** Copies the current ResultSet row into a StudentFile object. */
    private StudentFile mapRow(ResultSet rs) throws SQLException {
        StudentFile f = new StudentFile();
        f.setFileId(rs.getInt("file_id"));
        f.setStudentId(rs.getInt("student_id"));
        f.setFileName(rs.getString("file_name"));
        f.setFilePath(rs.getString("file_path"));
        f.setFileType(rs.getString("file_type"));
        f.setUploadDate(rs.getTimestamp("upload_date"));
        return f;
    }
}
