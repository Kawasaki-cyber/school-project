/**
 * RendezVous CRUD - Vanilla JavaScript using Fetch API
 * Handles list, add, and delete operations for appointments
 */

class RendezVousCRUD {
    constructor() {
        this.apiUrl = '/api/rdv';
        this.medecinsApiUrl = '/api/medecins';
        this.patientsApiUrl = '/api/patients';
        this.init();
    }

    init() {
        this.loadRendezVous();
        this.loadMedecins();
        this.loadPatients();
        this.setupEventListeners();
    }

    setupEventListeners() {
        const form = document.getElementById('rendezvous-form');
        if (form) {
            form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
    }

    /**
     * Load all rendez-vous from API
     */
    async loadRendezVous() {
        try {
            const response = await fetch(this.apiUrl);
            if (!response.ok) throw new Error('Failed to load appointments');
            
            const rendezvous = await response.json();
            this.renderRendezVousTable(rendezvous);
        } catch (error) {
            console.error('Error loading rendez-vous:', error);
            this.showError('Failed to load appointments');
        }
    }

    /**
     * Load medecins for dropdown
     */
    async loadMedecins() {
        try {
            const response = await fetch(this.medecinsApiUrl);
            if (!response.ok) throw new Error('Failed to load doctors');
            
            const medecins = await response.json();
            this.renderMedecinsDropdown(medecins);
        } catch (error) {
            console.error('Error loading medecins:', error);
        }
    }

    /**
     * Load patients for dropdown
     */
    async loadPatients() {
        try {
            const response = await fetch(this.patientsApiUrl);
            if (!response.ok) throw new Error('Failed to load patients');
            
            const patients = await response.json();
            this.renderPatientsDropdown(patients);
        } catch (error) {
            console.error('Error loading patients:', error);
        }
    }

    /**
     * Render rendez-vous table dynamically
     */
    renderRendezVousTable(rendezvous) {
        const tbody = document.getElementById('rendezvous-table-body');
        if (!tbody) return;

        tbody.innerHTML = '';

        rendezvous.forEach(rdv => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${rdv.id}</td>
                <td>${rdv.medecin_nom || 'N/A'}</td>
                <td>${rdv.patient_nom || 'N/A'}</td>
                <td>${rdv.date || 'N/A'}</td>
                <td>${rdv.heure || 'N/A'}</td>
                <td>${rdv.statut || 'N/A'}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="rendezvousCRUD.deleteRendezVous(${rdv.id})">
                        Delete
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    /**
     * Render medecins dropdown
     */
    renderMedecinsDropdown(medecins) {
        const select = document.getElementById('rendezvous-medecin');
        if (!select) return;

        select.innerHTML = '<option value="">Select Doctor</option>';
        medecins.forEach(medecin => {
            const option = document.createElement('option');
            option.value = medecin.id;
            option.textContent = medecin.nom;
            select.appendChild(option);
        });
    }

    /**
     * Render patients dropdown
     */
    renderPatientsDropdown(patients) {
        const select = document.getElementById('rendezvous-patient');
        if (!select) return;

        select.innerHTML = '<option value="">Select Patient</option>';
        patients.forEach(patient => {
            const option = document.createElement('option');
            option.value = patient.id;
            option.textContent = patient.nom;
            select.appendChild(option);
        });
    }

    /**
     * Handle form submission
     */
    async handleSubmit(e) {
        e.preventDefault();
        
        const medecinId = document.getElementById('rendezvous-medecin').value;
        const patientId = document.getElementById('rendezvous-patient').value;
        const date = document.getElementById('rendezvous-date').value;
        const heure = document.getElementById('rendezvous-heure').value;
        const statut = document.getElementById('rendezvous-statut').value;

        if (!medecinId || !patientId || !date || !heure) {
            this.showError('Please fill all required fields');
            return;
        }

        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    medecin_id: parseInt(medecinId),
                    patient_id: parseInt(patientId),
                    date: date,
                    heure: heure,
                    statut: statut || 'programme'
                })
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.error || 'Failed to create appointment');
            }

            this.showSuccess('Appointment created successfully');
            document.getElementById('rendezvous-form').reset();
            this.loadRendezVous();
        } catch (error) {
            console.error('Error creating rendez-vous:', error);
            this.showError(error.message);
        }
    }

    /**
     * Delete a rendez-vous
     */
    async deleteRendezVous(id) {
        if (!confirm('Are you sure you want to delete this appointment?')) {
            return;
        }

        try {
            const response = await fetch(`${this.apiUrl}/${id}`, {
                method: 'DELETE'
            });

            if (!response.ok) throw new Error('Failed to delete appointment');

            this.showSuccess('Appointment deleted successfully');
            this.loadRendezVous();
        } catch (error) {
            console.error('Error deleting rendez-vous:', error);
            this.showError('Failed to delete appointment');
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
let rendezvousCRUD;
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        rendezvousCRUD = new RendezVousCRUD();
    });
} else {
    rendezvousCRUD = new RendezVousCRUD();
}

