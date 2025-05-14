// Project model class
class Project {
    constructor(id, name, color = '#5297ff') {
        this.id = id || Date.now().toString();
        this.name = name;
        this.color = color;
        this.createdAt = new Date();
    }

    setName(name) {
        this.name = name;
    }

    setColor(color) {
        this.color = color;
    }

    // For JSON serialization
    toJSON() {
        return {
            id: this.id,
            name: this.name,
            color: this.color,
            createdAt: this.createdAt
        };
    }

    // Create from JSON object
    static fromJSON(json) {
        const project = new Project(
            json.id,
            json.name,
            json.color
        );
        project.createdAt = new Date(json.createdAt);
        return project;
    }
}
