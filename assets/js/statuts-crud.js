/**
 * RendezVous Statuses Management - Vanilla JavaScript
 * Handles display and management of appointment statuses
 * Note: Statuses are predefined values, not a separate entity
 */

class StatutsCRUD {
    constructor() {
        this.statuses = [
            { value: 'programme', label: 'Programmé' },
            { value: 'confirme', label: 'Confirmé' },
            { value: 'annule', label: 'Annulé' },
            { value: 'termine', label: 'Terminé' },
            { value: 'absent', label: 'Absent' }
        ];
        this.init();
    }

    init() {
        this.renderStatusesTable();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Statuses are predefined, so we just display them
        // In a real scenario, you might want to update rendez-vous statuses
    }

    /**
     * Render statuses table
     */
    renderStatusesTable() {
        const tbody = document.getElementById('statuts-table-body');
        if (!tbody) return;

        tbody.innerHTML = '';

        this.statuses.forEach(status => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${status.value}</td>
                <td>${status.label}</td>
                <td>
                    <span class="badge bg-secondary">Predefined</span>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    /**
     * Get all available statuses
     */
    getStatuses() {
        return this.statuses;
    }

    /**
     * Render status dropdown
     */
    renderStatusDropdown(selectId) {
        const select = document.getElementById(selectId);
        if (!select) return;

        select.innerHTML = '<option value="">Select Status</option>';
        this.statuses.forEach(status => {
            const option = document.createElement('option');
            option.value = status.value;
            option.textContent = status.label;
            select.appendChild(option);
        });
    }
}

// Initialize when DOM is ready
let statutsCRUD;
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        statutsCRUD = new StatutsCRUD();
    });
} else {
    statutsCRUD = new StatutsCRUD();
}

