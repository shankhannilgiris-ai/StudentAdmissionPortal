package com.studentportal.util;

import java.io.IOException;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;

/**
 * =====================================================================
 * File    : SessionUtil.java
 * Purpose : Helper methods used by every protected Servlet:
 *           - checks whether the admin is logged in (session check)
 *           - redirects unauthorized users to the login page
 *           - disables browser caching so "Back" after logout does not
 *             show protected pages
 *           - escapes text for safe HTML output (prevents XSS)
 * =====================================================================
 */
public class SessionUtil {

    // Name of the session attribute created by LoginServlet
    public static final String SESSION_ADMIN = "admin";

    private SessionUtil() {
    }

    /** Returns true when a valid login session exists. */
    public static boolean isLoggedIn(HttpServletRequest request) {
        // getSession(false) -> returns null instead of creating a new session
        HttpSession session = request.getSession(false);
        return session != null && session.getAttribute(SESSION_ADMIN) != null;
    }

    /**
     * Used at the start of every protected Servlet.
     * Returns true if the user is logged in; otherwise redirects to
     * login.jsp and returns false (the Servlet must then stop).
     */
    public static boolean checkLogin(HttpServletRequest request, HttpServletResponse response)
            throws IOException {
        noCache(response);
        if (!isLoggedIn(request)) {
            response.sendRedirect(request.getContextPath() + "/login.jsp?auth=required");
            return false;
        }
        return true;
    }

    /** Tells the browser not to store protected pages in its cache. */
    public static void noCache(HttpServletResponse response) {
        response.setHeader("Cache-Control", "no-cache, no-store, must-revalidate");
        response.setHeader("Pragma", "no-cache");
        response.setDateHeader("Expires", 0);
    }

    /** Escapes special HTML characters: <script> becomes &lt;script&gt; */
    public static String escape(String text) {
        if (text == null) {
            return "";
        }
        StringBuilder sb = new StringBuilder(text.length());
        for (char c : text.toCharArray()) {
            switch (c) {
                case '<':  sb.append("&lt;");   break;
                case '>':  sb.append("&gt;");   break;
                case '&':  sb.append("&amp;");  break;
                case '"':  sb.append("&quot;"); break;
                case '\'': sb.append("&#39;");  break;
                default:   sb.append(c);
            }
        }
        return sb.toString();
    }
}
