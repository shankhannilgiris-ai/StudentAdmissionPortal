package com.studentportal.util;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

/**
 * =====================================================================
 * File    : DBConnection.java  (JDBC Connection Utility Class)
 * Purpose : Creates a connection to the MySQL database "student_portal".
 *           Every DAO class calls DBConnection.getConnection() and closes
 *           the connection with try-with-resources after use.
 * =====================================================================
 */
public class DBConnection {

    // JDBC URL format: jdbc:mysql://host:port/database?options
    private static final String URL =
            "jdbc:mysql://localhost:3306/student_portal"
            + "?useSSL=false&allowPublicKeyRetrieval=true&serverTimezone=Asia/Kolkata";

    // XAMPP default MySQL user is "root" with an empty password
    private static final String USER = "root";
    private static final String PASSWORD = "";

    // MySQL Connector/J 8.x driver class name
    private static final String DRIVER = "com.mysql.cj.jdbc.Driver";

    // Static block runs once when the class is loaded: registers the driver
    static {
        try {
            Class.forName(DRIVER);
        } catch (ClassNotFoundException e) {
            // Happens when mysql-connector-j.jar is missing from WEB-INF/lib
            throw new RuntimeException("MySQL JDBC Driver not found. "
                    + "Copy mysql-connector-j-x.x.x.jar into WebContent/WEB-INF/lib", e);
        }
    }

    // Private constructor: this is a utility class, no objects are needed
    private DBConnection() {
    }

    /**
     * Opens and returns a new database connection.
     * The caller must close it (try-with-resources does this automatically).
     */
    public static Connection getConnection() throws SQLException {
        return DriverManager.getConnection(URL, USER, PASSWORD);
    }
}
