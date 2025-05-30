</div> <!-- End of main-content -->
</div> <!-- End of content-container -->
<!-- Task modal for adding/editing tasks -->
<?php include 'task-modal.php'; ?>

<!-- Task details modal for viewing task details -->
<?php include 'task-details-modal.php'; ?>

</div> <!-- End of wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- jQuery UI for Drag & Drop -->
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<?php
// Get the base directory path
$requestUri = $_SERVER['REQUEST_URI'];
$tamaEaglePos = strpos($requestUri, 'TamaEagle');
$baseDir = '/';
if ($tamaEaglePos !== false) {
    $baseDir = substr($requestUri, 0, $tamaEaglePos) . 'TamaEagle/';
}
?>
<script src="<?php echo $baseDir; ?>assets/js/main.js"></script>
<script src="<?php echo $baseDir; ?>assets/js/notifications.js"></script>
<script src="<?php echo $baseDir; ?>assets/js/task-details.js"></script>
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Check for saved sidebar state
    const sidebarState = localStorage.getItem('sidebarCollapsed');
    if (sidebarState === 'true') {
        document.body.classList.add('sidebar-collapsed');
    }
</script>
</body>

</html>