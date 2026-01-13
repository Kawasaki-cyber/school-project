/**
 * Specialites CRUD - Vanilla JavaScript using Fetch API
 * Handles list, add, and delete operations for specialities
 */

class SpecialitesCRUD {
    constructor() {
        this.apiUrl = '/api/specialites';
        this.init();
    }

    init() {
        this.loadSpecialites();
        this.setupEventListeners();
    }

    setupEventListeners() {
        const form = document.getElementById('specialite-form');
        if (form) {
            form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
    }

    /**
     * Load all specialites from API
     */
    async loadSpecialites() {
        try {
            const response = await fetch(this.apiUrl);
            if (!response.ok) throw new Error('Failed to load specialites');
            
            const specialites = await response.json();
            this.renderSpecialitesTable(specialites);
        } catch (error) {
            console.error('Error loading specialites:', error);
            this.showError('Failed to load specialities');
        }
    }

    /**
     * Render specialites table dynamically
     */
    renderSpecialitesTable(specialites) {
        const tbody = document.getElementById('specialites-table-body');
        if (!tbody) return;

        tbody.innerHTML = '';

        specialites.forEach(specialite => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${specialite.id}</td>
                <td>${specialite.nom}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="specialitesCRUD.deleteSpecialite(${specialite.id})">
                        Delete
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    /**
     * Handle form submission
     */
    async handleSubmit(e) {
        e.preventDefault();
        
        const nom = document.getElementById('specialite-nom').value;

        if (!nom) {
            this.showError('Please enter a speciality name');
            return;
        }

        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    nom: nom
                })
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.error || 'Failed to create speciality');
            }

            this.showSuccess('Speciality created successfully');
            document.getElementById('specialite-form').reset();
            this.loadSpecialites();
        } catch (error) {
            console.error('Error creating specialite:', error);
            this.showError(error.message);
        }
    }

    /**
     * Delete a specialite
     */
    async deleteSpecialite(id) {
        if (!confirm('Are you sure you want to delete this speciality?')) {
            return;
        }

        try {
            const response = await fetch(`${this.apiUrl}/${id}`, {
                method: 'DELETE'
            });

            if (!response.ok) throw new Error('Failed to delete speciality');

            this.showSuccess('Speciality deleted successfully');
            this.loadSpecialites();
        } catch (error) {
            console.error('Error deleting specialite:', error);
            this.showError('Failed to delete speciality');
        }
    }

    showSuccess(message) {
        alert('Success: ' + message);
    }

    showError(message) {
        alert('Error: ' + message);
    }
}

// Initialize when DOM is ready
let specialitesCRUD;
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        specialitesCRUD = new SpecialitesCRUD();
    });
} else {
    specialitesCRUD = new SpecialitesCRUD();
}

