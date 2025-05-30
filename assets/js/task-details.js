/**
 * Task details handling
 * Provides functionality for viewing task details in a modal
 */

// Define the showTaskDetails function globally for use in HTML elements
function showTaskDetails(button) {
    // Get task data from button attributes
    const taskId = button.getAttribute('data-id');
    const taskName = button.getAttribute('data-name');
    const taskDesc = button.getAttribute('data-description');
    const startDate = button.getAttribute('data-start-date');
    const dueDate = button.getAttribute('data-due-date');
    const priority = button.getAttribute('data-priority');
    const projectName = button.getAttribute('data-project-name');
    const projectColor = button.getAttribute('data-project-color');
    
    // Set values in the modal
    document.getElementById('detail-task-name').textContent = taskName;
    
    // Handle description
    const descText = document.querySelector('.task-description-text');
    if (descText) {
        if (taskDesc && taskDesc.trim() !== '') {
            descText.textContent = taskDesc;
        } else {
            descText.textContent = 'No description provided';
        }
    }
    
    // Dates
    const startDateElem = document.getElementById('detail-start-date');
    if (startDateElem) {
        if (startDate) {
            startDateElem.textContent = formatDate(startDate);
        } else {
            startDateElem.textContent = 'Not set';
        }
    }
    
    const dueDateElem = document.getElementById('detail-due-date');
    if (dueDateElem) {
        if (dueDate) {
            dueDateElem.textContent = formatDate(dueDate);
            
            const today = new Date().toISOString().split('T')[0];
            if (dueDate < today) {
                dueDateElem.classList.add('text-danger');
            } else {
                dueDateElem.classList.remove('text-danger');
            }
        } else {
            dueDateElem.textContent = 'Not set';
        }
    }
    
    // Priority badge
    const priorityBadge = document.querySelector('#detail-priority .badge');
    const priorityIndicator = document.getElementById('detail-priority-indicator');
    
    let priorityText = '';
    let badgeClass = '';
    
    switch (parseInt(priority)) {
        case 0:
            priorityText = 'Low';
            badgeClass = 'bg-secondary';
            break;
        case 1:
            priorityText = 'Medium';
            badgeClass = 'bg-info';
            break;
        case 2:
            priorityText = 'High';
            badgeClass = 'bg-warning';
            break;
        case 3:
            priorityText = 'Urgent';
            badgeClass = 'bg-danger';
            break;
        default:
            priorityText = 'Not set';
            badgeClass = 'bg-secondary';
    }
    
    if (priorityBadge) {
        priorityBadge.className = 'badge ' + badgeClass;
        priorityBadge.textContent = priorityText;
    }
    
    if (priorityIndicator) {
        priorityIndicator.className = 'badge badge-pill ' + badgeClass;
        priorityIndicator.textContent = priorityText;
    }
    
    // Project info
    const projectElement = document.getElementById('detail-project');
    if (projectElement) {
        if (projectName) {
            if (projectColor) {
                projectElement.innerHTML = `<span class="task-project" style="background-color: ${projectColor}20;"><i class="fa fa-project-diagram" style="color: ${projectColor}"></i> ${projectName}</span>`;
            } else {
                projectElement.innerHTML = `<span class="task-project">${projectName}</span>`;
            }
        } else {
            projectElement.textContent = 'No Project';
        }
    }
    
    // Action buttons
    const editBtn = document.getElementById('detail-edit-btn');
    if (editBtn) {
        editBtn.setAttribute('data-id', taskId);
    }
    
    const completeBtn = document.getElementById('detail-complete-btn');
    if (completeBtn) {
        completeBtn.setAttribute('href', `../tasks/complete-task.php?id=${taskId}`);
    }
    
    // Show the modal
    const modalElement = document.getElementById('taskDetailsModal');
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
}

// Helper function to format dates
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { weekday: 'short', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

// Document ready function for jQuery
document.addEventListener('DOMContentLoaded', function() {
    // Use event delegation for the view task buttons
    $(document).on('click', '.view-task', function(e) {
        e.preventDefault();
        showTaskDetails(this);
    });
    
    // Set up the Edit button in the details modal
    $('#detail-edit-btn').on('click', function() {
        const taskId = $(this).data('id');
        
        // Close the details modal
        $('#taskDetailsModal').modal('hide');
        
        // Find and click the edit button for this task
        const editButton = $(`.edit-task[data-id="${taskId}"]`);
        if (editButton.length > 0) {
            editButton.trigger('click');
        }
    });
    
    // Set up the Complete button in the details modal
    $('#detail-complete-btn').on('click', function(e) {
        e.preventDefault();
        const href = $(this).attr('href');
    
        window.location.href = href;
    });
});
