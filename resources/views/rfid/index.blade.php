@extends('layouts.dashboard')

@section('title', 'RFID Management')

@section('content')
<div class="container-fluid py-4" x-data="rfidManagementData()">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 text-dark fw-bold">RFID Management</h4>
                            <p class="text-muted mb-0">Kelola kartu RFID dan assignment ke karyawan</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button @click="refreshData()" 
                                    class="btn btn-outline-secondary">
                                <i class="fas fa-sync-alt me-2"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <div class="icon-shape bg-primary text-white rounded-circle">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                            </div>
                            <h5 class="mb-1" x-text="statistics.total_cards || 0"></h5>
                            <p class="text-muted mb-0 small">Total Cards</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <div class="icon-shape bg-success text-white rounded-circle">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                            <h5 class="mb-1" x-text="statistics.available_cards || 0"></h5>
                            <p class="text-muted mb-0 small">Available</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <div class="icon-shape bg-info text-white rounded-circle">
                                    <i class="fas fa-user-check"></i>
                                </div>
                            </div>
                            <h5 class="mb-1" x-text="statistics.assigned_cards || 0"></h5>
                            <p class="text-muted mb-0 small">Assigned</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <div class="icon-shape bg-warning text-white rounded-circle">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                            </div>
                            <h5 class="mb-1" x-text="statistics.damaged_cards || 0"></h5>
                            <p class="text-muted mb-0 small">Damaged</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <div class="icon-shape bg-danger text-white rounded-circle">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                            </div>
                            <h5 class="mb-1" x-text="statistics.lost_cards || 0"></h5>
                            <p class="text-muted mb-0 small">Lost</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <div class="icon-shape bg-secondary text-white rounded-circle">
                                    <i class="fas fa-pause-circle"></i>
                                </div>
                            </div>
                            <h5 class="mb-1" x-text="statistics.inactive_cards || 0"></h5>
                            <p class="text-muted mb-0 small">Inactive</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Search</label>
                            <input type="text" 
                                   class="form-control" 
                                   placeholder="Search by card number, type, or employee..."
                                   x-model="filters.search"
                                   @input.debounce.500ms="loadRfidData()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-dark">Status</label>
                            <select class="form-select" 
                                    x-model="filters.status"
                                    @change="loadRfidData()">
                                <option value="">All Status</option>
                                <option value="AVAILABLE">Available</option>
                                <option value="ASSIGNED">Assigned</option>
                                <option value="DAMAGED">Damaged</option>
                                <option value="LOST">Lost</option>
                                <option value="INACTIVE">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-dark">Assignment</label>
                            <select class="form-select" 
                                    x-model="filters.assigned"
                                    @change="loadRfidData()">
                                <option value="">All Cards</option>
                                <option value="true">Assigned to Employee</option>
                                <option value="false">Unassigned</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button @click="clearFilters()" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-1"></i>Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RFID Cards Table -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark">RFID Cards List</h6>
                        <div class="d-flex gap-2">
                            <!-- Bulk Actions -->
                            <div class="dropdown" x-show="selectedCards.length > 0">
                                <button class="btn btn-outline-primary dropdown-toggle btn-sm" 
                                        type="button" 
                                        data-bs-toggle="dropdown">
                                    <i class="fas fa-tasks me-1"></i>
                                    Bulk Actions (<span x-text="selectedCards.length"></span>)
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" @click="bulkChangeStatus('AVAILABLE')">
                                        <i class="fas fa-check-circle text-success me-2"></i>Set Available
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" @click="bulkChangeStatus('DAMAGED')">
                                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>Set Damaged
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" @click="bulkChangeStatus('LOST')">
                                        <i class="fas fa-times-circle text-danger me-2"></i>Set Lost
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" @click="bulkChangeStatus('INACTIVE')">
                                        <i class="fas fa-pause-circle text-secondary me-2"></i>Set Inactive
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" @click="bulkDelete()">
                                        <i class="fas fa-trash me-2"></i>Delete Selected
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Loading State -->
                    <div x-show="loading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading RFID data...</p>
                    </div>

                    <!-- Table -->
                    <div x-show="!loading" class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   @change="toggleAllCards($event.target.checked)">
                                        </div>
                                    </th>
                                    <th class="border-0">Card Number</th>
                                    <th class="border-0">Type</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0">Assigned Employee</th>
                                    <th class="border-0">Assigned Date</th>
                                    <th class="border-0">Notes</th>
                                    <th class="border-0">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="card in rfidCards" :key="card.id">
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       :value="card.id"
                                                       x-model="selectedCards">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <i class="fas fa-credit-card text-primary"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-bold" x-text="card.card_number"></span>
                                                    <br>
                                                    <small class="text-muted" x-text="formatDate(card.created_at)"></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark" x-text="card.card_type || 'MIFARE'"></span>
                                        </td>
                                        <td>
                                            <span class="badge" 
                                                  :class="getStatusBadgeClass(card.status)" 
                                                  x-text="card.status"></span>
                                        </td>
                                        <td>
                                            <div x-show="card.karyawan">
                                                <strong x-text="card.karyawan?.nama"></strong>
                                                <br>
                                                <small class="text-muted">
                                                    NIP: <span x-text="card.karyawan?.nip"></span>
                                                    <br>
                                                    <span x-text="card.karyawan?.jabatan"></span> - <span x-text="card.karyawan?.departemen"></span>
                                                </small>
                                            </div>
                                            <div x-show="!card.karyawan" class="text-muted">
                                                <em>Not assigned</em>
                                            </div>
                                        </td>
                                        <td>
                                            <span x-show="card.assigned_at" x-text="formatDate(card.assigned_at)"></span>
                                            <span x-show="!card.assigned_at" class="text-muted">-</span>
                                        </td>
                                        <td>
                                            <span x-show="card.notes" x-text="truncateText(card.notes, 50)"></span>
                                            <span x-show="!card.notes" class="text-muted">-</span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" 
                                                        class="btn btn-outline-info btn-sm" 
                                                        @click="editCard(card)"
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-outline-danger btn-sm" 
                                                        @click="confirmDelete(card)"
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="rfidCards.length === 0 && !loading">
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-credit-card fa-3x mb-3 opacity-50"></i>
                                            <p>No RFID cards found</p>
                                            <p class="small">Cards will appear here automatically when scanned by NodeMCU</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div x-show="pagination.last_page > 1" class="card-footer bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Showing <span x-text="pagination.from"></span> to <span x-text="pagination.to"></span> 
                                of <span x-text="pagination.total"></span> results
                            </div>
                            <nav>
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item" :class="{ 'disabled': pagination.current_page <= 1 }">
                                        <button class="page-link" @click="changePage(pagination.current_page - 1)">Previous</button>
                                    </li>
                                    <template x-for="page in getPageNumbers()" :key="page">
                                        <li class="page-item" :class="{ 'active': page === pagination.current_page }">
                                            <button class="page-link" @click="changePage(page)" x-text="page"></button>
                                        </li>
                                    </template>
                                    <li class="page-item" :class="{ 'disabled': pagination.current_page >= pagination.last_page }">
                                        <button class="page-link" @click="changePage(pagination.current_page + 1)">Next</button>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit RFID Modal -->
<div class="modal fade" id="editRfidModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit RFID Card</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form @submit.prevent="updateCard()">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Card Number</label>
                        <input type="text" class="form-control" x-model="editForm.card_number" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" x-model="editForm.status" required>
                            <option value="AVAILABLE">Available</option>
                            <option value="ASSIGNED">Assigned</option>
                            <option value="DAMAGED">Damaged</option>
                            <option value="LOST">Lost</option>
                            <option value="INACTIVE">Inactive</option>
                        </select>
                    </div>
                    <div x-show="editForm.status === 'ASSIGNED'" class="mb-3">
                        <label class="form-label">Assign to Employee</label>
                        <select class="form-select" x-model="editForm.karyawan_id">
                            <option value="">Select Employee...</option>
                            <template x-for="employee in availableEmployees" :key="employee.id">
                                <option :value="employee.id" x-text="`${employee.nama} (${employee.nip}) - ${employee.jabatan}`"></option>
                            </template>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" x-model="editForm.notes" rows="3" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" :disabled="updating">
                        <span x-show="updating">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Updating...
                        </span>
                        <span x-show="!updating">Update Card</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.icon-shape {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    color: #344767;
    border-bottom: 1px solid #e9ecef;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.btn-group .btn {
    padding: 0.25rem 0.5rem;
}
</style>
@endpush

@push('scripts')
<script>
function rfidManagementData() {
    return {
        rfidCards: [],
        statistics: {},
        availableEmployees: [],
        selectedCards: [],
        loading: false,
        updating: false,
        filters: {
            search: '',
            status: '',
            assigned: ''
        },
        pagination: {
            current_page: 1,
            last_page: 1,
            per_page: 15,
            total: 0,
            from: 0,
            to: 0
        },
        editForm: {
            id: '',
            card_number: '',
            status: '',
            karyawan_id: '',
            notes: ''
        },

        async init() {
            await this.loadStatistics();
            await this.loadRfidData();
            await this.loadAvailableEmployees();
        },

        async loadStatistics() {
            try {
                const response = await fetch('/rfid/api/statistics');
                const data = await response.json();
                
                if (data.success) {
                    this.statistics = data.statistics;
                }
            } catch (error) {
                console.error('Error loading statistics:', error);
            }
        },

        async loadRfidData(page = 1) {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: page,
                    per_page: this.pagination.per_page,
                    ...this.filters
                });

                const response = await fetch(`/rfid/api/data?${params}`);
                const data = await response.json();
                
                if (data.success) {
                    this.rfidCards = data.data;
                    this.pagination = data.pagination;
                } else {
                    showNotification('error', data.message);
                }
            } catch (error) {
                console.error('Error loading RFID data:', error);
                showNotification('error', 'Failed to load RFID data');
            } finally {
                this.loading = false;
            }
        },

        async loadAvailableEmployees() {
            try {
                const response = await fetch('/rfid/api/available-employees');
                const data = await response.json();
                
                if (data.success) {
                    this.availableEmployees = data.data;
                }
            } catch (error) {
                console.error('Error loading employees:', error);
            }
        },

        async refreshData() {
            await this.loadStatistics();
            await this.loadRfidData();
            await this.loadAvailableEmployees();
            showNotification('success', 'Data refreshed successfully');
        },

        clearFilters() {
            this.filters = {
                search: '',
                status: '',
                assigned: ''
            };
            this.loadRfidData();
        },

        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page) {
                this.loadRfidData(page);
            }
        },

        getPageNumbers() {
            const pages = [];
            const start = Math.max(1, this.pagination.current_page - 2);
            const end = Math.min(this.pagination.last_page, start + 4);
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            return pages;
        },

        toggleAllCards(checked) {
            if (checked) {
                this.selectedCards = this.rfidCards.map(card => card.id);
            } else {
                this.selectedCards = [];
            }
        },

        getStatusBadgeClass(status) {
            const classes = {
                'AVAILABLE': 'bg-success',
                'ASSIGNED': 'bg-info',
                'DAMAGED': 'bg-warning',
                'LOST': 'bg-danger',
                'INACTIVE': 'bg-secondary'
            };
            return classes[status] || 'bg-secondary';
        },

        formatDate(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        truncateText(text, length) {
            if (!text) return '-';
            return text.length > length ? text.substring(0, length) + '...' : text;
        },

        editCard(card) {
            this.editForm = {
                id: card.id,
                card_number: card.card_number,
                status: card.status,
                karyawan_id: card.karyawan_id || '',
                notes: card.notes || ''
            };
            
            new bootstrap.Modal(document.getElementById('editRfidModal')).show();
        },

        async updateCard() {
            this.updating = true;
            try {
                const response = await fetch(`/rfid/api/${this.editForm.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.editForm)
                });

                const data = await response.json();
                
                if (data.success) {
                    showNotification('success', data.message);
                    bootstrap.Modal.getInstance(document.getElementById('editRfidModal')).hide();
                    await this.loadRfidData();
                    await this.loadStatistics();
                    await this.loadAvailableEmployees();
                } else {
                    showNotification('error', data.message);
                }
            } catch (error) {
                console.error('Error updating card:', error);
                showNotification('error', 'Failed to update card');
            } finally {
                this.updating = false;
            }
        },

        confirmDelete(card) {
            if (confirm(`Are you sure you want to delete RFID card ${card.card_number}?`)) {
                this.deleteCard(card.id);
            }
        },

        async deleteCard(cardId) {
            try {
                const response = await fetch(`/rfid/api/${cardId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    showNotification('success', data.message);
                    await this.loadRfidData();
                    await this.loadStatistics();
                    await this.loadAvailableEmployees();
                } else {
                    showNotification('error', data.message);
                }
            } catch (error) {
                console.error('Error deleting card:', error);
                showNotification('error', 'Failed to delete card');
            }
        },

        async bulkChangeStatus(newStatus) {
            if (this.selectedCards.length === 0) return;
            
            if (confirm(`Change status of ${this.selectedCards.length} selected cards to ${newStatus}?`)) {
                try {
                    const response = await fetch('/rfid/api/bulk-operation', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            operation: 'change_status',
                            card_ids: this.selectedCards,
                            new_status: newStatus
                        })
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        showNotification('success', data.message);
                        this.selectedCards = [];
                        await this.loadRfidData();
                        await this.loadStatistics();
                    } else {
                        showNotification('error', data.message);
                    }
                } catch (error) {
                    console.error('Error in bulk operation:', error);
                    showNotification('error', 'Failed to change status');
                }
            }
        },

        async bulkDelete() {
            if (this.selectedCards.length === 0) return;
            
            if (confirm(`Delete ${this.selectedCards.length} selected RFID cards? This action cannot be undone.`)) {
                try {
                    const response = await fetch('/rfid/api/bulk-operation', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            operation: 'delete',
                            card_ids: this.selectedCards
                        })
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        showNotification('success', data.message);
                        this.selectedCards = [];
                        await this.loadRfidData();
                        await this.loadStatistics();
                        await this.loadAvailableEmployees();
                    } else {
                        showNotification('error', data.message);
                    }
                } catch (error) {
                    console.error('Error in bulk delete:', error);
                    showNotification('error', 'Failed to delete cards');
                }
            }
        }
    }
}

// Notification function
function showNotification(type, message) {
    // You can integrate with your existing notification system
    // For now, using simple alert
    if (type === 'success') {
        alert('✅ ' + message);
    } else {
        alert('❌ ' + message);
    }
}
</script>
@endpush