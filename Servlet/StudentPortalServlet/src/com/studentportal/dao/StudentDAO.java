package com.studentportal.dao;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

import com.studentportal.model.Student;
import com.studentportal.util.DBConnection;

/**
 * =====================================================================
 * File    : StudentDAO.java  (Data Access Object)
 * Purpose : All SELECT queries on the "students" table.
 *           Servlets call these methods; JSP pages never use SQL directly.
 * =====================================================================
 */
public class StudentDAO {

    // Column list used by every query (password is never selected)
    private static final String COLUMNS =
            "student_id, name, roll_number, email, course, mobile, gender, address, profile_image, created_at";

    /** Returns every student, newest first. */
    public List<Student> getAllStudents() throws SQLException {
        String sql = "SELECT " + COLUMNS + " FROM students ORDER BY student_id DESC";
        List<Student> list = new ArrayList<>();

        try (Connection con = DBConnection.getConnection();
             PreparedStatement ps = con.prepareStatement(sql);
             ResultSet rs = ps.executeQuery()) {

            // Loop through every row and convert it into a Student object
            while (rs.next()) {
                list.add(mapRow(rs));
            }
        }
        return list;
    }

    /** Search by name, roll number, email or course (contains match). */
    public List<Student> searchStudents(String keyword) throws SQLException {
        String sql = "SELECT " + COLUMNS + " FROM students "
                   + "WHERE name LIKE ? OR roll_number LIKE ? OR email LIKE ? OR course LIKE ? "
                   + "ORDER BY student_id DESC";
        List<Student> list = new ArrayList<>();
        String like = "%" + keyword + "%";

        try (Connection con = DBConnection.getConnection();
             PreparedStatement ps = con.prepareStatement(sql)) {

            for (int i = 1; i <= 4; i++) {
                ps.setString(i, like);
            }
            try (ResultSet rs = ps.executeQuery()) {
                while (rs.next()) {
                    list.add(mapRow(rs));
                }
            }
        }
        return list;
    }

    /** Returns one student or null if the id does not exist. */
    public Student getStudentById(int studentId) throws SQLException {
        String sql = "SELECT " + COLUMNS + " FROM students WHERE student_id = ?";

        try (Connection con = DBConnection.getConnection();
             PreparedStatement ps = con.prepareStatement(sql)) {

            ps.setInt(1, studentId);
            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next()) {
                    return mapRow(rs);
                }
            }
        }
        return null;
    }

    /** Total number of students - shown on the dashboard. */
    public int countStudents() throws SQLException {
        try (Connection con = DBConnection.getConnection();
             Statement st = con.createStatement();
             ResultSet rs = st.executeQuery("SELECT COUNT(*) FROM students")) {
            return rs.next() ? rs.getInt(1) : 0;
        }
    }

    /** Number of students in each course - shown on the dashboard. */
    public Map<String, Integer> countByCourse() throws SQLException {
        String sql = "SELECT course, COUNT(*) AS total FROM students GROUP BY course ORDER BY total DESC";
        // LinkedHashMap keeps the order returned by ORDER BY
        Map<String, Integer> map = new LinkedHashMap<>();

        try (Connection con = DBConnection.getConnection();
             PreparedStatement ps = con.prepareStatement(sql);
             ResultSet rs = ps.executeQuery()) {
            while (rs.next()) {
                map.put(rs.getString("course"), rs.getInt("total"));
            }
        }
        return map;
    }

    /** Latest registered students (for the dashboard). */
    public List<Student> getRecentStudents(int limit) throws SQLException {
        String sql = "SELECT " + COLUMNS + " FROM students ORDER BY created_at DESC, student_id DESC LIMIT ?";
        List<Student> list = new ArrayList<>();

        try (Connection con = DBConnection.getConnection();
             PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setInt(1, limit);
            try (ResultSet rs = ps.executeQuery()) {
                while (rs.next()) {
                    list.add(mapRow(rs));
                }
            }
        }
        return list;
    }

    /** Copies the current ResultSet row into a new Student object. */
    private Student mapRow(ResultSet rs) throws SQLException {
        Student s = new Student();
        s.setStudentId(rs.getInt("student_id"));
        s.setName(rs.getString("name"));
        s.setRollNumber(rs.getString("roll_number"));
        s.setEmail(rs.getString("email"));
        s.setCourse(rs.getString("course"));
        s.setMobile(rs.getString("mobile"));
        s.setGender(rs.getString("gender"));
        s.setAddress(rs.getString("address"));
        s.setProfileImage(rs.getString("profile_image"));
        s.setCreatedAt(rs.getTimestamp("created_at"));
        return s;
    }
}
