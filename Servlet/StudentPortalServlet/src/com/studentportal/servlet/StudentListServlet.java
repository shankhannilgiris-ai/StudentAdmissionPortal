package com.studentportal.servlet;

import java.io.IOException;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import com.studentportal.dao.StudentDAO;
import com.studentportal.model.Student;
import com.studentportal.util.SessionUtil;

/**
 * =====================================================================
 * File    : StudentListServlet.java
 * URL     : /StudentListServlet            -> all students
 *           /StudentListServlet?search=CSE -> filtered list
 * Purpose : Fetches student details from MySQL (through StudentDAO)
 *           and forwards the list to student_list.jsp.
 * =====================================================================
 */
public class StudentListServlet extends HttpServlet {

    private static final long serialVersionUID = 1L;

    private final StudentDAO studentDAO = new StudentDAO();

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // Session check
        if (!SessionUtil.checkLogin(request, response)) {
            return;
        }

        String search = request.getParameter("search");
        search = (search == null) ? "" : search.trim();

        List<Student> students = new ArrayList<>();
        try {
            // Conditional logic: search only when a keyword is given
            if (search.isEmpty()) {
                students = studentDAO.getAllStudents();
            } else {
                students = studentDAO.searchStudents(search);
            }
        } catch (SQLException e) {
            log("Student list query failed", e);
            request.setAttribute("error", "Could not load students: " + e.getMessage());
        }

        request.setAttribute("students", students);
        request.setAttribute("search", search);
        request.getRequestDispatcher("/WEB-INF/views/student_list.jsp").forward(request, response);
    }
}
