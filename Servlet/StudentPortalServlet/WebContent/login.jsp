<%--
    =====================================================================
    File    : login.jsp
    Purpose : Login page of the Servlet module. The form is posted to
              LoginServlet, which checks the admin_users table.
    Demo    : username = admin    password = admin123
    =====================================================================
--%>
<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" %>
<%@ page import="com.studentportal.util.SessionUtil" %>
<%
    // If the admin is already logged in, skip the login page
    if (session.getAttribute(SessionUtil.SESSION_ADMIN) != null) {
        response.sendRedirect("DashboardServlet");
        return;
    }

    // Messages: error comes from LoginServlet (request attribute),
    // logout / auth come from fixed URL parameters
    String error = (String) request.getAttribute("error");
    String username = (String) request.getAttribute("username");
    String info = null;
    if ("1".equals(request.getParameter("logout"))) {
        info = "You have been logged out successfully.";
    } else if ("required".equals(request.getParameter("auth"))) {
        info = "Please login to continue.";
    }
%>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Student Portal (JSP)</title>
    <link rel="stylesheet" href="<%= request.getContextPath() %>/css/style.css">
</head>
<body>

    <header class="site-header">
        <h1>Student Admission Portal</h1>
        <p>Admin Panel - JSP / Servlet / JDBC Module</p>
    </header>

    <main class="main-content">
        <div class="card form-card login-card">
            <h2>Admin Login</h2>

            <%-- Conditional logic: show a message only when one exists --%>
            <% if (error != null) { %>
                <div class="alert alert-error"><%= SessionUtil.escape(error) %></div>
            <% } else if (info != null) { %>
                <div class="alert alert-info"><%= info %></div>
            <% } %>

            <!-- Simple JavaScript check so empty forms are not sent -->
            <form action="LoginServlet" method="post"
                  onsubmit="if (this.username.value.trim() === '' || this.password.value === '') { alert('Please enter username and password.'); return false; } return true;">

                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <input type="text" id="username" name="username" maxlength="50"
                           value="<%= SessionUtil.escape(username) %>" placeholder="Enter username">
                </div>

                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" id="password" name="password" maxlength="50" placeholder="Enter password">
                </div>

                <div class="button-row">
                    <input type="submit" class="btn btn-primary" value="Login">
                    <input type="reset" class="btn btn-secondary" value="Reset">
                </div>
            </form>

            <p class="hint text-center"><br>Demo login: <b>admin</b> / <b>admin123</b></p>
        </div>
    </main>

    <footer class="site-footer">
        &copy; 2026 Student Admission Portal | JSP - Servlet - JDBC - MySQL
    </footer>
</body>
</html>
