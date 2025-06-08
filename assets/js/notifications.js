/**
 * Task notification system
 * Shows notifications for tasks due soon and overdue tasks
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check if notification elements exist
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationContainer = document.getElementById('task-notifications');
    const notificationBadge = document.getElementById('notification-badge');
    
    if (notificationDropdown) {
        // Load notifications when dropdown is clicked
        notificationDropdown.addEventListener('click', loadNotifications);
    }
    
    // Also load notifications on page load
    loadNotifications();
    
    // Set up auto-refresh of notifications every minute
    setInterval(loadNotifications, 60000);
});

/**
 * Load task notifications (overdue and upcoming tasks)
 */
function loadNotifications() {
    // Show loading indicator
    const notificationContainer = document.getElementById('task-notifications');
    
    if (!notificationContainer) {
        return;
    }
    
    notificationContainer.innerHTML = '<div class="p-3 text-center"><i class="fa fa-spinner fa-spin"></i>Loading notifications...</div>';
      // Use base URL from meta tag if available, otherwise construct from path
    const baseUrl = document.querySelector('meta[name="base-url"]')?.getAttribute('content');
    let finalUrl = window.location.origin + '/api/get-notifications.php';
    
    console.log("Fetching notifications from:", finalUrl);
      fetch(finalUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Network response was not ok (Status: ${response.status})`);
            }
            return response.json();
        })
        .then(data => {
            if (data && data.error) {
                throw new Error(data.error);
            }
            displayNotifications(data);
        })
        .catch(error => {
            console.error("Notification error:", error);
            notificationContainer.innerHTML = `<div class="p-3 text-center text-danger">Error loading notifications<br><small>${error.message}</small></div>`;
        });
}

/**
 * Display notifications in the dropdown
 */
function displayNotifications(data) {
    const notificationContainer = document.getElementById('task-notifications');
    const notificationBadge = document.getElementById('notification-badge');
    const notificationCount = document.getElementById('notification-count');
    
    // Clear current content
    notificationContainer.innerHTML = '';
    
    // Count total notifications
    const totalTasks = data.overdue.length + data.upcoming.length;
    
    // Update notification badge
    if (totalTasks > 0) {
        notificationCount.textContent = totalTasks;
        notificationBadge.classList.remove('d-none');
    } else {
        notificationBadge.classList.add('d-none');
    }
    
    // Header for the notification dropdown
    const header = document.createElement('div');
    header.className = 'dropdown-header bg-light py-2';
    header.innerHTML = '<h6 class="m-0">Tasks Notification</h6>';
    notificationContainer.appendChild(header);
    
    // If there are overdue tasks
    if (data.overdue.length > 0) {
        const overdueHeader = document.createElement('div');
        overdueHeader.className = 'dropdown-header bg-danger text-white py-2';
        overdueHeader.innerHTML = `<small class="m-0"><i class="fa fa-exclamation-circle"></i> Overdue Tasks (${data.overdue.length})</small>`;
        notificationContainer.appendChild(overdueHeader);
        
        data.overdue.forEach(task => {
            addTaskItem(task, notificationContainer, true);
        });
    }
    
    // If there are upcoming tasks
    if (data.upcoming.length > 0) {
        const upcomingHeader = document.createElement('div');
        upcomingHeader.className = 'dropdown-header bg-info text-white py-2';
        upcomingHeader.innerHTML = `<small class="m-0"><i class="fa fa-calendar"></i> Due Soon (${data.upcoming.length})</small>`;
        notificationContainer.appendChild(upcomingHeader);
        
        data.upcoming.forEach(task => {
            addTaskItem(task, notificationContainer, false);
        });
    }
    
    // If no tasks
    if (totalTasks === 0) {
        const noTasks = document.createElement('div');
        noTasks.className = 'dropdown-item text-center py-3';
        noTasks.innerHTML = '<i class="fa fa-check-circle text-success"></i> No urgent tasks';
        notificationContainer.appendChild(noTasks);
    }
    
    // Footer with link to today's tasks
    const footer = document.createElement('div');
    footer.className = 'dropdown-item text-center border-top py-2';
    footer.innerHTML = '<a href="../views/today.php" class="text-decoration-none text-primary">View Today\'s Tasks</a>';
    notificationContainer.appendChild(footer);
}

/**
 * Add a task item to the notification dropdown
 */
function addTaskItem(task, container, isOverdue) {
    const daysDiff = getDaysDifference(task.due_date);
    const taskItem = document.createElement('a');
    taskItem.href = task.project_id
        ? `../projects/project.php?id=${task.project_id}`
        : '../views/today.php';
    taskItem.className = `dropdown-item py-2 border-bottom ${isOverdue ? 'bg-danger-subtle' : ''}`;
    
    // Priority indicator
    const priorityClass = getPriorityClass(task.priority);
    const priorityLabel = getPriorityLabel(task.priority);
    
    // Format due date label
    let dateLabel = '';
    if (isOverdue) {
        dateLabel = `<span class="text-danger">${Math.abs(daysDiff)} day${Math.abs(daysDiff) !== 1 ? 's' : ''} overdue</span>`;
    } else {
        if (daysDiff === 0) {
            dateLabel = '<span class="text-warning">Today</span>';
        } else {
            dateLabel = `<span class="text-primary">In ${daysDiff} day${daysDiff !== 1 ? 's' : ''}</span>`;
        }
    }
    
    // Project label if available
    const projectLabel = task.project_name
        ? `<span class="badge" style="background-color: ${task.project_color || '#6c757d'};">${task.project_name}</span>`
        : '';
    
    // Task content
    taskItem.innerHTML = `
        <div class="d-flex w-100 justify-content-between align-items-center">
            <h6 class="mb-1 text-truncate" style="max-width: 200px;">${task.name}</h6>
            <span class="badge ${priorityClass}">${priorityLabel}</span>
        </div>
        <div class="d-flex w-100 justify-content-between align-items-center mb-2">
            <small>${dateLabel}</small>
            <small>${projectLabel}</small>
        </div>
    `;
    
    container.appendChild(taskItem);
}

/**
 * Calculate the difference in days from today
 */
function getDaysDifference(dateString) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    const targetDate = new Date(dateString);
    targetDate.setHours(0, 0, 0, 0);
    
    const diffTime = targetDate - today;
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
}

/**
 * Get priority CSS class based on priority level
 */
function getPriorityClass(priority) {
    switch (parseInt(priority)) {
        case 3: return 'bg-outline-danger text-dark';
        case 2: return 'bg-outline-warning text-dark';
        case 1: return 'bg-outline-info text-dark';
        default: return 'bg-outline-secondary text-dark';
    }
}

/**
 * Get priority text label based on priority level
 */
function getPriorityLabel(priority) {
    switch (parseInt(priority)) {
        case 3: return 'Urgent';
        case 2: return 'High';
        case 1: return 'Medium';
        default: return 'Low';
    }
}
