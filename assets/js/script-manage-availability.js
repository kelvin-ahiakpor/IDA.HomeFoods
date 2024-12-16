document.addEventListener('DOMContentLoaded', function() {
    const availabilityForm = document.getElementById('availabilityForm');
    const daySelect = document.getElementById('day');
    const startTimeInput = document.getElementById('start_time');
    const previewDiv = document.getElementById('availabilityPreview');
    const submitButton = availabilityForm.querySelector('button[type="submit"]');
    const officeHoursText = document.querySelector('.office-hours-text');
    const deleteModal = document.getElementById('deleteModal');
    const editModal = document.getElementById('editModal');
    const editForm = document.getElementById('editAvailabilityForm');
    const editAvailabilityId = document.getElementById('editAvailabilityId');
    const editStartTime = document.getElementById('editStartTime');
    let currentAvailabilityId = null;

    function validateOfficeHours(time) {
        if (!time) return true; // Don't show error if no time selected
        
        const startTime = new Date(`2000-01-01T${time}`);
        const endTime = new Date(startTime.getTime() + 3600000); // Add 1 hour in milliseconds
        
        const officeStart = new Date(`2000-01-01T09:00:00`);
        const officeEnd = new Date(`2000-01-01T21:00:00`);

        const isValid = startTime >= officeStart && endTime <= officeEnd;
        
        // Update UI based on validation
        officeHoursText.className = `mt-1 text-xs ${isValid ? 'text-gray-500' : 'text-red-500 font-medium'}`;
        submitButton.disabled = !isValid;
        
        return isValid;
    }

    startTimeInput.addEventListener('input', function() {
        validateOfficeHours(this.value);
    });

    function updatePreview() {
        const selectedDay = daySelect.value;
        const startTime = startTimeInput.value;
        
        if (!selectedDay && !startTime) {
            previewDiv.textContent = '';
            return;
        }

        // Start building the message
        let message = 'You are setting availability for ';

        if (selectedDay) {
            const date = getNextDayOccurrence(selectedDay);
            message += `${selectedDay}, ${formatDate(date)}`;
        }

        message += ' from ';

        if (startTime) {
            const endTime = addHourToTime(startTime);
            message += `${formatTime(startTime)} to ${formatTime(endTime)}`;
        } else {
            message += '__ AM to __ PM';
        }

        previewDiv.textContent = message;
    }

    // Make sure both inputs trigger the preview update
    daySelect.addEventListener('change', updatePreview);
    startTimeInput.addEventListener('change', updatePreview);
    // Also trigger on input for more reactivity
    startTimeInput.addEventListener('input', updatePreview);

    availabilityForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Get the date for the selected day
        const selectedDay = daySelect.value;
        const date = getNextDayOccurrence(selectedDay);
        
        const formData = new FormData();
        formData.append('date', formatDateForDB(date));
        formData.append('start_time', startTimeInput.value);
        
        try {
            const response = await fetch('../../actions/addAvailability.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showNotification('Availability added successfully!', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showNotification(data.message || 'Error adding availability', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('An error occurred. Please try again.', 'error');
        }
    });

    // Define deleteAvailability function in the global scope
    window.deleteAvailability = function(availabilityId) {
        currentAvailabilityId = availabilityId;
        deleteModal.classList.remove('hidden');
    };

    function closeDeleteModal() {
        deleteModal.classList.add('hidden');
        currentAvailabilityId = null;
    }

    window.confirmDelete = async function() {
        if (!currentAvailabilityId) return;

        try {
            const response = await fetch('../../actions/deleteAvailability.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ availability_id: currentAvailabilityId })
            });

            const data = await response.json();

            if (data.success) {
                showNotification('Availability deleted successfully!', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showNotification(data.message || 'Error deleting availability', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('An error occurred. Please try again.', 'error');
        } finally {
            closeDeleteModal();
        }
    }

    document.addEventListener('click', function(event) {
        if (event.target === deleteModal) {
            closeDeleteModal();
        }
    });

    // Add edit functions
    window.editAvailability = function(id, date, startTime) {
        editAvailabilityId.value = id;
        editStartTime.value = startTime;
        editModal.classList.remove('hidden');
    };

    function closeEditModal() {
        editModal.classList.add('hidden');
        editForm.reset();
    }

    editForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const response = await fetch('../../actions/editAvailability.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    availability_id: editAvailabilityId.value,
                    start_time: editStartTime.value
                })
            });

            const data = await response.json();

            if (data.success) {
                showNotification('Availability updated successfully!', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showNotification(data.message || 'Error updating availability', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('An error occurred. Please try again.', 'error');
        } finally {
            closeEditModal();
        }
    });
});

// Helper functions
function getNextDayOccurrence(dayName) {
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const today = new Date();
    const todayDay = today.getDay();
    const targetDay = days.indexOf(dayName);
    
    let daysUntilTarget = targetDay - todayDay;
    if (daysUntilTarget < 0) {
        daysUntilTarget += 7;
    }
    
    const targetDate = new Date();
    targetDate.setDate(today.getDate() + daysUntilTarget);
    return targetDate;
}

function formatDate(date) {
    return date.toLocaleDateString('en-US', { 
        month: 'long', 
        day: 'numeric', 
        year: 'numeric' 
    });
}

function formatDateForDB(date) {
    return date.toISOString().split('T')[0];
}

function formatTime(timeString) {
    return new Date(`2000-01-01T${timeString}`).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: 'numeric',
        hour12: true
    });
}

function addHourToTime(timeString) {
    const date = new Date(`2000-01-01T${timeString}`);
    date.setHours(date.getHours() + 1);
    return date.toTimeString().slice(0, 5);
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
