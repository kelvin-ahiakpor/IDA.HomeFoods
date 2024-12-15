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

document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('../actions/updateAccount.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.noChanges) {
                showNotification('No changes were made to your account.', 'info');
            } else {
                showNotification('Account updated successfully!', 'success');
                setTimeout(() => location.reload(), 3000); // Delay reload
            }
        } else {
            showNotification('Error updating account: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    });
}); 