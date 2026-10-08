package com.studentportal.model;

import java.sql.Timestamp;

/**
 * =====================================================================
 * File    : Student.java  (Model / JavaBean class)
 * Purpose : Represents one row of the "students" table.
 *           The DAO fills these objects from the ResultSet and the JSP
 *           pages read them using the getter methods.
 * =====================================================================
 */
public class Student {

    // One field for every column of the students table (password excluded)
    private int studentId;
    private String name;
    private String rollNumber;
    private String email;
    private String course;
    private String mobile;
    private String gender;
    private String address;
    private String profileImage;   // relative path, e.g. uploads/images/1700000001_arun.png
    private Timestamp createdAt;

    // Default constructor (required for a JavaBean)
    public Student() {
    }

    /** true when the student has a profile photo path stored */
    public boolean hasProfileImage() {
        return profileImage != null && !profileImage.trim().isEmpty();
    }

    // ---------- Getters and Setters ----------
    public int getStudentId() {
        return studentId;
    }

    public void setStudentId(int studentId) {
        this.studentId = studentId;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getRollNumber() {
        return rollNumber;
    }

    public void setRollNumber(String rollNumber) {
        this.rollNumber = rollNumber;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public String getCourse() {
        return course;
    }

    public void setCourse(String course) {
        this.course = course;
    }

    public String getMobile() {
        return mobile;
    }

    public void setMobile(String mobile) {
        this.mobile = mobile;
    }

    public String getGender() {
        return gender;
    }

    public void setGender(String gender) {
        this.gender = gender;
    }

    public String getAddress() {
        return address;
    }

    public void setAddress(String address) {
        this.address = address;
    }

    public String getProfileImage() {
        return profileImage;
    }

    public void setProfileImage(String profileImage) {
        this.profileImage = profileImage;
    }

    public Timestamp getCreatedAt() {
        return createdAt;
    }

    public void setCreatedAt(Timestamp createdAt) {
        this.createdAt = createdAt;
    }
}
