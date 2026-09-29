/**
 * Driving School Management System - API Client
 * Modern ES6+ JavaScript module for handling all API interactions
 */

class DrivingSchoolAPI {
    constructor(schoolSlug) {
        this.schoolSlug = schoolSlug;
        this.baseUrl = `/${schoolSlug}`;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    }

    /**
     * Generic HTTP request handler
     */
    async request(endpoint, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        };

        const config = {
            ...defaultOptions,
            ...options,
            headers: { ...defaultOptions.headers, ...options.headers }
        };

        try {
            const response = await fetch(endpoint, config);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Request failed');
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // ==================== COURSES API ====================
    
    async getCourses(filters = {}) {
        const params = new URLSearchParams(filters);
        return this.request(`${this.baseUrl}/admin/courses?${params}`);
    }

    async getCourse(id) {
        return this.request(`${this.baseUrl}/admin/courses/${id}`);
    }

    async createCourse(courseData) {
        return this.request(`${this.baseUrl}/admin/courses`, {
            method: 'POST',
            body: JSON.stringify(courseData)
        });
    }

    async updateCourse(id, courseData) {
        return this.request(`${this.baseUrl}/admin/courses/${id}`, {
            method: 'PUT',
            body: JSON.stringify({ ...courseData, _method: 'PUT' })
        });
    }

    async deleteCourse(id) {
        return this.request(`${this.baseUrl}/admin/courses/${id}`, {
            method: 'DELETE',
            body: JSON.stringify({ _method: 'DELETE' })
        });
    }

    // ==================== BOOKINGS API ====================
    
    async getBookings(filters = {}, role = 'admin') {
        const params = new URLSearchParams(filters);
        return this.request(`${this.baseUrl}/${role}/bookings?${params}`);
    }

    async getBooking(id, role = 'admin') {
        return this.request(`${this.baseUrl}/${role}/bookings/${id}`);
    }

    async createBooking(bookingData, role = 'admin') {
        return this.request(`${this.baseUrl}/${role}/bookings`, {
            method: 'POST',
            body: JSON.stringify(bookingData)
        });
    }

    async updateBooking(id, bookingData, role = 'admin') {
        return this.request(`${this.baseUrl}/${role}/bookings/${id}`, {
            method: 'PUT',
            body: JSON.stringify({ ...bookingData, _method: 'PUT' })
        });
    }

    async updateBookingStatus(id, status, role = 'admin') {
        return this.request(`${this.baseUrl}/${role}/bookings/${id}/status`, {
            method: 'PATCH',
            body: JSON.stringify({ status })
        });
    }

    async deleteBooking(id, role = 'admin') {
        return this.request(`${this.baseUrl}/${role}/bookings/${id}`, {
            method: 'DELETE',
            body: JSON.stringify({ _method: 'DELETE' })
        });
    }

    // ==================== PAYMENTS API ====================
    
    async getPayments(filters = {}) {
        const params = new URLSearchParams(filters);
        return this.request(`${this.baseUrl}/admin/payments?${params}`);
    }

    async getPayment(id, role = 'admin') {
        return this.request(`${this.baseUrl}/${role}/payments/${id}`);
    }

    async createPayment(paymentData) {
        return this.request(`${this.baseUrl}/admin/payments`, {
            method: 'POST',
            body: JSON.stringify(paymentData)
        });
    }

    async updatePayment(id, paymentData) {
        return this.request(`${this.baseUrl}/admin/payments/${id}`, {
            method: 'PUT',
            body: JSON.stringify({ ...paymentData, _method: 'PUT' })
        });
    }

    async deletePayment(id) {
        return this.request(`${this.baseUrl}/admin/payments/${id}`, {
            method: 'DELETE',
            body: JSON.stringify({ _method: 'DELETE' })
        });
    }

    async getPaymentStatistics() {
        return this.request(`${this.baseUrl}/admin/payments/statistics`);
    }

    // ==================== PROGRESS API ====================
    
    async getProgresses(filters = {}, role = 'admin') {
        const params = new URLSearchParams(filters);
        return this.request(`${this.baseUrl}/${role}/progress?${params}`);
    }

    async getProgress(id, role = 'admin') {
        return this.request(`${this.baseUrl}/${role}/progress/${id}`);
    }

    async createOrUpdateProgress(progressData, role = 'admin') {
        return this.request(`${this.baseUrl}/${role}/progress`, {
            method: 'POST',
            body: JSON.stringify(progressData)
        });
    }

    async updateProgress(id, progressData, role = 'admin') {
        return this.request(`${this.baseUrl}/${role}/progress/${id}`, {
            method: 'PUT',
            body: JSON.stringify({ ...progressData, _method: 'PUT' })
        });
    }

    async deleteProgress(id, role = 'admin') {
        return this.request(`${this.baseUrl}/${role}/progress/${id}`, {
            method: 'DELETE',
            body: JSON.stringify({ _method: 'DELETE' })
        });
    }

    async getStudentProgressSummary(studentId) {
        return this.request(`${this.baseUrl}/admin/progress/student/${studentId}/summary`);
    }
}

// ==================== UI HELPER FUNCTIONS ====================

class UIHelpers {
    static showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        Object.assign(notification.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            padding: '15px 25px',
            borderRadius: '8px',
            backgroundColor: type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8',
            color: 'white',
            fontWeight: '600',
            boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
            zIndex: '10000',
            animation: 'slideIn 0.3s ease-out'
        });
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    static showLoading(message = 'Loading...') {
        const loading = document.createElement('div');
        loading.id = 'loading-overlay';
        loading.innerHTML = `
            <div style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
            ">
                <div style="
                    background: white;
                    padding: 30px 40px;
                    border-radius: 12px;
                    text-align: center;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                ">
                    <div style="
                        width: 50px;
                        height: 50px;
                        border: 5px solid #f3f3f3;
                        border-top: 5px solid #667eea;
                        border-radius: 50%;
                        margin: 0 auto 15px;
                        animation: spin 1s linear infinite;
                    "></div>
                    <p style="
                        margin: 0;
                        font-weight: 600;
                        color: #333;
                    ">${message}</p>
                </div>
            </div>
        `;
        document.body.appendChild(loading);
    }

    static hideLoading() {
        const loading = document.getElementById('loading-overlay');
        if (loading) loading.remove();
    }

    static confirmAction(message = 'Are you sure?') {
        return confirm(message);
    }

    static formatCurrency(amount) {
        return new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP'
        }).format(amount);
    }

    static formatDate(date, includeTime = false) {
        const d = new Date(date);
        const options = {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        };
        
        if (includeTime) {
            options.hour = '2-digit';
            options.minute = '2-digit';
        }
        
        return d.toLocaleDateString('en-US', options);
    }

    static debounce(func, wait = 300) {
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
}

// ==================== FORM VALIDATION ====================

class FormValidator {
    static validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    static validatePhone(phone) {
        const re = /^[0-9]{10,15}$/;
        return re.test(phone.replace(/[\s-]/g, ''));
    }

    static validateRequired(value) {
        return value !== null && value !== undefined && value.toString().trim() !== '';
    }

    static validateNumber(value, min = null, max = null) {
        const num = parseFloat(value);
        if (isNaN(num)) return false;
        if (min !== null && num < min) return false;
        if (max !== null && num > max) return false;
        return true;
    }

    static validateForm(formElement) {
        const errors = [];
        const inputs = formElement.querySelectorAll('input[required], select[required], textarea[required]');
        
        inputs.forEach(input => {
            if (!this.validateRequired(input.value)) {
                errors.push(`${input.name || input.id} is required`);
            }
        });
        
        return {
            isValid: errors.length === 0,
            errors
        };
    }
}

// ==================== INITIALIZATION ====================

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { DrivingSchoolAPI, UIHelpers, FormValidator };
}

// Make available globally
window.DrivingSchoolAPI = DrivingSchoolAPI;
window.UIHelpers = UIHelpers;
window.FormValidator = FormValidator;
