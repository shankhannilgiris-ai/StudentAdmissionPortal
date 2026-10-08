package com.studentportal.servlet;

import java.io.IOException;
import java.sql.SQLException;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;

import com.studentportal.dao.AdminDAO;
import com.studentportal.model.Admin;
import com.studentportal.util.SessionUtil;

/**
 * =====================================================================
 * File    : LoginServlet.java
 * URL     : /LoginServlet   (mapped in web.xml)
 * Purpose : GET  -> shows login.jsp
 *           POST -> checks username/password using AdminDAO.
 *                   Success: creates a session and opens the dashboard.
 *                   Failure: shows login.jsp again with an error message.
 * =====================================================================
 */
public class LoginServlet extends HttpServlet {

    private static final long serialVersionUID = 1L;

    // Session expires after 30 minutes of inactivity
    private static final int SESSION_TIMEOUT_SECONDS = 30 * 60;

    private final AdminDAO adminDAO = new AdminDAO();

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        // Already logged in? Go straight to the dashboard.
        if (SessionUtil.isLoggedIn(request)) {
            response.sendRedirect("DashboardServlet");
            return;
        }
        request.getRequestDispatcher("/login.jsp").forward(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // 1. Read the form values
        String username = request.getParameter("username");
        String password = request.getParameter("password");
        username = (username == null) ? "" : username.trim();
        password = (password == null) ? "" : password;

        // 2. Server-side validation: both fields required
        if (username.isEmpty() || password.isEmpty()) {
            showError(request, response, username, "Username and password are required.");
            return;
        }

        try {
            // 3. Check the credentials in the database
            Admin admin = adminDAO.validateLogin(username, password);

            if (admin == null) {
                showError(request, response, username, "Invalid username or password.");
                return;
            }

            // 4. Session creation
            //    Invalidate any old session first (prevents session fixation attack)
            HttpSession oldSession = request.getSession(false);
            if (oldSession != null) {
                oldSession.invalidate();
            }
            HttpSession session = request.getSession(true);          // new session
            session.setAttribute(SessionUtil.SESSION_ADMIN, admin);  // mark as logged in
            session.setMaxInactiveInterval(SESSION_TIMEOUT_SECONDS);

            // 5. Redirect (not forward) so refreshing the page does not resend the password
            response.sendRedirect("DashboardServlet");

        } catch (SQLException e) {
            // SQL error handling: log the details on the server, show a simple message
            log("Login failed because of a database error", e);
            showError(request, response, username,
                      "Database error. Please make sure MySQL is running. (" + e.getMessage() + ")");
        }
    }

    // Puts the error message in the request and shows login.jsp again
    private void showError(HttpServletRequest request, HttpServletResponse response,
                           String username, String message) throws ServletException, IOException {
        request.setAttribute("error", message);
        request.setAttribute("username", username);   // keep the typed username
        request.getRequestDispatcher("/login.jsp").forward(request, response);
    }
}
