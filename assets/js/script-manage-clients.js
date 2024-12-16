function openDeactivateModal(userId) {
    window.currentUserId = userId;
    document.getElementById('deactivateModal').classList.remove('hidden');
}

function closeDeactivateModal() {
    document.getElementById('deactivateModal').classList.add('hidden');
}

function confirmDeactivate() {
    if (window.currentUserId) {
        toggleClientStatus(window.currentUserId, false);
        closeDeactivateModal();
    }
}

async function toggleClientStatus(userId, makeActive = false) {
    try {
        const response = await fetch('../../actions/toggleClientStatus.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId,
                active: makeActive ? 1 : 0
            })
        });

        const data = await response.json();
        if (data.success) {
            showNotification(
                `Client ${makeActive ? 'activated' : 'deactivated'} successfully!`, 
                'success'
            );
            setTimeout(() => window.location.reload(), 1500);
        } else {
            throw new Error(data.message || `Failed to ${makeActive ? 'activate' : 'deactivate'} client`);
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
} 