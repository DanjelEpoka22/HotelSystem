// Global JavaScript functionality
class VillaAdrianApp {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupUIComponents();
        this.setupFormValidation();
    }

    setupEventListeners() {
        // Mobile menu toggle
        this.setupMobileMenu();
        
        // Form submissions
        this.setupFormHandlers();
        
        // Modal functionality
        this.setupModals();
        
        // Date picker enhancements
        this.setupDatePickers();
    }

    setupMobileMenu() {
        const menuToggle = document.querySelector('.menu-toggle');
        const navMenu = document.querySelector('.nav-menu');
        
        if (menuToggle && navMenu) {
            menuToggle.addEventListener('click', () => {
                navMenu.classList.toggle('active');
                menuToggle.classList.toggle('active');
            });
        }
    }

    setupFormHandlers() {
        // Auto-save forms
        document.querySelectorAll('form[data-autosave]').forEach(form => {
            form.addEventListener('input', this.debounce(() => {
                this.autoSaveForm(form);
            }, 1000));
        });

        // Confirm destructive actions
        document.querySelectorAll('a[data-confirm], button[data-confirm]').forEach(element => {
            element.addEventListener('click', (e) => {
                const message = element.getAttribute('data-confirm') || 'Are you sure?';
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });
    }

    setupModals() {
        // Close modal on outside click
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                e.target.style.display = 'none';
            }
        });

        // Close modal on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.style.display = 'none';
                });
            }
        });
    }

    setupDatePickers() {
        // Add min date to check-in fields
        const checkInInputs = document.querySelectorAll('input[type="date"][name="check_in"]');
        checkInInputs.forEach(input => {
            if (!input.min) {
                input.min = new Date().toISOString().split('T')[0];
            }
        });

        // Link check-in and check-out dates
        const checkInFields = document.querySelectorAll('input[name="check_in"]');
        const checkOutFields = document.querySelectorAll('input[name="check_out"]');
        
        checkInFields.forEach(checkIn => {
            checkIn.addEventListener('change', () => {
                const checkInDate = new Date(checkIn.value);
                const nextDay = new Date(checkInDate);
                nextDay.setDate(nextDay.getDate() + 1);
                
                checkOutFields.forEach(checkOut => {
                    checkOut.min = nextDay.toISOString().split('T')[0];
                    if (new Date(checkOut.value) <= checkInDate) {
                        checkOut.value = nextDay.toISOString().split('T')[0];
                    }
                });
            });
        });
    }

    setupUIComponents() {
        // Initialize tooltips
        this.setupTooltips();
        
        // Initialize loading states
        this.setupLoadingStates();
        
        // Initialize notifications
        this.setupNotifications();
    }

    setupTooltips() {
        const tooltips = document.querySelectorAll('[data-tooltip]');
        
        tooltips.forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                const tooltipText = element.getAttribute('data-tooltip');
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = tooltipText;
                
                document.body.appendChild(tooltip);
                
                const rect = element.getBoundingClientRect();
                tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
                
                element._tooltip = tooltip;
            });
            
            element.addEventListener('mouseleave', () => {
                if (element._tooltip) {
                    element._tooltip.remove();
                    element._tooltip = null;
                }
            });
        });
    }

    setupLoadingStates() {
        document.addEventListener('submit', (e) => {
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (submitBtn) {
                submitBtn.setAttribute('data-original-text', submitBtn.textContent);
                submitBtn.textContent = 'Loading...';
                submitBtn.disabled = true;
            }
        });
    }

    setupNotifications() {
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                if (!alert.classList.contains('alert-persistent')) {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }
            });
        }, 5000);
    }

    setupFormValidation() {
        document.addEventListener('blur', (e) => {
            if (e.target.matches('input[required], select[required], textarea[required]')) {
                this.validateField(e.target);
            }
        }, true);

        document.addEventListener('input', (e) => {
            if (e.target.hasAttribute('data-pattern')) {
                this.validatePattern(e.target);
            }
        });
    }

    validateField(field) {
        const value = field.value.trim();
        const isValid = field.checkValidity();
        
        field.classList.remove('is-valid', 'is-invalid');
        field.classList.add(isValid ? 'is-valid' : 'is-invalid');
        
        return isValid;
    }

    validatePattern(field) {
        const pattern = field.getAttribute('data-pattern');
        const regex = new RegExp(pattern);
        const isValid = regex.test(field.value);
        
        field.classList.remove('is-valid', 'is-invalid');
        field.classList.add(isValid ? 'is-valid' : 'is-invalid');
    }

    autoSaveForm(form) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        localStorage.setItem(`autosave_${form.id}`, JSON.stringify(data));
    }

    loadAutoSave(form) {
        const saved = localStorage.getItem(`autosave_${form.id}`);
        if (saved) {
            const data = JSON.parse(saved);
            Object.keys(data).forEach(key => {
                const field = form.querySelector(`[name="${key}"]`);
                if (field) {
                    field.value = data[key];
                }
            });
        }
    }

    clearAutoSave(form) {
        localStorage.removeItem(`autosave_${form.id}`);
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Utility functions
    formatCurrency(amount, currency = 'EUR') {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(amount);
    }

    formatDate(date) {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // API helper methods
    async apiCall(endpoint, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
            },
        };

        const config = { ...defaultOptions, ...options };
        
        try {
            const response = await fetch(endpoint, config);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || 'API request failed');
            }
            
            return data;
        } catch (error) {
            console.error('API call failed:', error);
            this.showNotification(error.message, 'error');
            throw error;
        }
    }
}

// Initialize app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.villaAdrianApp = new VillaAdrianApp();
});

// Global utility functions
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
    input.setAttribute('type', type);
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        window.villaAdrianApp?.showNotification('Copied to clipboard!', 'success');
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}