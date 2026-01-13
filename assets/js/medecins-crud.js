/**
 * Medecins CRUD - Vanilla JavaScript using Fetch API
 * Handles list, add, and delete operations for doctors
 */

class MedecinsCRUD {
    constructor() {
        this.apiUrl = '/api/medecins';
        this.specialitesApiUrl = '/api/specialites';
        this.init();
    }

    init() {
        this.loadMedecins();
        this.loadSpecialites();
        this.setupEventListeners();
    }

    setupEventListeners() {
        const form = document.getElementById('medecin-form');
        if (form) {
            form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
    }

    /**
     * Load all medecins from API
     */
    async loadMedecins() {
        try {
            const response = await fetch(this.apiUrl);
            if (!response.ok) throw new Error('Failed to load medecins');
            
            const medecins = await response.json();
            this.renderMedecinsTable(medecins);
        } catch (error) {
            console.error('Error loading medecins:', error);
            this.showError('Failed to load doctors');
        }
    }

    /**
     * Load all specialites for dropdown
     */
    async loadSpecialites() {
        try {
            const response = await fetch(this.specialitesApiUrl);
            if (!response.ok) throw new Error('Failed to load specialites');
            
            const specialites = await response.json();
            this.renderSpecialitesDropdown(specialites);
        } catch (error) {
            console.error('Error loading specialites:', error);
        }
    }

    /**
     * Render medecins table dynamically
     */
    renderMedecinsTable(medecins) {
        const tbody = document.getElementById('medecins-table-body');
        if (!tbody) return;

        tbody.innerHTML = '';

        medecins.forEach(medecin => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${medecin.id}</td>
                <td>${medecin.nom}</td>
                <td>${medecin.specialite || 'N/A'}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="medecinsCRUD.deleteMedecin(${medecin.id})">
                        Delete
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    /**
     * Render specialites dropdown
     */
    renderSpecialitesDropdown(specialites) {
        const select = document.getElementById('medecin-specialite');
        if (!select) return;

        select.innerHTML = '<option value="">Select Specialite</option>';
        specialites.forEach(specialite => {
            const option = document.createElement('option');
            option.value = specialite.id;
            option.textContent = specialite.nom;
            select.appendChild(option);
        });
    }

    /**
     * Handle form submission
     */
    async handleSubmit(e) {
        e.preventDefault();
        
        const nom = document.getElementById('medecin-nom').value;
        const specialiteId = document.getElementById('medecin-specialite').value;

        if (!nom || !specialiteId) {
            this.showError('Please fill all fields');
            return;
        }

        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    nom: nom,
                    specialite_id: parseInt(specialiteId)
                })
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.error || 'Failed to create medecin');
            }

            this.showSuccess('Doctor created successfully');
            document.getElementById('medecin-form').reset();
            this.loadMedecins();
        } catch (error) {
            console.error('Error creating medecin:', error);
            this.showError(error.message);
        }
    }

    /**
     * Delete a medecin
     */
    async deleteMedecin(id) {
        if (!confirm('Are you sure you want to delete this doctor?')) {
            return;
        }

        try {
            const response = await fetch(`${this.apiUrl}/${id}`, {
                method: 'DELETE'
            });

            if (!response.ok) throw new Error('Failed to delete medecin');

            this.showSuccess('Doctor deleted successfully');
            this.loadMedecins();
        } catch (error) {
            console.error('Error deleting medecin:', error);
            this.showError('Failed to delete doctor');
        }
    }

    showSuccess(message) {
        // Simple alert for now - can be replaced with toast notification
        alert('Success: ' + message);
    }

    showError(message) {
        alert('Error: ' + message);
    }
}

// Initialize when DOM is ready
let medecinsCRUD;
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        medecinsCRUD = new MedecinsCRUD();
    });
} else {
    medecinsCRUD = new MedecinsCRUD();
}

