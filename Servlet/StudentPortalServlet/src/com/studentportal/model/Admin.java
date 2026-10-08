package com.studentportal.model;

import java.io.Serializable;

/**
 * =====================================================================
 * File    : Admin.java  (Model class)
 * Purpose : Represents one row of the admin_users table.
 *           An Admin object is stored in the HTTP session after login.
 *           Serializable -> the session can be saved by Tomcat.
 * =====================================================================
 */
public class Admin implements Serializable {

    private static final long serialVersionUID = 1L;

    private int adminId;
    private String username;
    private String fullName;   // password is intentionally NOT kept in the object

    public Admin() {
    }

    public Admin(int adminId, String username, String fullName) {
        this.adminId = adminId;
        this.username = username;
        this.fullName = fullName;
    }

    // ---------- Getters and Setters ----------
    public int getAdminId() {
        return adminId;
    }

    public void setAdminId(int adminId) {
        this.adminId = adminId;
    }

    public String getUsername() {
        return username;
    }

    public void setUsername(String username) {
        this.username = username;
    }

    public String getFullName() {
        return fullName;
    }

    public void setFullName(String fullName) {
        this.fullName = fullName;
    }
}
