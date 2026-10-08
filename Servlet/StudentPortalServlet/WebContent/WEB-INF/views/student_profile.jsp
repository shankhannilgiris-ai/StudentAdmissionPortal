<%--
    =====================================================================
    File    : student_profile.jsp
    Opened  : only through StudentProfileServlet?id=N
    Purpose : Dynamic student profile - details, photo
              (<img src="FileServlet?id=N">) and the list of all uploaded
              documents with View / Download links.
    =====================================================================
--%>
<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" %>
<%@ page import="java.util.List, java.text.SimpleDateFormat" %>
<%@ page import="com.studentportal.model.Student, com.studentportal.model.StudentFile" %>
<% String pageTitle = "Student Profile"; %>
<%@ include file="header.jspf" %>
<%
    // Data sent by StudentProfileServlet
    Student student = (Student) request.getAttribute("student");
    @SuppressWarnings("unchecked")
    List<StudentFile> files = (List<StudentFile>) request.getAttribute("files");
    String error = (String) request.getAttribute("error");
    SimpleDateFormat dateFormat = new SimpleDateFormat("dd-MM-yyyy hh:mm a");
%>

<% if (error != null || student == null) { %>
    <!-- ===== Student not found / database error ===== -->
    <div class="card">
        <div class="alert alert-error"><%= SessionUtil.escape(error != null ? error : "Student not found.") %></div>
        <a href="StudentListServlet" class="btn btn-secondary">&laquo; Back to Student List</a>
    </div>
<% } else { %>

    <!-- ===== Profile details ===== -->
    <div class="card">
        <div class="top-bar">
            <h2>Student Profile</h2>
            <a href="StudentListServlet" class="btn btn-secondary">&laquo; Back to List</a>
        </div>

        <div class="profile-layout">
            <div class="profile-photo">
                <% if (student.hasProfileImage()) { %>
                    <!-- The image bytes come from FileServlet, not from a direct folder URL -->
                    <img src="FileServlet?id=<%= student.getStudentId() %>" class="profile-img" alt="Profile photo">
                <% } else { %>
                    <span class="no-image large">No Image</span>
                <% } %>
                <p><strong><%= SessionUtil.escape(student.getRollNumber()) %></strong></p>
            </div>

            <div class="profile-info">
                <table class="details-table">
                    <tr><th>Student ID</th><td><%= student.getStudentId() %></td></tr>
                    <tr><th>Name</th><td><%= SessionUtil.escape(student.getName()) %></td></tr>
                    <tr><th>Roll Number</th><td><%= SessionUtil.escape(student.getRollNumber()) %></td></tr>
                    <tr><th>Email</th><td><%= SessionUtil.escape(student.getEmail()) %></td></tr>
                    <tr><th>Course</th><td><%= SessionUtil.escape(student.getCourse()) %></td></tr>
                    <tr><th>Mobile</th><td><%= SessionUtil.escape(student.getMobile()) %></td></tr>
                    <tr><th>Gender</th><td><%= SessionUtil.escape(student.getGender()) %></td></tr>
                    <tr><th>Address</th><td><%= SessionUtil.escape(student.getAddress()) %></td></tr>
                    <tr><th>Registered On</th>
                        <td><%= (student.getCreatedAt() != null) ? dateFormat.format(student.getCreatedAt()) : "-" %></td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- ===== Uploaded documents ===== -->
    <div class="card">
        <h2>Uploaded Files (<%= (files == null) ? 0 : files.size() %>)</h2>
        <div class="table-wrapper">
            <table class="data-table">
                <tr>
                    <th>S.No</th>
                    <th>Preview</th>
                    <th>File Name</th>
                    <th>Type</th>
                    <th>Uploaded On</th>
                    <th>Action</th>
                </tr>
                <% if (files == null || files.isEmpty()) { %>
                    <tr><td colspan="6" class="text-center muted">No files uploaded for this student.</td></tr>
                <% } else {
                       int serial = 1;
                       for (StudentFile f : files) { %>
                    <tr>
                        <td><%= serial++ %></td>
                        <td>
                            <%-- Condition: images get a thumbnail, PDFs get a label --%>
                            <% if (f.isImage()) { %>
                                <img src="FileServlet?fileId=<%= f.getFileId() %>" class="thumb" alt="Preview">
                            <% } else { %>
                                <span class="no-image"><%= SessionUtil.escape(f.getFileType().toUpperCase()) %></span>
                            <% } %>
                        </td>
                        <td><%= SessionUtil.escape(f.getFileName()) %></td>
                        <td><%= SessionUtil.escape(f.getFileType().toUpperCase()) %></td>
                        <td><%= (f.getUploadDate() != null) ? dateFormat.format(f.getUploadDate()) : "-" %></td>
                        <td>
                            <a href="FileServlet?fileId=<%= f.getFileId() %>" target="_blank" class="btn btn-secondary btn-small">View</a>
                            <a href="FileServlet?fileId=<%= f.getFileId() %>&amp;download=true" class="btn btn-primary btn-small">Download</a>
                        </td>
                    </tr>
                <%     }
                   } %>
            </table>
        </div>
    </div>
<% } %>

<%@ include file="footer.jspf" %>
