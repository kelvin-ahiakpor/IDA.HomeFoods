let selectedBookingId = null;

function showCancellationModal(bookingId) {
    selectedBookingId = bookingId;
    document.getElementById('cancellationModal').classList.remove('hidden');
}

function closeCancellationModal() {
    document.getElementById('cancellationModal').classList.add('hidden');
    selectedBookingId = null;
}

async function cancelBooking() {
    if (!selectedBookingId) return;

    try {
        console.log('Attempting to cancel booking:', selectedBookingId);
        const response = await fetch('../../actions/cancelBooking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                booking_id: selectedBookingId
            })
        });

        if (!response.ok) {
            const text = await response.text();
            console.error('Response not OK:', text);
            throw new Error('Server returned error');
        }

        const data = await response.json();
        console.log('Response data:', data);

        if (data.success) {
            showNotification('Booking cancelled successfully!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(data.message || 'Error cancelling booking', 'error');
        }
    } catch (error) {
        console.error('Detailed error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    } finally {
        closeCancellationModal();
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

document.addEventListener('DOMContentLoaded', () => {
    // Tab functionality
    const tabs = document.querySelectorAll('.tab-btn');
    const bookingCards = document.querySelectorAll('.booking-card');

    console.log('Found tabs:', tabs.length);
    console.log('Found cards:', bookingCards.length);

    // Function to switch tabs
    function switchTab(activeTab) {
        const status = activeTab.dataset.tab;
        console.log('Switching to tab:', status);

        // Update tab styles
        tabs.forEach(tab => {
            if (tab === activeTab) {
                tab.classList.remove('border-transparent', 'text-gray-500');
                tab.classList.add('border-idafu-primary', 'text-idafu-primary');
            } else {
                tab.classList.remove('border-idafu-primary', 'text-idafu-primary');
                tab.classList.add('border-transparent', 'text-gray-500');
            }
        });

        // Filter bookings
        bookingCards.forEach(card => {
            const cardStatus = card.dataset.status.toLowerCase();
            console.log('Card status:', cardStatus, 'Looking for:', status);
            
            if (status === 'all' || cardStatus === status) {
                card.classList.remove('hidden');
                console.log('Showing card');
            } else {
                card.classList.add('hidden');
                console.log('Hiding card');
            }
        });

        // Update URL without page reload
        const url = new URL(window.location);
        url.searchParams.set('tab', status);
        window.history.pushState({}, '', url);
    }

    // Add click events to tabs
    tabs.forEach(tab => {
        tab.addEventListener('click', () => switchTab(tab));
    });

    // Set initial active tab
    const urlParams = new URLSearchParams(window.location.search);
    const activeTabFromUrl = urlParams.get('tab') || 'all';
    console.log('Initial tab:', activeTabFromUrl);
    
    const defaultTab = document.querySelector(`[data-tab="${activeTabFromUrl}"]`) || 
                      document.querySelector('[data-tab="all"]');
    if (defaultTab) {
        switchTab(defaultTab);
    }

    // Cancellation functionality
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



