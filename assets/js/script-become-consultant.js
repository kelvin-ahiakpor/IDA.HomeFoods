// Notification function
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-4 py-2 rounded-md text-white shadow-lg z-50`;
    notification.style.backgroundColor = type === 'success' ? '#048069' : '#D4AE36'; // Gold/yellow color
    notification.style.minWidth = '200px';
    notification.style.maxWidth = '300px';
    notification.style.fontSize = '0.875rem'; // Smaller font size
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

/**
 * Adds a new certification input field to the form
 */
function addCertificationField() {
    const container = document.getElementById('certificationsContainer');
    const newInput = document.createElement('div');
    newInput.innerHTML = `
        <input 
            type="text" 
            name="certifications[]"
            placeholder="Enter certification (e.g., CPT - ACE 2023)" 
            class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0"
            required>
    `;
    container.appendChild(newInput);
}

/**
 * Initialize the consultant application form submission handler
 */
function initConsultantApplicationForm() {
    const form = document.getElementById('consultantApplicationForm');
    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const formData = new FormData(this);
            const response = await fetch('../../actions/submitConsultantApplication.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showNotification('Application submitted successfully! Our team will review your application.', 'success');
                // Redirect after a short delay
                setTimeout(() => {
                    window.location.href = '../consultant/dashboard.php';
                }, 2000);
            } else {
                throw new Error(data.message || 'Application submission failed');
            }
        } catch (error) {
            showNotification('There was an error submitting your application. Please try again.', 'error');
            console.error('Error:', error);
        }
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', initConsultantApplicationForm); 