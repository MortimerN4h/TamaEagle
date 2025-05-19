$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Toggle sidebar on mobile
    $('#sidebarToggle').on('click', function() {
        $('.sidebar').toggleClass('show');
        $('.content-container').toggleClass('sidebar-open');
    });

    // Task form submit handler
    $('#taskForm').on('submit', function() {
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
    $('.edit-task').on('click', function(e) {
        e.preventDefault();
        const taskId = $(this).data('id');
        const taskName = $(this).data('name');
        const taskDesc = $(this).data('description');
        const startDate = $(this).data('start-date');
        const dueDate = $(this).data('due-date');
        const priority = $(this).data('priority');
        const projectId = $(this).data('project-id');
        const sectionId = $(this).data('section-id');
        
        // Set modal values
        $('#taskModalLabel').text('Edit Task');
        $('#taskId').val(taskId);
        $('#taskName').val(taskName);
        $('#taskDescription').val(taskDesc);
        $('#startDate').val(startDate);
        $('#dueDate').val(dueDate);
        $('#priority').val(priority);
        $('#project').val(projectId);
        $('#sectionId').val(sectionId);
        
        // Change form action
        $('#taskForm').attr('action', 'edit-task.php');
        
        // Show modal
        const taskModal = new bootstrap.Modal(document.getElementById('taskModal'));
        taskModal.show();
    });
    
    // Complete task button handler
    $('.complete-task').on('click', function(e) {
        const taskId = $(this).data('id');
        window.location.href = 'complete-task.php?id=' + taskId;
    });
    
    // Add section handler
    $('.add-section').on('click', function() {
        const projectId = $(this).data('project-id');
        const sectionName = prompt('Enter section name:');
        
        if (sectionName) {
            window.location.href = 'add-section.php?project_id=' + projectId + '&name=' + encodeURIComponent(sectionName);
        }
    });
    
    // Initialize sortable lists for drag and drop
    if ($('.sortable-tasks').length) {
        $('.sortable-tasks').sortable({
            connectWith: '.sortable-tasks',
            placeholder: 'task-item task-placeholder',
            handle: '.drag-handle',
            start: function(e, ui) {
                ui.item.addClass('dragging');
            },
            stop: function(e, ui) {
                ui.item.removeClass('dragging');
                
                // Get the new positions
                const taskId = ui.item.data('id');
                const sectionId = ui.item.closest('.sortable-tasks').data('section-id');
                const position = ui.item.index();
                
                // Send AJAX request to update position
                $.ajax({
                    url: 'update-task-position.php',
                    type: 'POST',
                    data: {
                        task_id: taskId,
                        section_id: sectionId,
                        position: position
                    },
                    success: function(response) {
                        console.log('Position updated successfully');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error updating position:', error);
                    }
                });
            }
        }).disableSelection();
    }
    
    // Initialize sortable sections for drag and drop
    if ($('.sortable-sections').length) {
        $('.sortable-sections').sortable({
            handle: '.section-drag-handle',
            start: function(e, ui) {
                ui.item.addClass('dragging');
            },
            stop: function(e, ui) {
                ui.item.removeClass('dragging');
                
                // Get the new positions
                const sectionId = ui.item.data('section-id');
                const position = ui.item.index();
                
                // Send AJAX request to update position
                $.ajax({
                    url: 'update-section-position.php',
                    type: 'POST',
                    data: {
                        section_id: sectionId,
                        position: position
                    },
                    success: function(response) {
                        console.log('Section position updated successfully');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error updating section position:', error);
                    }
                });
            }
        }).disableSelection();
    }
    
    // Reset task form when opening the modal for a new task
    $('#addTaskBtn').on('click', function() {
        $('#taskModalLabel').text('Add New Task');
        $('#taskForm').attr('action', 'add-task.php');
        $('#taskId').val('');
        $('#taskName').val('');
        $('#taskDescription').val('');
        $('#startDate').val(new Date().toISOString().split('T')[0]);
        $('#dueDate').val(new Date().toISOString().split('T')[0]);
        $('#priority').val(0);
        $('#project').val('');
        $('#sectionId').val('');
    });
    
    // Date navigation buttons
    $('.date-nav-prev').on('click', function() {
        const currentWeekStart = $(this).data('current');
        window.location.href = 'upcoming.php?week=' + currentWeekStart;
    });
    
    $('.date-nav-next').on('click', function() {
        const nextWeekStart = $(this).data('next');
        window.location.href = 'upcoming.php?week=' + nextWeekStart;
    });
    
    $('.date-nav-today').on('click', function() {
        window.location.href = 'upcoming.php';
    });
});