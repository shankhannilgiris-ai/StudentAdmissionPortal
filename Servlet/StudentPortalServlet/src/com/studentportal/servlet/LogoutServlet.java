package com.studentportal.servlet;

import java.io.IOException;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;

import com.studentportal.util.SessionUtil;

/**
 * =====================================================================
 * File    : LogoutServlet.java
 * URL     : /LogoutServlet
 * Purpose : Destroys the session and sends the user back to login.jsp.
 * =====================================================================
 */
public class LogoutServlet extends HttpServlet {

    private static final long serialVersionUID = 1L;

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // getSession(false) -> do not create a new session just to destroy it
        HttpSession session = request.getSession(false);
        if (session != null) {
            session.invalidate();   // removes all session attributes (logout)
        }

        SessionUtil.noCache(response);
        response.sendRedirect("login.jsp?logout=1");
    }

    // Logout also works from a POST form
    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        doGet(request, response);
    }
}
