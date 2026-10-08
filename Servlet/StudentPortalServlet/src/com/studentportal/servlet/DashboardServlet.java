package com.studentportal.servlet;

import java.io.IOException;
import java.sql.SQLException;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import com.studentportal.dao.StudentDAO;
import com.studentportal.dao.StudentFileDAO;
import com.studentportal.util.SessionUtil;

/**
 * =====================================================================
 * File    : DashboardServlet.java
 * URL     : /DashboardServlet
 * Purpose : Collects summary numbers (total students, total files,
 *           students per course, recent registrations) and forwards
 *           them to dashboard.jsp.
 * =====================================================================
 */
public class DashboardServlet extends HttpServlet {

    private static final long serialVersionUID = 1L;

    private final StudentDAO studentDAO = new StudentDAO();
    private final StudentFileDAO fileDAO = new StudentFileDAO();

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // Restrict page: not logged in -> redirect to login page
        if (!SessionUtil.checkLogin(request, response)) {
            return;
        }

        try {
            // Values read by dashboard.jsp using request.getAttribute()
            request.setAttribute("totalStudents", studentDAO.countStudents());
            request.setAttribute("totalFiles", fileDAO.countFiles());
            request.setAttribute("totalDocuments", fileDAO.countDocuments());
            request.setAttribute("courseCounts", studentDAO.countByCourse());
            request.setAttribute("recentStudents", studentDAO.getRecentStudents(5));
        } catch (SQLException e) {
            log("Dashboard query failed", e);
            request.setAttribute("error", "Could not load dashboard data: " + e.getMessage());
        }

        // JSP is inside WEB-INF so it can only be opened through this Servlet
        request.getRequestDispatcher("/WEB-INF/views/dashboard.jsp").forward(request, response);
    }
}
