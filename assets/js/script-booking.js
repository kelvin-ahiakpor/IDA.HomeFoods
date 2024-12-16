let selectedSlotId = null;
let selectedSlotDetails = null;

function bookSlot(slotId, date, startTime, endTime) {
    selectedSlotId = slotId;
    selectedSlotDetails = { date, startTime, endTime };
    
    // Update modal content
    const bookingDetails = document.getElementById('bookingDetails');
    bookingDetails.innerHTML = `
        <p class="text-gray-600">You are about to book a consultation for:</p>
        <p class="font-medium text-gray-800 mt-2">${formatDate(date)}</p>
        <p class="text-gray-600">${formatTime(startTime)} - ${formatTime(endTime)}</p>
    `;
    
    // Show modal
    document.getElementById('bookingModal').classList.remove('hidden');
}

function closeBookingModal() {
    document.getElementById('bookingModal').classList.add('hidden');
    selectedSlotId = null;
    selectedSlotDetails = null;
}

async function confirmBooking() {
    if (!selectedSlotId) return;

    const notes = document.getElementById('meetingNotes').value;

    try {
        const response = await fetch('../../actions/processBooking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                availability_id: selectedSlotId,
                notes: notes
            })
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Booking confirmed successfully!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(data.message || 'Error confirming booking', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    } finally {
        closeBookingModal();
    }
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-4 py-2 rounded-md text-white shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
}

function formatTime(timeStr) {
    return new Date(`2000-01-01T${timeStr}`).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: 'numeric',
        hour12: true
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Cancel button click handlers
    document.querySelectorAll('.cancel-booking-btn').forEach(button => {
        button.addEventListener('click', () => {
            const bookingId = button.getAttribute('data-booking-id');
            showCancellationModal(bookingId);
        });
    });

    // Modal close handlers
    document.getElementById('cancelCancellation').addEventListener('click', closeCancellationModal);
    document.getElementById('confirmCancellation').addEventListener('click', cancelBooking);
});




