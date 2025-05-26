/**
 * TamaEagle Task Management JavaScript
 * Firebase version
 */

document.addEventListener('DOMContentLoaded', function() {
    // Task Form Handling
    const taskForm = document.getElementById('taskForm');
    
    if (taskForm) {
        taskForm.addEventListener('submit', handleTaskFormSubmit);
    }
    
    // Task Actions
    setupTaskActions();
    
    // Date shortcuts
    setupDateShortcuts();
});

// Handle task form submission
async function handleTaskFormSubmit(e) {
    e.preventDefault();
    
    const taskId = document.getElementById('taskId').value;
    const isEdit = taskId !== '';
    
    const formData = new FormData(e.target);
    const taskData = Object.fromEntries(formData.entries());
    
    try {
        let response;
        
        if (isEdit) {
            // Edit existing task
            response = await fetch('../tasks/edit-task-firebase.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(taskData)
            });
        } else {
            // Add new task
            response = await fetch('../tasks/add-task-firebase.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(taskData)
            });
        }
        
        const result = await response.json();
        
        if (result.success) {
            // Close modal and refresh page
            const modal = bootstrap.Modal.getInstance(document.getElementById('taskModal'));
            modal.hide();
            window.location.reload();
        } else {
            alert(result.error || 'An error occurred while saving the task');
        }
    } catch (error) {
        console.error('Error saving task:', error);
        alert('Failed to save task. Please try again.');
    }
}

// Setup task actions (complete, edit, delete)
function setupTaskActions() {
    // Complete/uncomplete task
    document.querySelectorAll('.complete-task').forEach(checkbox => {
        checkbox.addEventListener('change', async function() {
            const taskId = this.getAttribute('data-task-id');
            const endpoint = this.checked 
                ? '../tasks/complete-task-firebase.php' 
                : '../tasks/uncomplete-task-firebase.php';
                
            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({task_id: taskId})
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Find the task item and update its appearance
                    const taskItem = this.closest('.task-item');
                    taskItem.classList.toggle('completed', this.checked);
                    
                    // Optional: Refresh page after a delay
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    alert(result.error || 'Failed to update task status');
                    this.checked = !this.checked; // Revert the checkbox state
                }
            } catch (error) {
                console.error('Error updating task status:', error);
                alert('Failed to update task status. Please try again.');
                this.checked = !this.checked; // Revert the checkbox state
            }
        });
    });
    
    // Uncomplete task (used on completed tasks page)
    document.querySelectorAll('.uncomplete-task').forEach(checkbox => {
        checkbox.addEventListener('change', async function() {
            if (!this.checked) {
                const taskId = this.getAttribute('data-task-id');
                
                try {
                    const response = await fetch('../tasks/uncomplete-task-firebase.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({task_id: taskId})
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Remove the task item from the list
                        const taskItem = this.closest('.task-item');
                        taskItem.remove();
                    } else {
                        alert(result.error || 'Failed to uncomplete the task');
                        this.checked = true; // Revert the checkbox state
                    }
                } catch (error) {
                    console.error('Error uncompleting task:', error);
                    alert('Failed to uncomplete task. Please try again.');
                    this.checked = true; // Revert the checkbox state
                }
            }
        });
    });
    
    // Edit task
    document.querySelectorAll('.edit-task').forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.getAttribute('data-task-id');
            const taskItem = this.closest('.task-item');
            
            // Get task data
            const title = taskItem.querySelector('.task-title').textContent.trim();
            const description = taskItem.querySelector('.task-description')?.textContent.trim() || '';
            
            // Get due date
            let dueDate = '';
            const dueDateElement = taskItem.querySelector('.task-due-date');
            if (dueDateElement) {
                const dueDateText = dueDateElement.textContent.trim().replace('Due: ', '');
                if (dueDateText !== 'Today') {
                    // Convert "Mar 15, 2023" to "2023-03-15"
                    const date = new Date(dueDateText);
                    if (!isNaN(date.getTime())) {
                        dueDate = date.toISOString().split('T')[0];
                    }
                } else {
                    dueDate = new Date().toISOString().split('T')[0];
                }
            }
            
            // Set priority
            let priority = 'medium';
            if (taskItem.classList.contains('priority-high')) {
                priority = 'high';
            } else if (taskItem.classList.contains('priority-low')) {
                priority = 'low';
            }
            
            // Open modal with task data
            const modal = new bootstrap.Modal(document.getElementById('taskModal'));
            
            // Set form values
            document.getElementById('taskModalLabel').textContent = 'Edit Task';
            document.getElementById('taskId').value = taskId;
            document.getElementById('taskName').value = title;
            document.getElementById('taskDescription').value = description;
            document.getElementById('dueDate').value = dueDate;
            
            // Set priority
            const priorityRadios = document.querySelectorAll(`input[name="priority"][value="${priority}"]`);
            if (priorityRadios.length > 0) {
                priorityRadios[0].checked = true;
            }
            
            modal.show();
        });
    });
    
    // Delete task
    document.querySelectorAll('.delete-task').forEach(button => {
        button.addEventListener('click', async function() {
            if (confirm('Are you sure you want to delete this task?')) {
                const taskId = this.getAttribute('data-task-id');
                
                try {
                    const response = await fetch('../tasks/delete-task-firebase.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({task_id: taskId})
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Remove the task item from the list
                        const taskItem = this.closest('.task-item');
                        taskItem.remove();
                    } else {
                        alert(result.error || 'Failed to delete the task');
                    }
                } catch (error) {
                    console.error('Error deleting task:', error);
                    alert('Failed to delete task. Please try again.');
                }
            }
        });
    });
}

// Setup date shortcuts
function setupDateShortcuts() {
    document.querySelectorAll('.date-shortcut').forEach(button => {
        button.addEventListener('click', function() {
            const startDate = this.getAttribute('data-start');
            const dueDate = this.getAttribute('data-due');
            
            document.getElementById('startDate').value = startDate;
            document.getElementById('dueDate').value = dueDate;
        });
    });
}

// Function to clear all completed tasks
function clearAllCompleted() {
    if (confirm('Are you sure you want to clear all completed tasks? This action cannot be undone.')) {
        fetch('../tasks/clear-completed-firebase.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                window.location.reload();
            } else {
                alert(result.error || 'Failed to clear completed tasks');
            }
        })
        .catch(error => {
            console.error('Error clearing completed tasks:', error);
            alert('Failed to clear completed tasks. Please try again.');
        });
    }
}