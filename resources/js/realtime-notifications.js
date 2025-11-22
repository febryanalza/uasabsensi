/**
 * Real-time Notification System
 * Handles live updates and notifications for karyawan management
 */

class RealTimeNotifications {
    constructor() {
        this.lastUpdate = new Date().getTime();
        this.listeners = new Map();
        this.init();
    }
    
    init() {
        // Setup storage listener for cross-tab communication
        window.addEventListener('storage', (e) => {
            if (e.key === 'karyawan_update') {
                this.handleStorageUpdate(e);
            }
        });
        
        // Setup visibility change listener
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.handleVisibilityChange();
            }
        });
        
        // Setup online/offline listeners
        window.addEventListener('online', () => {
            this.handleNetworkChange(true);
        });
        
        window.addEventListener('offline', () => {
            this.handleNetworkChange(false);
        });
    }
    
    // Register callback for specific update types
    on(event, callback) {
        if (!this.listeners.has(event)) {
            this.listeners.set(event, []);
        }
        this.listeners.get(event).push(callback);
    }
    
    // Remove callback
    off(event, callback) {
        if (this.listeners.has(event)) {
            const callbacks = this.listeners.get(event);
            const index = callbacks.indexOf(callback);
            if (index > -1) {
                callbacks.splice(index, 1);
            }
        }
    }
    
    // Trigger update broadcast
    broadcast(action, data) {
        const updateData = {
            action: action,
            data: data,
            timestamp: new Date().getTime(),
            source: 'local'
        };
        
        // Store in localStorage for cross-tab communication
        localStorage.setItem('karyawan_update', JSON.stringify(updateData));
        
        // Dispatch local event
        this.emit(action, updateData);
        
        console.log('📡 Real-time update broadcasted:', updateData);
    }
    
    // Emit event to registered listeners
    emit(event, data) {
        if (this.listeners.has(event)) {
            this.listeners.get(event).forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error('Error in event callback:', error);
                }
            });
        }
    }
    
    // Handle storage updates from other tabs
    handleStorageUpdate(e) {
        try {
            const updateData = JSON.parse(e.newValue);
            if (updateData && updateData.timestamp > this.lastUpdate) {
                updateData.source = 'remote';
                this.emit(updateData.action, updateData);
                this.lastUpdate = updateData.timestamp;
                
                // Show cross-tab notification
                this.showCrossTabNotification(updateData);
            }
        } catch (error) {
            console.error('Error parsing storage update:', error);
        }
    }
    
    // Handle tab visibility change
    handleVisibilityChange() {
        const now = new Date().getTime();
        if (now - this.lastUpdate > 300000) { // 5 minutes
            this.emit('visibility_change', { timestamp: now });
        }
    }
    
    // Handle network status change
    handleNetworkChange(isOnline) {
        this.emit('network_change', { online: isOnline, timestamp: new Date().getTime() });
        
        if (isOnline) {
            this.showNetworkNotification('Koneksi internet pulih', 'success');
        } else {
            this.showNetworkNotification('Koneksi internet terputus', 'warning');
        }
    }
    
    // Show cross-tab notification
    showCrossTabNotification(updateData) {
        let message = '';
        let type = 'info';
        
        switch (updateData.action) {
            case 'create':
                message = `Karyawan "${updateData.data.nama}" ditambahkan di tab lain`;
                type = 'info';
                break;
            case 'update':
                message = 'Data karyawan diperbarui di tab lain';
                type = 'info';
                break;
            case 'delete':
                message = 'Data karyawan dihapus di tab lain';
                type = 'warning';
                break;
        }
        
        if (message) {
            this.showNotification(message, type, {
                icon: '🔄',
                duration: 3000,
                actions: [{
                    text: 'Refresh',
                    action: () => window.location.reload()
                }]
            });
        }
    }
    
    // Show network notification
    showNetworkNotification(message, type) {
        this.showNotification(message, type, {
            icon: type === 'success' ? '🌐' : '⚠️',
            duration: 2000
        });
    }
    
    // Enhanced notification display
    showNotification(message, type = 'info', options = {}) {
        const notification = document.createElement('div');
        const icon = options.icon || this.getTypeIcon(type);
        const duration = options.duration || 4000;
        
        notification.className = `fixed top-4 right-4 z-50 flex items-center p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${this.getTypeClasses(type)}`;
        
        notification.innerHTML = `
            <div class="flex items-center">
                <span class="text-lg mr-3">${icon}</span>
                <div class="flex-1">
                    <p class="font-medium">${message}</p>
                    ${options.actions ? this.renderActions(options.actions) : ''}
                </div>
                <button class="ml-3 text-current opacity-60 hover:opacity-100" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Auto remove
        setTimeout(() => {
            if (notification.parentElement) {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }
        }, duration);
    }
    
    getTypeIcon(type) {
        const icons = {
            success: '✅',
            error: '❌', 
            warning: '⚠️',
            info: 'ℹ️'
        };
        return icons[type] || icons.info;
    }
    
    getTypeClasses(type) {
        const classes = {
            success: 'bg-green-100 text-green-800 border border-green-200',
            error: 'bg-red-100 text-red-800 border border-red-200',
            warning: 'bg-yellow-100 text-yellow-800 border border-yellow-200',
            info: 'bg-blue-100 text-blue-800 border border-blue-200'
        };
        return classes[type] || classes.info;
    }
    
    renderActions(actions) {
        return `
            <div class="mt-2 flex space-x-2">
                ${actions.map(action => `
                    <button class="px-3 py-1 text-sm bg-white bg-opacity-50 rounded hover:bg-opacity-70 transition-colors"
                            onclick="${action.action}">
                        ${action.text}
                    </button>
                `).join('')}
            </div>
        `;
    }
}

// Global instance
window.realTimeNotifications = new RealTimeNotifications();

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RealTimeNotifications;
}