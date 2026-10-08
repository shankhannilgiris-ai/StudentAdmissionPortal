package com.studentportal.servlet;

import java.io.IOException;
import java.sql.SQLException;
import java.util.List;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import com.studentportal.dao.StudentDAO;
import com.studentportal.dao.StudentFileDAO;
import com.studentportal.model.Student;
import com.studentportal.model.StudentFile;
import com.studentportal.util.SessionUtil;

/**
 * =====================================================================
 * File    : StudentProfileServlet.java
 * URL     : /StudentProfileServlet?id=1
 * Purpose : Fetches one student and all of his/her uploaded files and
 *           forwards them to student_profile.jsp (dynamic profile page).
 * =====================================================================
 */
public class StudentProfileServlet extends HttpServlet {

    private static final long serialVersionUID = 1L;

    private final StudentDAO studentDAO = new StudentDAO();
    private final StudentFileDAO fileDAO = new StudentFileDAO();

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        if (!SessionUtil.checkLogin(request, response)) {
            return;
        }

        // 1. Read and validate the id parameter
        int id;
        try {
            id = Integer.parseInt(request.getParameter("id"));
        } catch (NumberFormatException e) {      // missing or not a number
            id = -1;
        }
        if (id < 1) {
            response.sendRedirect("StudentListServlet");
            return;
        }

        try {
            // 2. Student details
            Student student = studentDAO.getStudentById(id);
            if (student == null) {
                request.setAttribute("error", "No student found with id " + id + ".");
            } else {
                // 3. Uploaded file information of the same student
                List<StudentFile> files = fileDAO.getFilesByStudentId(id);
                request.setAttribute("student", student);
                request.setAttribute("files", files);
            }
        } catch (SQLException e) {
            log("Student profile query failed", e);
            request.setAttribute("error", "Could not load the profile: " + e.getMessage());
        }

        request.getRequestDispatcher("/WEB-INF/views/student_profile.jsp").forward(request, response);
    }
}
