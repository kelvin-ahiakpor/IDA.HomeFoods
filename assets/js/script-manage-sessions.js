let selectedSessionId = null;

// View Session Details Modal
function viewSessionDetails(sessionId, notes) {
    const modal = document.getElementById('viewSessionModal');
    const notesContent = document.getElementById('sessionNotes');
    
    notesContent.textContent = notes || 'No notes available for this session.';
    modal.classList.remove('hidden');
}

function closeViewModal() {
    const modal = document.getElementById('viewSessionModal');
    modal.classList.add('hidden');
}

// Notification function
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-4 py-2 rounded-md text-white shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

function openCancellationModal(bookingId) {
    document.getElementById('cancellationModal').classList.remove('hidden');
    document.getElementById('cancelBookingId').value = bookingId;
}

function closeCancellationModal() {
    document.getElementById('cancellationModal').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
    // Existing event listeners...

    // Cancel session handlers
    document.getElementById('confirmCancel').addEventListener('click', async function() {
        const bookingId = document.getElementById('cancelBookingId').value;

        try {
            const response = await fetch('../../actions/cancelSession.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    booking_id: bookingId
                })
            });

            const data = await response.json();
            if (data.success) {
                showNotification('Session cancelled successfully!', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || 'Failed to cancel session');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification(error.message, 'error');
        }

        closeCancellationModal();
    });

    document.getElementById('cancelCancellation').addEventListener('click', closeCancellationModal);

    // Close modal when clicking outside
    document.getElementById('cancellationModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeCancellationModal();
        }
    });
}); 