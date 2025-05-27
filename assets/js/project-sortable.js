/**
 * Project Sortable Functionality
 * Handles drag and drop operations for tasks and sections within a project
 * Created: May 27, 2025
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
    
    console.log('Project sortable functionality initialized');
    console.log('Base URL for AJAX calls:', baseUrl);
    
    // Add a refresh button for debugging
    const addDebugRefreshButton = () => {
        const button = $('<button>')
            .text('🔄 Refresh Page')
            .addClass('btn btn-sm btn-info position-fixed')
            .css({
                bottom: '20px',
                right: '20px',
                zIndex: 9999,
                padding: '5px 10px',
                fontSize: '12px'
            })
            .attr('title', 'Refresh page to see if position changes were saved')
            .on('click', function() {
                window.location.reload();
            });
        
        $('body').append(button);
    };
    
    // Only add in development mode or when URL has debug parameter
    if (window.location.href.includes('debug=true') || 
        window.location.hostname === 'localhost' || 
        window.location.hostname === '127.0.0.1') {
        addDebugRefreshButton();
    }

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
                
                console.log('Task moved:', {
                    taskId: taskId,
                    sectionId: sectionId,
                    position: position
                });
                  // Send AJAX request to update position in database                const taskApiUrl = baseUrl + 'api/update-task-position.php';
                console.log('Sending task position update to:', taskApiUrl);
                
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
                        if (response && response.success) {
                            console.log('✓ Task position updated successfully', response);
                        } else {
                            console.error('× Error updating task position:', response ? response.message : 'No response data');
                            console.log('Full response:', response);
                            // You could revert the UI here if needed
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('× AJAX error updating task position:', error);
                        console.log('Status:', status);
                        console.log('Response text:', xhr.responseText);
                        console.log('Status code:', xhr.status);
                        
                        // Try to parse the response if possible
                        try {
                            const errorData = JSON.parse(xhr.responseText);
                            console.log('Parsed error data:', errorData);
                        } catch(e) {
                            console.log('Could not parse error response as JSON');
                        }
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
                
                console.log('Section moved:', {
                    sectionId: sectionId,
                    position: position
                });
                  // Send AJAX request to update position in database                const sectionApiUrl = baseUrl + 'api/update-section-position.php';
                console.log('Sending section position update to:', sectionApiUrl);
                
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
                        if (response && response.success) {
                            console.log('✓ Section position updated successfully', response);
                        } else {
                            console.error('× Error updating section position:', response ? response.message : 'No response data');
                            console.log('Full response:', response);
                            // You could revert the UI here if needed
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('× AJAX error updating section position:', error);
                        console.log('Status:', status);
                        console.log('Response text:', xhr.responseText);
                        console.log('Status code:', xhr.status);
                        
                        // Try to parse the response if possible
                        try {
                            const errorData = JSON.parse(xhr.responseText);
                            console.log('Parsed error data:', errorData);
                        } catch(e) {
                            console.log('Could not parse error response as JSON');
                        }
                    }
                });
            }
        }).disableSelection();
    }
    
    // ==========================================================
    // Debug helper function - can be called from console
    // ==========================================================
    window.testPositionUpdate = function(type, id, sectionId, position) {
        console.log(`Testing ${type} position update:`, { id, sectionId, position });
          let url, data;
        if (type === 'task') {
            url = baseUrl + 'api/update-task-position.php';
            data = {
                task_id: id,
                section_id: sectionId,
                position: position
            };
        } else if (type === 'section') {
            url = baseUrl + 'api/update-section-position.php';
            data = {
                section_id: id,
                position: position
            };
        } else {
            console.error('Invalid type. Use "task" or "section"');
            return;
        }
          // Add timestamp to prevent caching
        const timestamp = new Date().getTime();
        
        $.ajax({
            url: url + '?_=' + timestamp,
            type: 'POST',
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache, no-store, must-revalidate'
            },
            data: data,
            success: function(response) {
                console.log('✓ Test update response:', response);
                if (response && response.success) {
                    alert('Position update successful! Try refreshing the page to verify changes were saved.');
                } else {
                    alert('Position update failed: ' + (response ? response.message : 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('× Test update error:', error);
                console.log('Status:', status);
                console.log('Response text:', xhr.responseText);
                console.log('Status code:', xhr.status);
                
                alert('Error updating position. Check the console for details.');
                
                // Try to parse response
                try {
                    const errorData = JSON.parse(xhr.responseText);
                    console.log('Error details:', errorData);
                } catch (e) {
                    console.log('Could not parse error response as JSON');
                }
            }
        });
    };
});