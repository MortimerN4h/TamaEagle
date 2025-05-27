/**
 * DatabaseHelper class
 * This helper provides methods for database operations using Firebase Firestore
 */
class DatabaseHelper {
    constructor() {
        this.db = null;
        this._init();
    }

    /**
     * Initialize the database helper
     * Sets up Firebase Firestore
     */
    async _init() {
        try {
            // Check if Firebase is loaded
            if (typeof firebase !== 'undefined' && firebase.firestore) {
                console.log('Initializing Firestore database');
                this.db = firebase.firestore();
            } else {
                console.error('Firebase not detected. Make sure Firebase is properly initialized.');
                throw new Error('Firebase not available');
            }
        } catch (error) {
            console.error('Error initializing database:', error);
            throw error;
        }
    }

    /**
     * Update a task position
     * @param {string} taskId - Task ID
     * @param {string} sectionId - Section ID
     * @param {number} position - New position
     */
    async updateTaskPosition(taskId, sectionId, position) {
        try {
            // Update the task in Firestore
            await this.db.collection('tasks').doc(taskId).update({
                section_id: sectionId,
                position: position
            });
            return true;
        } catch (error) {
            console.error('Error updating task position in Firebase:', error);
            throw error;
        }
    }

    /**
     * Update a section position
     * @param {string} sectionId - Section ID
     * @param {number} position - New position
     */
    async updateSectionPosition(sectionId, position) {
        try {
            // Update the section in Firestore
            await this.db.collection('sections').doc(sectionId).update({
                position: position
            });
            return true;
        } catch (error) {
            console.error('Error updating section position in Firebase:', error);
            throw error;
        }
    }
}

// Initialize the helper and make it available globally
window.dbHelper = new DatabaseHelper();