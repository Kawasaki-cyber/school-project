/**
 * Dashboard Statistics - Vanilla JavaScript using Fetch API and Chart.js
 * Displays 5 statistics charts as required:
 * 1. Appointments by doctor
 * 2. Appointments by status
 * 3. Appointments by date
 * 4. Patients by speciality
 * 5. Appointments per week
 */

class DashboardStats {
    constructor() {
        this.apiUrl = '/api/stats';
        this.charts = {};
        this.init();
    }

    /**
     * Initialize dashboard - load statistics and render charts
     */
    async init() {
        try {
            const stats = await this.loadStats();
            this.renderCharts(stats);
        } catch (error) {
            console.error('Error loading dashboard statistics:', error);
            this.showError('Failed to load statistics');
        }
    }

    /**
     * Load statistics from API
     */
    async loadStats() {
        const response = await fetch(this.apiUrl);
        if (!response.ok) throw new Error('Failed to load statistics');
        return await response.json();
    }

    /**
     * Render all charts
     */
    renderCharts(stats) {
        this.renderChartByDoctor(stats.rdvByDoctor || []);
        this.renderChartByStatus(stats.rdvByStatus || []);
        this.renderChartByDate(stats.rdvByDate || []);
        this.renderChartBySpecialite(stats.patientsBySpecialite || []);
        this.renderChartPerWeek(stats.rdvPerWeek || []);
    }

    /**
     * Chart 1: Appointments by Doctor
     */
    renderChartByDoctor(data) {
        const ctx = document.getElementById('chartByDoctor');
        if (!ctx) return;

        const labels = data.map(item => item.medecin_nom || 'Unknown');
        const values = data.map(item => parseInt(item.total) || 0);

        this.charts.byDoctor = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Number of Appointments',
                    data: values,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    /**
     * Chart 2: Appointments by Status
     */
    renderChartByStatus(data) {
        const ctx = document.getElementById('chartByStatus');
        if (!ctx) return;

        const labels = data.map(item => item.statut || 'Unknown');
        const values = data.map(item => parseInt(item.total) || 0);

        const colors = [
            'rgba(255, 99, 132, 0.6)',
            'rgba(54, 162, 235, 0.6)',
            'rgba(255, 206, 86, 0.6)',
            'rgba(75, 192, 192, 0.6)',
            'rgba(153, 102, 255, 0.6)'
        ];

        this.charts.byStatus = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Appointments',
                    data: values,
                    backgroundColor: colors.slice(0, labels.length),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    /**
     * Chart 3: Appointments by Date
     */
    renderChartByDate(data) {
        const ctx = document.getElementById('chartByDate');
        if (!ctx) return;

        // Sort by date
        data.sort((a, b) => new Date(a.date) - new Date(b.date));

        const labels = data.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' });
        });
        const values = data.map(item => parseInt(item.total) || 0);

        this.charts.byDate = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Appointments',
                    data: values,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    /**
     * Chart 4: Patients by Speciality
     */
    renderChartBySpecialite(data) {
        const ctx = document.getElementById('chartBySpecialite');
        if (!ctx) return;

        const labels = data.map(item => item.specialite_nom || 'Unknown');
        const values = data.map(item => parseInt(item.total) || 0);

        this.charts.bySpecialite = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Patients',
                    data: values,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 159, 64, 0.6)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    /**
     * Chart 5: Appointments per Week
     */
    renderChartPerWeek(data) {
        const ctx = document.getElementById('chartPerWeek');
        if (!ctx) return;

        // Sort by week
        data.sort((a, b) => parseInt(a.week) - parseInt(b.week));

        const labels = data.map(item => {
            // Convert week number to readable format
            const year = Math.floor(item.week / 100);
            const week = item.week % 100;
            return `Week ${week}/${year}`;
        });
        const values = data.map(item => parseInt(item.total) || 0);

        this.charts.perWeek = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Appointments per Week',
                    data: values,
                    backgroundColor: 'rgba(153, 102, 255, 0.6)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    showError(message) {
        console.error(message);
        // Could show a toast notification here
    }
}

// Initialize when DOM is ready
let dashboardStats;
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        dashboardStats = new DashboardStats();
    });
} else {
    dashboardStats = new DashboardStats();
}

