/**
 * Project-specific task handling functionality
 * Ensures project context is maintained when adding/editing tasks
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get current project ID from URL
    function getCurrentProjectId() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('id');
    }

    // Override the default edit task behavior for project pages
    $(document).on('click', '.edit-task', function(e) {
        // If we're on a project page, ensure we keep the current project context
        if (window.location.pathname.includes('/projects/project.php')) {
            const currentProjectId = getCurrentProjectId();
            
            // Wait a bit for the modal to be populated by the main.js handler
            setTimeout(() => {
                // Update hidden project_id field if we're in a project context
                const projectIdField = document.querySelector('input[name="project_id"]');
                if (projectIdField && currentProjectId) {
                    projectIdField.value = currentProjectId;
                }
            }, 100);
        }
    });
});
