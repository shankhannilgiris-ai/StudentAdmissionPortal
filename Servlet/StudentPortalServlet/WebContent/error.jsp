<%--
    =====================================================================
    File    : error.jsp
    Purpose : Friendly page shown for 404 (not found) and 500 (server
              error) instead of the default Tomcat error screen.
              Configured in web.xml with <error-page>.
    =====================================================================
--%>
<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" isErrorPage="true" %>
<%
    // Status code set by Tomcat when it forwards to the error page
    Object codeObj = request.getAttribute("javax.servlet.error.status_code");
    int code = (codeObj instanceof Integer) ? (Integer) codeObj : 500;
    String message = (code == 404) ? "The page or file you requested was not found."
                                   : "Something went wrong on the server. Please try again.";
%>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <%= code %> | Student Portal</title>
    <link rel="stylesheet" href="<%= request.getContextPath() %>/css/style.css">
</head>
<body>
    <header class="site-header">
        <h1>Student Admission Portal</h1>
    </header>
    <main class="main-content">
        <div class="card form-card text-center">
            <h2>Error <%= code %></h2>
            <p><%= message %></p>
            <div class="button-row">
                <a href="<%= request.getContextPath() %>/DashboardServlet" class="btn btn-primary">Go to Dashboard</a>
            </div>
        </div>
    </main>
    <footer class="site-footer">&copy; 2026 Student Admission Portal</footer>
</body>
</html>
