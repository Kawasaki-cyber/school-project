/**
 * Patients CRUD - Vanilla JavaScript using Fetch API
 * Handles list, add, and delete operations for patients
 */

class PatientsCRUD {
    constructor() {
        this.apiUrl = '/api/patients';
        this.init();
    }

    init() {
        this.loadPatients();
        this.setupEventListeners();
    }

    setupEventListeners() {
        const form = document.getElementById('patient-form');
        if (form) {
            form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
    }

    /**
     * Load all patients from API
     */
    async loadPatients() {
        try {
            const response = await fetch(this.apiUrl);
            if (!response.ok) throw new Error('Failed to load patients');
            
            const patients = await response.json();
            this.renderPatientsTable(patients);
        } catch (error) {
            console.error('Error loading patients:', error);
            this.showError('Failed to load patients');
        }
    }

    /**
     * Render patients table dynamically
     */
    renderPatientsTable(patients) {
        const tbody = document.getElementById('patients-table-body');
        if (!tbody) return;

        tbody.innerHTML = '';

        patients.forEach(patient => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${patient.id}</td>
                <td>${patient.nom}</td>
                <td>${patient.telephone || 'N/A'}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="patientsCRUD.deletePatient(${patient.id})">
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
        
        const nom = document.getElementById('patient-nom').value;
        const telephone = document.getElementById('patient-telephone').value;

        if (!nom || !telephone) {
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
                    telephone: telephone
                })
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.error || 'Failed to create patient');
            }

            this.showSuccess('Patient created successfully');
            document.getElementById('patient-form').reset();
            this.loadPatients();
        } catch (error) {
            console.error('Error creating patient:', error);
            this.showError(error.message);
        }
    }

    /**
     * Delete a patient
     */
    async deletePatient(id) {
        if (!confirm('Are you sure you want to delete this patient?')) {
            return;
        }

        try {
            const response = await fetch(`${this.apiUrl}/${id}`, {
                method: 'DELETE'
            });

            if (!response.ok) throw new Error('Failed to delete patient');

            this.showSuccess('Patient deleted successfully');
            this.loadPatients();
        } catch (error) {
            console.error('Error deleting patient:', error);
            this.showError('Failed to delete patient');
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
let patientsCRUD;
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        patientsCRUD = new PatientsCRUD();
    });
} else {
    patientsCRUD = new PatientsCRUD();
}

