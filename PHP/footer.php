<?php
/* =====================================================================
   File    : footer.php
   Purpose : Common bottom part of every PHP page - closes the main
             area, prints the footer and loads script.js.
   ===================================================================== */
?>
    </main>

    <!-- ===== Simple footer ===== -->
    <footer class="site-footer">
        &copy; <?php echo date('Y'); ?> Student Admission Portal | Department of Computer Science and Engineering
    </footer>

    <!-- Client-side validation and helper functions -->
    <script src="../Frontend/script.js"></script>
</body>
</html>
<?php
// Close the database connection at the end of every page
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>
