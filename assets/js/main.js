/**
 * IOMS - Main JavaScript
 
 */

// Toggle mobile menu
function toggleMenu() {
    const menu = document.getElementById('navMenu');
    if (menu) {
        menu.classList.toggle('active');
    }
}

// Toggle dropdown menu
function toggleDropdown(e) {
    e.preventDefault();
    e.stopPropagation(); // Prevent event from bubbling to document

    const dropdown = e.currentTarget.parentElement;
    const wasActive = dropdown.classList.contains('active');

    // Close all other dropdowns first
    document.querySelectorAll('.navbar-item.dropdown').forEach(item => {
        if (item !== dropdown) {
            item.classList.remove('active');
        }
    });

    // Toggle current dropdown
    dropdown.classList.toggle('active');
}

// Close menu/dropdowns when clicking outside
document.addEventListener('click', function (e) {
    // Close mobile menu
    const menu = document.getElementById('navMenu');
    const toggle = document.querySelector('.menu-toggle');

    if (menu && toggle && !menu.contains(e.target) && !toggle.contains(e.target)) {
        menu.classList.remove('active');
    }

    // Close all dropdowns
    if (!e.target.closest('.navbar-item.dropdown')) {
        document.querySelectorAll('.navbar-item.dropdown').forEach(item => {
            item.classList.remove('active');
        });
    }
});

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// Form validation helper
function validateForm(formId, rules) {
    const form = document.getElementById(formId);
    if (!form) return true;

    let isValid = true;

    Object.keys(rules).forEach(fieldId => {
        const field = document.getElementById(fieldId);
        const rule = rules[fieldId];

        if (field) {
            const value = field.value.trim();
            let fieldValid = true;

            if (rule.required && !value) {
                fieldValid = false;
            }

            if (rule.minLength && value.length < rule.minLength) {
                fieldValid = false;
            }

            if (rule.pattern && !rule.pattern.test(value)) {
                fieldValid = false;
            }

            if (rule.min && parseFloat(value) < rule.min) {
                fieldValid = false;
            }

            if (!fieldValid) {
                field.classList.add('error');
                isValid = false;
            } else {
                field.classList.remove('error');
            }
        }
    });

    return isValid;
}

// Format number with commas
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Format currency
function formatCurrency(amount) {
    return parseFloat(amount).toFixed(2) + ' ر.س';
}

// Confirm action helper
function confirmAction(message) {
    return confirm(message);
}

// Show loading overlay
function showLoading() {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.id = 'loadingOverlay';
    overlay.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(overlay);
}

// Hide loading overlay
function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.remove();
    }
}

// Debounce function for search
function debounce(func, wait) {
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

// Real-time search (if needed)
const searchInput = document.querySelector('.search-box input');
if (searchInput) {
    searchInput.addEventListener('input', debounce(function () {
        // Can be used for AJAX search
        // For now, form submission is used
    }, 300));
}

// Print functionality
function printPage() {
    window.print();
}

// Export to CSV (helper function)
function exportToCSV(data, filename) {
    const csv = data.map(row => row.join(',')).join('\n');
    const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename + '.csv';
    link.click();
}

// Dark mode toggle (if implemented)
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
}

// Check for saved dark mode preference
if (localStorage.getItem('darkMode') === 'true') {
    document.body.classList.add('dark-mode');
}

// Toggle Password Visibility
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    if (input && icon) {
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
}

console.log('IOMS - نظام إدارة المخزون والطلبات - تم التحميل بنجاح');
