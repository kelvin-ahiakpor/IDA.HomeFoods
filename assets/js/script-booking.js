document.addEventListener('DOMContentLoaded', () => {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('booking_date').setAttribute('min', today);
    
    document.getElementById('bookingForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        try {
            const response = await fetch('../actions/bookConsultation.php', {
                method: 'POST',
                body: formData
            });
            if (response.ok) {
                showNotification('Booking successful!');
                window.location.href = 'explore_consultants.php'; // Redirect back to the consultants page
            } else {
                const errorData = await response.json();
                showNotification('Booking failed: ' + errorData.message, 'error');
            }
        } catch (error) {
            showNotification('There was an error booking your consultation. Please try again.', 'error');
            console.error('Error:', error);
        }
    });
});