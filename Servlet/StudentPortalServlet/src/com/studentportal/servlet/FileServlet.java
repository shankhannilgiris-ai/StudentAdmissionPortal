package com.studentportal.servlet;

import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.net.URLEncoder;
import java.sql.SQLException;

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
 * File    : FileServlet.java  (Image / File Streaming)
 * URLs    : FileServlet?id=1                    -> profile image of student 1
 *           FileServlet?fileId=4                -> view uploaded file 4 in browser
 *           FileServlet?fileId=4&download=true  -> download uploaded file 4
 * Purpose : Reads the file from the PHP module's uploads folder on the
 *           disk and streams its bytes to the browser. Because the
 *           browser only sees this Servlet URL, the real folder path is
 *           hidden and only logged-in users can see student files.
 *           Usage in JSP:  <img src="FileServlet?id=1">
 * =====================================================================
 */
public class FileServlet extends HttpServlet {

    private static final long serialVersionUID = 1L;

    // Bytes copied in one read/write step (8 KB)
    private static final int BUFFER_SIZE = 8192;

    private final StudentDAO studentDAO = new StudentDAO();
    private final StudentFileDAO fileDAO = new StudentFileDAO();

    // Base folder that contains "uploads/" (read from web.xml)
    private File baseDir;

    @Override
    public void init() throws ServletException {
        // <context-param> uploadBaseDir in web.xml, e.g. C:/xampp/htdocs/StudentAdmissionPortal/PHP
        String path = getServletContext().getInitParameter("uploadBaseDir");
        if (path == null || path.trim().isEmpty()) {
            throw new ServletException("Context parameter 'uploadBaseDir' is missing in web.xml");
        }
        baseDir = new File(path.trim());
        log("FileServlet will read files from: " + baseDir.getAbsolutePath());
    }

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // Only logged-in users may see student files
        if (!SessionUtil.isLoggedIn(request)) {
            response.sendError(HttpServletResponse.SC_UNAUTHORIZED, "Please login first.");
            return;
        }

        String relativePath = null;   // e.g. uploads/images/1700000001_arun.png
        String downloadName = null;   // name suggested to the browser
        boolean download = "true".equalsIgnoreCase(request.getParameter("download"));

        try {
            String studentIdParam = request.getParameter("id");
            String fileIdParam = request.getParameter("fileId");

            if (studentIdParam != null) {
                // ----- Mode 1: profile image of a student -----
                Student student = studentDAO.getStudentById(Integer.parseInt(studentIdParam));
                if (student != null && student.hasProfileImage()) {
                    relativePath = student.getProfileImage();
                    downloadName = new File(relativePath).getName();
                }
            } else if (fileIdParam != null) {
                // ----- Mode 2: any uploaded file from student_files -----
                StudentFile sf = fileDAO.getFileById(Integer.parseInt(fileIdParam));
                if (sf != null) {
                    relativePath = sf.getFilePath();
                    downloadName = sf.getFileName();
                }
            }
        } catch (NumberFormatException e) {
            response.sendError(HttpServletResponse.SC_BAD_REQUEST, "Invalid id.");
            return;
        } catch (SQLException e) {
            log("FileServlet database error", e);
            response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Database error.");
            return;
        }

        // No record in the database
        if (relativePath == null) {
            response.sendError(HttpServletResponse.SC_NOT_FOUND, "File not found.");
            return;
        }

        // Build the full path and make sure it stays INSIDE the base folder
        // (stops "../../" path traversal tricks)
        File file = new File(baseDir, relativePath);
        String basePath = baseDir.getCanonicalPath() + File.separator;
        if (!file.getCanonicalPath().startsWith(basePath) || !file.isFile()) {
            response.sendError(HttpServletResponse.SC_NOT_FOUND, "File not found on the server.");
            return;
        }

        // Content-Type from the extension (image/png, application/pdf ...)
        String mimeType = getServletContext().getMimeType(file.getName());
        if (mimeType == null) {
            mimeType = "application/octet-stream";
        }
        response.setContentType(mimeType);
        response.setContentLengthLong(file.length());

        // inline = show in browser,  attachment = "Save As" download
        String encodedName = URLEncoder.encode(downloadName, "UTF-8").replace("+", "%20");
        String disposition = download ? "attachment" : "inline";
        response.setHeader("Content-Disposition",
                disposition + "; filename=\"" + encodedName + "\"; filename*=UTF-8''" + encodedName);

        // Stream the bytes: read from the file -> write to the response
        try (InputStream in = new FileInputStream(file);
             OutputStream out = response.getOutputStream()) {
            byte[] buffer = new byte[BUFFER_SIZE];
            int bytesRead;
            while ((bytesRead = in.read(buffer)) != -1) {
                out.write(buffer, 0, bytesRead);
            }
        }
    }
}
