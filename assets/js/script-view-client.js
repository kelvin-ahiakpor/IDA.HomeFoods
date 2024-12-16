function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-4 py-2 rounded-md text-white shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

function openEditModal(clientData) {
    originalData = {
        first_name: clientData.first_name,
        last_name: clientData.last_name,
        email: clientData.email
    };
    
    document.getElementById('editClientId').value = clientData.user_id;
    document.getElementById('editFirstName').value = clientData.first_name;
    document.getElementById('editLastName').value = clientData.last_name;
    document.getElementById('editEmail').value = clientData.email;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

document.getElementById('editClientForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        user_id: document.getElementById('editClientId').value,
        first_name: document.getElementById('editFirstName').value,
        last_name: document.getElementById('editLastName').value,
        email: document.getElementById('editEmail').value
    };

    if (formData.first_name === originalData.first_name && 
        formData.last_name === originalData.last_name && 
        formData.email === originalData.email) {
        showNotification('No changes made', 'error');
        setTimeout(() => closeEditModal(), 2500);
        return;
    }

    try {
        const response = await fetch('../../actions/updateClient.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();
        if (data.success) {
            showNotification('Client updated successfully!', 'success');
            closeEditModal();
            setTimeout(() => window.location.reload(), 2500);
        } else {
            throw new Error(data.message || 'Failed to update client');
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
});

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
