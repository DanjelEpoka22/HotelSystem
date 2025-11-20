// Form validation functionality
class FormValidator {
    constructor(formId) {
        this.form = document.getElementById(formId);
        this.fields = [];
        this.errors = [];
        
        if (this.form) {
            this.init();
        }
    }

    init() {
        this.setupValidationRules();
        this.setupEventListeners();
    }

    setupValidationRules() {
        // Define validation rules for each field
        this.fields = Array.from(this.form.querySelectorAll('input, select, textarea'))
            .filter(field => field.hasAttribute('data-validate'));
        
        this.fields.forEach(field => {
            const rules = field.getAttribute('data-validate').split(' ');
            field.validationRules = rules;
        });
    }

    setupEventListeners() {
        this.form.addEventListener('submit', (e) => {
            if (!this.validateForm()) {
                e.preventDefault();
                this.showErrors();
            }
        });

        // Real-time validation
        this.fields.forEach(field => {
            field.addEventListener('blur', () => {
                this.validateField(field);
            });
            
            field.addEventListener('input', () => {
                this.clearFieldError(field);
            });
        });
    }

    validateForm() {
        this.errors = [];
        let isValid = true;

        this.fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    validateField(field) {
        const value = field.value.trim();
        const rules = field.validationRules || [];
        let isValid = true;

        // Clear previous errors
        this.clearFieldError(field);

        // Required validation
        if (rules.includes('required') && !value) {
            this.addError(field, 'This field is required');
            isValid = false;
        }

        // Email validation
        if (rules.includes('email') && value && !this.isValidEmail(value)) {
            this.addError(field, 'Please enter a valid email address');
            isValid = false;
        }

        // Phone validation
        if (rules.includes('phone') && value && !this.isValidPhone(value)) {
            this.addError(field, 'Please enter a valid phone number');
            isValid = false;
        }

        // Min length validation
        const minLength = field.getAttribute('data-min-length');
        if (minLength && value.length < parseInt(minLength)) {
            this.addError(field, `Minimum ${minLength} characters required`);
            isValid = false;
        }

        // Max length validation
        const maxLength = field.getAttribute('data-max-length');
        if (maxLength && value.length > parseInt(maxLength)) {
            this.addError(field, `Maximum ${maxLength} characters allowed`);
            isValid = false;
        }

        // Pattern validation
        const pattern = field.getAttribute('pattern');
        if (pattern && value && !new RegExp(pattern).test(value)) {
            const title = field.getAttribute('title') || 'Please match the requested format';
            this.addError(field, title);
            isValid = false;
        }

        // Custom validation
        const customValidator = field.getAttribute('data-custom-validator');
        if (customValidator && value) {
            const customIsValid = this[customValidator]?.(value, field);
            if (customIsValid !== undefined && !customIsValid) {
                this.addError(field, field.getAttribute('data-custom-error') || 'Invalid value');
                isValid = false;
            }
        }

        if (isValid) {
            field.classList.add('is-valid');
            field.classList.remove('is-invalid');
        } else {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
        }

        return isValid;
    }

    addError(field, message) {
        this.errors.push({ field, message });
        
        // Add error message to DOM
        const errorElement = document.createElement('div');
        errorElement.className = 'invalid-feedback';
        errorElement.textContent = message;
        
        field.parentNode.appendChild(errorElement);
    }

    clearFieldError(field) {
        field.classList.remove('is-invalid', 'is-valid');
        
        const existingError = field.parentNode.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }
    }

    showErrors() {
        // Focus on first error field
        if (this.errors.length > 0) {
            this.errors[0].field.focus();
        }
        
        // Show general error message
        if (this.errors.length > 0) {
            window.villaAdrianApp?.showNotification(
                'Please correct the errors in the form',
                'error'
            );
        }
    }

    // Validation methods
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    isValidPhone(phone) {
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        return phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ''));
    }

    isValidDate(date) {
        return !isNaN(Date.parse(date));
    }

    isFutureDate(date) {
        return new Date(date) > new Date();
    }

    isAdult(birthdate) {
        const today = new Date();
        const birthDate = new Date(birthdate);
        const age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            return age - 1 >= 18;
        }
        return age >= 18;
    }

    // Custom validators
    validatePasswordStrength(password) {
        const strongRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})/;
        return strongRegex.test(password);
    }

    validateCreditCard(number) {
        // Luhn algorithm
        let sum = 0;
        let isEven = false;
        
        for (let i = number.length - 1; i >= 0; i--) {
            let digit = parseInt(number.charAt(i), 10);
            
            if (isEven) {
                digit *= 2;
                if (digit > 9) {
                    digit -= 9;
                }
            }
            
            sum += digit;
            isEven = !isEven;
        }
        
        return sum % 10 === 0;
    }

    validateCardExpiry(expiry) {
        const [month, year] = expiry.split('/');
        const expiryDate = new Date(2000 + parseInt(year), parseInt(month) - 1);
        const today = new Date();
        
        return expiryDate > today;
    }

    validateCVV(cvv) {
        return /^\d{3,4}$/.test(cvv);
    }
}

// Initialize validators for all forms with data-validate attribute
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-validate]').forEach(form => {
        new FormValidator(form.id);
    });
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FormValidator;
}