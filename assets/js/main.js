/**
 * TamaEagle Main JavaScript
 * Firebase version - Vanilla JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Toggle sidebar on mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            const sidebar = document.querySelector('.sidebar');
            const contentContainer = document.querySelector('.content-container');
            if (sidebar) sidebar.classList.toggle('show');
            if (contentContainer) contentContainer.classList.toggle('sidebar-open');
        });
    }

    // Task form date validation
    const taskForm = document.getElementById('taskForm');
    if (taskForm) {
        taskForm.addEventListener('submit', function(e) {
            // Get form data
            const startDate = document.getElementById('startDate').value;
            const dueDate = document.getElementById('dueDate').value;
            const today = new Date().toISOString().split('T')[0];
            
            // Basic validation
            if (startDate < today) {
                e.preventDefault();
                alert('Start date cannot be in the past');
                return false;
            }
            
            if (dueDate < startDate) {
                e.preventDefault();
                alert('Due date cannot be before start date');
                return false;
            }
        });
    }

    // Reset task form when opening the modal for a new task
    const addTaskBtn = document.getElementById('addTaskBtn');
    if (addTaskBtn) {
        addTaskBtn.addEventListener('click', function () {
            const taskModalLabel = document.getElementById('taskModalLabel');
            const taskForm = document.getElementById('taskForm');
            const taskId = document.getElementById('taskId');
            const taskName = document.getElementById('taskName');
            const taskDescription = document.getElementById('taskDescription');
            const startDate = document.getElementById('startDate');
            const dueDate = document.getElementById('dueDate');
            const priority = document.getElementById('priority');
            const project = document.getElementById('project');
            const sectionId = document.getElementById('sectionId');

            if (taskModalLabel) taskModalLabel.textContent = 'Add New Task';
            if (taskForm) taskForm.setAttribute('action', '../tasks/add-task-firebase.php');
            if (taskId) taskId.value = '';
            if (taskName) taskName.value = '';
            if (taskDescription) taskDescription.value = '';
            if (startDate) startDate.value = new Date().toISOString().split('T')[0];
            if (dueDate) dueDate.value = new Date().toISOString().split('T')[0];
            if (priority) priority.value = '0';
            if (project) project.value = '';
            if (sectionId) sectionId.value = '';
        });
    }

    // Date navigation buttons for upcoming view
    const datePrevBtns = document.querySelectorAll('.date-nav-prev');
    datePrevBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const currentWeekStart = this.getAttribute('data-current');
            window.location.href = 'upcoming.php?week=' + currentWeekStart;
        });
    });

    const dateNextBtns = document.querySelectorAll('.date-nav-next');
    dateNextBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const nextWeekStart = this.getAttribute('data-next');
            window.location.href = 'upcoming.php?week=' + nextWeekStart;
        });
    });

    const dateTodayBtns = document.querySelectorAll('.date-nav-today');
    dateTodayBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            window.location.href = 'upcoming.php';
        });
    });

    // Add section handler
    const addSectionBtns = document.querySelectorAll('.add-section');
    addSectionBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const projectId = this.getAttribute('data-project-id');
            const sectionName = prompt('Enter section name:');
            if (sectionName) {
                window.location.href = '../sections/add-section.php?project_id=' + projectId + '&name=' + encodeURIComponent(sectionName);
            }
        });
    });

    // Note: Drag and drop functionality removed for Firebase version
    // If needed, implement with modern drag and drop API instead of jQuery UI
    
    console.log('TamaEagle main.js loaded (Firebase version)');
});