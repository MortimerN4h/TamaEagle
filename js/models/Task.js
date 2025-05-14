// Task model class
class Task {
    constructor(id, name, description = '', projectId, startDate = null, deadline = null, priority = 'medium', completed = false) {
        this.id = id || Date.now().toString();
        this.name = name;
        this.description = description;
        this.projectId = projectId;
        this.startDate = startDate;
        this.deadline = deadline;
        this.priority = priority; // 'low', 'medium', 'high', 'urgent'
        this.completed = completed;
        this.createdAt = new Date();
    }

    toggleComplete() {
        this.completed = !this.completed;
        return this.completed;
    }

    setName(name) {
        this.name = name;
    }

    setDescription(description) {
        this.description = description;
    }

    setStartDate(date) {
        this.startDate = date;
    }

    setDeadline(date) {
        this.deadline = date;
    }

    setPriority(priority) {
        this.priority = priority;
    }

    setPriority(priority) {
        if (['low', 'medium', 'high', 'urgent'].includes(priority.toLowerCase())) {
            this.priority = priority.toLowerCase();
        }
    }

    setProjectId(projectId) {
        this.projectId = projectId;
    }

    isOverdue() {
        if (!this.deadline) return false;
        const deadlineDate = new Date(this.deadline);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return deadlineDate < today && !this.completed;
    }

    isDueToday() {
        if (!this.deadline) return false;
        const deadlineDate = new Date(this.deadline);
        const today = new Date();
        return deadlineDate.getDate() === today.getDate() && 
               deadlineDate.getMonth() === today.getMonth() && 
               deadlineDate.getFullYear() === today.getFullYear();
    }

    // Calculates and returns remaining time in a human-readable format
    getRemainingTime() {
        if (!this.deadline) return 'No deadline';
        if (this.completed) return 'Completed';

        const now = new Date();
        const deadline = new Date(this.deadline);
        const diffTime = deadline - now;
        
        if (diffTime < 0) {
            return 'Overdue';
        }

        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
        const diffHours = Math.floor((diffTime % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        
        if (diffDays > 0) {
            return diffDays === 1 ? '1 day left' : `${diffDays} days left`;
        } else if (diffHours > 0) {
            return diffHours === 1 ? '1 hour left' : `${diffHours} hours left`;
        } else {
            const diffMinutes = Math.floor((diffTime % (1000 * 60 * 60)) / (1000 * 60));
            return diffMinutes <= 0 ? 'Less than a minute' : `${diffMinutes} minutes left`;
        }
    }

    // For JSON serialization
    toJSON() {
        return {
            id: this.id,
            name: this.name,
            description: this.description,
            projectId: this.projectId,
            startDate: this.startDate,
            deadline: this.deadline,
            priority: this.priority,
            completed: this.completed,
            createdAt: this.createdAt
        };
    }

    // Create from JSON object
    static fromJSON(json) {
        const task = new Task(
            json.id,
            json.name,
            json.description,
            json.projectId,
            json.startDate,
            json.deadline,
            json.priority,
            json.completed
        );
        task.createdAt = new Date(json.createdAt);
        return task;
    }
}
