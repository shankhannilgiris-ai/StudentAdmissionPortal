package com.studentportal.dao;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

import com.studentportal.model.Admin;
import com.studentportal.util.DBConnection;

/**
 * =====================================================================
 * File    : AdminDAO.java  (Data Access Object)
 * Purpose : Checks the login username and password against the
 *           admin_users table.
 * =====================================================================
 */
public class AdminDAO {

    /**
     * Returns the Admin when username + password are correct, otherwise null.
     * The password is hashed inside MySQL with SHA2(?, 256) and compared
     * with the stored hash, so the plain password is never stored.
     */
    public Admin validateLogin(String username, String password) throws SQLException {
        String sql = "SELECT admin_id, username, full_name FROM admin_users "
                   + "WHERE username = ? AND password = SHA2(?, 256)";

        // try-with-resources closes Connection, PreparedStatement and ResultSet automatically
        try (Connection con = DBConnection.getConnection();
             PreparedStatement ps = con.prepareStatement(sql)) {

            // PreparedStatement parameters prevent SQL injection
            ps.setString(1, username);
            ps.setString(2, password);

            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next()) {
                    return new Admin(rs.getInt("admin_id"),
                                     rs.getString("username"),
                                     rs.getString("full_name"));
                }
            }
        }
        return null;   // wrong username or password
    }
}
