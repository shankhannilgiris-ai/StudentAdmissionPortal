<%--
    =====================================================================
    File    : student_list.jsp
    Opened  : only through StudentListServlet
    Purpose : Displays all students (or search results) in an HTML table
              with their profile photo streamed by FileServlet.
    =====================================================================
--%>
<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" %>
<%@ page import="java.util.List, java.text.SimpleDateFormat" %>
<%@ page import="com.studentportal.model.Student" %>
<% String pageTitle = "Student List"; %>
<%@ include file="header.jspf" %>
<%
    // Data sent by StudentListServlet
    @SuppressWarnings("unchecked")
    List<Student> students = (List<Student>) request.getAttribute("students");
    String search = (String) request.getAttribute("search");
    String error  = (String) request.getAttribute("error");
    if (search == null) {
        search = "";
    }
    SimpleDateFormat dateFormat = new SimpleDateFormat("dd-MM-yyyy");
%>

<div class="card">
    <h2>Student List</h2>

    <!-- Search form: GET request back to the same Servlet -->
    <form class="search-form" method="get" action="StudentListServlet">
        <input type="text" name="search" value="<%= SessionUtil.escape(search) %>"
               placeholder="Search by name, roll number, email or course">
        <input type="submit" class="btn btn-primary" value="Search">
        <a href="StudentListServlet" class="btn btn-secondary">Clear</a>
    </form>

    <% if (error != null) { %>
        <div class="alert alert-error"><%= SessionUtil.escape(error) %></div>
    <% } %>

    <% if (!search.isEmpty()) { %>
        <p class="muted"><%= (students == null) ? 0 : students.size() %> result(s) for
           "<%= SessionUtil.escape(search) %>"</p><br>
    <% } %>

    <div class="table-wrapper">
        <table class="data-table">
            <tr>
                <th>S.No</th>
                <th>Photo</th>
                <th>Name</th>
                <th>Roll Number</th>
                <th>Email</th>
                <th>Course</th>
                <th>Mobile</th>
                <th>Gender</th>
                <th>Registered</th>
                <th>Action</th>
            </tr>

            <%-- Conditional: empty list vs. rows --%>
            <% if (students == null || students.isEmpty()) { %>
                <tr><td colspan="10" class="text-center muted">No student records found.</td></tr>
            <% } else {
                   int serial = 1;
                   // Loop: one table row for every Student object
                   for (Student s : students) { %>
                <tr>
                    <td><%= serial++ %></td>
                    <td>
                        <% if (s.hasProfileImage()) { %>
                            <img src="FileServlet?id=<%= s.getStudentId() %>" class="thumb" alt="Photo">
                        <% } else { %>
                            <span class="no-image">No Image</span>
                        <% } %>
                    </td>
                    <td><%= SessionUtil.escape(s.getName()) %></td>
                    <td><%= SessionUtil.escape(s.getRollNumber()) %></td>
                    <td><%= SessionUtil.escape(s.getEmail()) %></td>
                    <td><%= SessionUtil.escape(s.getCourse()) %></td>
                    <td><%= SessionUtil.escape(s.getMobile()) %></td>
                    <td><%= SessionUtil.escape(s.getGender()) %></td>
                    <td><%= (s.getCreatedAt() != null) ? dateFormat.format(s.getCreatedAt()) : "-" %></td>
                    <td><a href="StudentProfileServlet?id=<%= s.getStudentId() %>" class="btn btn-primary btn-small">View Profile</a></td>
                </tr>
            <%     }
               } %>
        </table>
    </div>
    <p class="muted"><br>Total records: <%= (students == null) ? 0 : students.size() %></p>
</div>

<%@ include file="footer.jspf" %>
