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

    // ==========================================================    // Task sorting functionality - allows moving tasks between sections
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
                
                // Store original position for revert if needed
                ui.item.data('orig-position', ui.item.index());
                ui.item.data('orig-section', ui.item.closest('.sortable-tasks').data('section-id'));
            },
            stop: function(e, ui) {
                ui.item.removeClass('dragging');
                
                // Get the relevant data
                const taskId = ui.item.data('id');
                const sectionId = ui.item.closest('.sortable-tasks').data('section-id');
                const position = ui.item.index();
                const totalTasks = ui.item.closest('.sortable-tasks').find('.task-item').length;
                
                if (!taskId) {
                    console.error('Task ID not found on dragged item');
                    return;
                }
                
                // Validate position (should be between 0 and total tasks - 1)
                const validPosition = Math.max(0, Math.min(position, totalTasks - 1));
                
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
                        position: validPosition
                    },
                    success: function(response) {
                        if (!response || !response.success) {
                            console.error('Error updating task position:', response ? response.message : 'No response data');
                            // Revert to original position if there's an error
                            const origSection = ui.item.data('orig-section');
                            const origPosition = ui.item.data('orig-position');
                            
                            if (origSection && origPosition !== undefined) {
                                // Find original section and move item back
                                const $origSectionList = $(`.sortable-tasks[data-section-id="${origSection}"]`);
                                if ($origSectionList.length) {
                                    const items = $origSectionList.find('.task-item');
                                    if (items.length > 0) {
                                        if (origPosition < items.length) {
                                            ui.item.insertBefore(items.eq(origPosition));
                                        } else {
                                            ui.item.appendTo($origSectionList);
                                        }
                                    }
                                }
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error updating task position:', error);
                        // Similar revert logic could go here
                    }
                });
            }
        }).disableSelection();
    }

    // ==========================================================    // Section sorting functionality - allows reordering sections
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
                
                // Store original position for revert if needed
                ui.item.data('orig-position', ui.item.index());
            },
            stop: function(e, ui) {
                ui.item.removeClass('dragging');
                
                // Get the relevant data
                const sectionId = ui.item.data('section-id');
                const position = ui.item.index();
                const totalSections = $('.sortable-sections').children().length;
                
                if (!sectionId) {
                    console.error('Section ID not found on dragged item');
                    return;
                }
                
                // Validate position (should be between 0 and total sections - 1)
                const validPosition = Math.max(0, Math.min(position, totalSections - 1));
                
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
                        position: validPosition
                    },
                    success: function(response) {
                        if (!response || !response.success) {
                            console.error('Error updating section position:', response ? response.message : 'No response data');
                            // Revert to original position if there's an error
                            const origPosition = ui.item.data('orig-position');
                            
                            if (origPosition !== undefined) {
                                const $container = $('.sortable-sections');
                                const items = $container.children();
                                
                                if (origPosition < items.length) {
                                    ui.item.insertBefore(items.eq(origPosition));
                                } else {
                                    ui.item.appendTo($container);
                                }
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error updating section position:', error);
                        // Similar revert logic could go here
                    }
                });
            }
        }).disableSelection();
    }
});