<%--
    =====================================================================
    File    : dashboard.jsp
    Opened  : only through DashboardServlet (file is inside WEB-INF)
    Purpose : Shows summary boxes, students per course and the latest
              registrations. Uses loops and conditions in scriptlets.
    =====================================================================
--%>
<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" %>
<%@ page import="java.util.List, java.util.Map, java.text.SimpleDateFormat" %>
<%@ page import="com.studentportal.model.Student" %>
<% String pageTitle = "Dashboard"; %>
<%@ include file="header.jspf" %>
<%
    // ----- Read the values prepared by DashboardServlet -----
    String error = (String) request.getAttribute("error");
    Integer totalStudents  = (Integer) request.getAttribute("totalStudents");
    Integer totalFiles     = (Integer) request.getAttribute("totalFiles");
    Integer totalDocuments = (Integer) request.getAttribute("totalDocuments");
    @SuppressWarnings("unchecked")
    Map<String, Integer> courseCounts = (Map<String, Integer>) request.getAttribute("courseCounts");
    @SuppressWarnings("unchecked")
    List<Student> recentStudents = (List<Student>) request.getAttribute("recentStudents");

    SimpleDateFormat dateFormat = new SimpleDateFormat("dd-MM-yyyy hh:mm a");
%>

<div class="card">
    <h2>Welcome, <%= SessionUtil.escape(loggedInAdmin.getFullName()) %></h2>
    <p class="muted">This dashboard reads live data from the <b>student_portal</b> MySQL database using JDBC.</p>
</div>

<% if (error != null) { %>
    <div class="alert alert-error"><%= SessionUtil.escape(error) %></div>
<% } %>

<!-- ===== Statistic boxes ===== -->
<div class="stats-row">
    <div class="stat-box">
        <div class="stat-number"><%= (totalStudents != null) ? totalStudents : 0 %></div>
        <div class="stat-label">Total Students</div>
    </div>
    <div class="stat-box">
        <div class="stat-number"><%= (totalFiles != null) ? totalFiles : 0 %></div>
        <div class="stat-label">Uploaded Files</div>
    </div>
    <div class="stat-box">
        <div class="stat-number"><%= (totalDocuments != null) ? totalDocuments : 0 %></div>
        <div class="stat-label">PDF Documents</div>
    </div>
</div>

<!-- ===== Students per course ===== -->
<div class="card">
    <h3>Students per Course</h3>
    <div class="table-wrapper">
        <table class="data-table">
            <tr>
                <th>Course</th>
                <th>No. of Students</th>
            </tr>
            <% if (courseCounts == null || courseCounts.isEmpty()) { %>
                <tr><td colspan="2" class="text-center muted">No data available.</td></tr>
            <% } else {
                   // Loop over every entry of the Map
                   for (Map.Entry<String, Integer> entry : courseCounts.entrySet()) { %>
                <tr>
                    <td><%= SessionUtil.escape(entry.getKey()) %></td>
                    <td><%= entry.getValue() %></td>
                </tr>
            <%     }
               } %>
        </table>
    </div>
</div>

<!-- ===== Recently registered students ===== -->
<div class="card">
    <div class="top-bar">
        <h3>Recently Registered Students</h3>
        <a href="StudentListServlet" class="btn btn-primary btn-small">View All</a>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <tr>
                <th>Photo</th>
                <th>Name</th>
                <th>Roll Number</th>
                <th>Course</th>
                <th>Registered On</th>
                <th>Action</th>
            </tr>
            <% if (recentStudents == null || recentStudents.isEmpty()) { %>
                <tr><td colspan="6" class="text-center muted">No students registered yet.</td></tr>
            <% } else {
                   for (Student s : recentStudents) { %>
                <tr>
                    <td>
                        <% if (s.hasProfileImage()) { %>
                            <!-- Image is streamed by FileServlet -->
                            <img src="FileServlet?id=<%= s.getStudentId() %>" class="thumb" alt="Photo">
                        <% } else { %>
                            <span class="no-image">No Image</span>
                        <% } %>
                    </td>
                    <td><%= SessionUtil.escape(s.getName()) %></td>
                    <td><%= SessionUtil.escape(s.getRollNumber()) %></td>
                    <td><%= SessionUtil.escape(s.getCourse()) %></td>
                    <td><%= (s.getCreatedAt() != null) ? dateFormat.format(s.getCreatedAt()) : "-" %></td>
                    <td><a href="StudentProfileServlet?id=<%= s.getStudentId() %>" class="btn btn-primary btn-small">Profile</a></td>
                </tr>
            <%     }
               } %>
        </table>
    </div>
</div>

<%@ include file="footer.jspf" %>
