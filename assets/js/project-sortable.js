/**
 * Project Sortable Functionality
 * Handles drag and drop operations for tasks and sections within a project
 */

$(document).ready(function() {
    // Check if jQuery UI is loaded properly
    if (typeof $.ui === 'undefined') {
        console.error('jQuery UI is not loaded. Sortable functionality will not work.');
        return;
    }
    
    // Determine base URL for AJAX calls
    const getBaseUrl = () => {
        // Get the current path (e.g., /TamaEagle/projects/project.php)
        const path = window.location.pathname;
        // Extract the base path (e.g., /TamaEagle/)
        const basePath = path.substring(0, path.indexOf('/', 1) + 1);
        return basePath;
    };
    // Store base URL
    const baseUrl = getBaseUrl();

    // ==========================================================
    // Task sorting functionality - allows moving tasks between sections
    // ==========================================================
    if ($('.sortable-tasks').length) {
        $('.sortable-tasks').sortable({
            connectWith: '.sortable-tasks', // Allow dragging between different task lists
            placeholder: 'task-item task-placeholder',
            handle: '.drag-handle',
            cursor: 'move',
            opacity: 0.8,
            tolerance: 'pointer',
            start: function(e, ui) {
                ui.item.addClass('dragging');
                ui.placeholder.height(ui.item.height());
            },
            stop: function(e, ui) {
                ui.item.removeClass('dragging');
                
                // Get the relevant data
                const taskId = ui.item.data('id');
                const sectionId = ui.item.closest('.sortable-tasks').data('section-id');
                const position = ui.item.index();
                
                if (!taskId) {
                    console.error('Task ID not found on dragged item');
                    return;
                }
                
                // Send AJAX request to update position in database
                const taskApiUrl = baseUrl + 'api/update-task-position.php';
                
                // Add a timestamp to bypass cache
                const timestamp = new Date().getTime();
                
                $.ajax({
                    url: taskApiUrl + '?_=' + timestamp,
                    type: 'POST',
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Cache-Control': 'no-cache, no-store, must-revalidate'
                    },
                    data: {
                        task_id: taskId,
                        section_id: sectionId,
                        position: position
                    },
                    success: function(response) {
                        if (!response || !response.success) {
                            console.error('Error updating task position:', response ? response.message : 'No response data');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error updating task position:', error);
                    }
                });
            }
        }).disableSelection();
    }

    // ==========================================================
    // Section sorting functionality - allows reordering sections
    // ==========================================================
    if ($('.sortable-sections').length) {
        $('.sortable-sections').sortable({
            handle: '.section-drag-handle',
            placeholder: 'project-column section-placeholder',
            cursor: 'move',
            opacity: 0.8,
            tolerance: 'pointer',
            start: function(e, ui) {
                ui.item.addClass('dragging');
                ui.placeholder.height(ui.item.height());
                ui.placeholder.width(ui.item.width());
            },
            stop: function(e, ui) {
                ui.item.removeClass('dragging');
                
                // Get the relevant data
                const sectionId = ui.item.data('section-id');
                const position = ui.item.index();
                
                if (!sectionId) {
                    console.error('Section ID not found on dragged item');
                    return;
                }
                
                // Send AJAX request to update position in database
                const sectionApiUrl = baseUrl + 'api/update-section-position.php';
                
                // Add a timestamp to bypass cache
                const timestamp = new Date().getTime();
                
                $.ajax({
                    url: sectionApiUrl + '?_=' + timestamp,
                    type: 'POST',
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Cache-Control': 'no-cache, no-store, must-revalidate'
                    },
                    data: {
                        section_id: sectionId,
                        position: position
                    },
                    success: function(response) {
                        if (!response || !response.success) {
                            console.error('Error updating section position:', response ? response.message : 'No response data');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error updating section position:', error);
                    }
                });
            }
        }).disableSelection();
    }
});