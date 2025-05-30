$(document).ready(function () {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });// Toggle sidebar for all screen sizes
    $('#sidebarToggle').on('click', function () {
        const windowWidth = $(window).width();
        
        if (windowWidth <= 768) {
            // Mobile behavior
            $('.sidebar').toggleClass('show');
            $('.content-container').toggleClass('sidebar-open');
        } else {
            // Desktop behavior
            const isCollapsed = $('body').toggleClass('sidebar-collapsed').hasClass('sidebar-collapsed');
            // Save state in localStorage
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        }
    });
    
    // Close sidebar button (for mobile view)
    $('#closeSidebar').on('click', function() {
        $('.sidebar').removeClass('show');
        $('.content-container').removeClass('sidebar-open');
    });

    // Task form submit handler
    $('#taskForm').on('submit', function () {
        // Validate dates
        const startDate = $('#startDate').val();
        const dueDate = $('#dueDate').val();
        const today = new Date().toISOString().split('T')[0];

        if (startDate < today) {
            alert('Start date cannot be in the past');
            return false;
        }

        if (dueDate < startDate) {
            alert('Due date cannot be before start date');
            return false;
        }

        return true;
    });

    // Edit task button handler
    $('.edit-task').on('click', function (e) {
        e.preventDefault();
        const taskId = $(this).data('id');
        const taskName = $(this).data('name');
        const taskDesc = $(this).data('description');
        const startDate = $(this).data('start-date');
        const dueDate = $(this).data('due-date');
        const priority = $(this).data('priority');
        const projectId = $(this).data('project-id');
        const sectionId = $(this).data('section-id');        // Set modal values
        $('#taskModalLabel').text('Edit Task');
        $('#taskId').val(taskId);
        $('#taskName').val(taskName);
        $('#taskDescription').val(taskDesc);
        $('#startDate').val(startDate);
        $('#dueDate').val(dueDate);
        $('#priority').val(priority);
        
        // Handle project field which could be either a dropdown or hidden field
        const projectSelect = document.getElementById('project');
        if (projectSelect) {
            $('#project').val(projectId);
        }
        // Hidden project_id field is already set with the current project
        
        $('#sectionId').val(sectionId);
        // Change form action
        $('#taskForm').attr('action', '../tasks/edit-task.php');

        // Show modal
        const taskModal = new bootstrap.Modal(document.getElementById('taskModal'));
        taskModal.show();
    });    // Bỏ hành vi JavaScript cho complete-task, để link HTML hoạt động tự nhiên
    // $('.complete-task').on('click', function (e) {
    //     const taskId = $(this).data('id');
    //     window.location.href = '../tasks/complete-task.php?id=' + taskId;
    // });    // Add section handler
    $('.add-section').on('click', function () {
        const projectId = $(this).data('project-id');
        
        if (!projectId) {
            alert('Error: Could not determine project ID');
            return;
        }
        
        const sectionName = prompt('Enter section name:');
        if (sectionName) {
            // Get the base URL from the current page
            const currentPath = window.location.pathname;
            const pathParts = currentPath.split('/');
            // Remove the last part (file name)
            pathParts.pop();
            
            // Construct absolute path to add-section.php
            const baseUrl = window.location.origin + pathParts.join('/') + '/../sections/add-section.php';
            
            window.location.href = baseUrl + '?project_id=' + projectId + '&name=' + encodeURIComponent(sectionName);
        }
    });

    // Initialize sortable lists for drag and drop
    if ($('.sortable-tasks').length) {
        $('.sortable-tasks').sortable({
            connectWith: '.sortable-tasks',
            placeholder: 'task-item task-placeholder',
            handle: '.drag-handle',
            start: function (e, ui) {
                ui.item.addClass('dragging');
            },
            stop: function (e, ui) {
                ui.item.removeClass('dragging');

                // Get the new positions
                const taskId = ui.item.data('id');
                const sectionId = ui.item.closest('.sortable-tasks').data('section-id');
                const position = ui.item.index();

                // Check if database helper is initialized
                if (typeof window.dbHelper !== 'undefined') {
                    // Use dbHelper regardless of database type
                    window.dbHelper.updateTaskPosition(taskId, sectionId, position)
                        .then(() => {
                            console.log('Task position updated successfully');
                        })
                        .catch(error => {
                            console.error('Error updating task position:', error);
                        });
                } else {
                    console.warn('Database helper not found, using fallback AJAX');
                    // Fall back to AJAX if helper isn't loaded
                    $.ajax({
                        url: '../api/update-task-position.php',
                        type: 'POST',
                        data: {
                            task_id: taskId,
                            section_id: sectionId,
                            position: position
                        },
                        success: function (response) {
                            console.log('Position updated successfully');
                        },
                        error: function (xhr, status, error) {
                            console.error('Error updating position:', error);
                        }
                    });
                }
            }
        }).disableSelection();
    }

    // Initialize sortable sections for drag and drop
    if ($('.sortable-sections').length) {
        $('.sortable-sections').sortable({
            handle: '.section-drag-handle',
            start: function (e, ui) {
                ui.item.addClass('dragging');
            },
            stop: function (e, ui) {
                ui.item.removeClass('dragging');

                // Get the new positions
                const sectionId = ui.item.data('section-id');
                const position = ui.item.index();

                // Check if database helper is initialized
                if (typeof window.dbHelper !== 'undefined') {
                    // Use dbHelper regardless of database type
                    window.dbHelper.updateSectionPosition(sectionId, position)
                        .then(() => {
                            console.log('Section position updated successfully');
                        })
                        .catch(error => {
                            console.error('Error updating section position:', error);
                        });
                } else {
                    console.warn('Database helper not found, using fallback AJAX');
                    // Fall back to AJAX
                    $.ajax({
                        url: '../api/update-section-position.php',
                        type: 'POST',
                        data: {
                            section_id: sectionId,
                            position: position
                        },
                        success: function (response) {
                            console.log('Section position updated successfully');
                        },
                        error: function (xhr, status, error) {
                            console.error('Error updating section position:', error);
                        }
                    });
                }
            }
        }).disableSelection();
    }    // Header add task button removed as per requirements
    // Now tasks can only be added from project pages
    // Date navigation buttons
    $('.date-nav-prev').on('click', function () {
        const currentWeekStart = $(this).data('current');
        window.location.href = 'upcoming.php?week=' + currentWeekStart;
    });

    $('.date-nav-next').on('click', function () {
        const nextWeekStart = $(this).data('next');
        window.location.href = 'upcoming.php?week=' + nextWeekStart;
    });

    $('.date-nav-today').on('click', function () {
        window.location.href = 'upcoming.php';
    });
});
