function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-4 py-2 rounded-md text-white shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

function openDeactivateModal(userId) {
    window.currentUserId = userId;
    document.getElementById('deactivateModal').classList.remove('hidden');
}

function closeDeactivateModal() {
    document.getElementById('deactivateModal').classList.add('hidden');
}

function openActivateModal(userId) {
    window.currentUserId = userId;
    document.getElementById('activateModal').classList.remove('hidden');
}

function closeActivateModal() {
    document.getElementById('activateModal').classList.add('hidden');
}

function confirmDeactivate() {
    if (window.currentUserId) {
        toggleConsultantStatus(window.currentUserId, false);
        closeDeactivateModal();
    }
}

function confirmActivate() {
    if (window.currentUserId) {
        toggleConsultantStatus(window.currentUserId, true);
        closeActivateModal();
    }
}

async function toggleConsultantStatus(userId, makeActive = false) {
    try {
        const response = await fetch('../../actions/toggleConsultantStatus.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId,
                active: makeActive
            })
        });

        const data = await response.json();
        if (data.success) {
            showNotification(
                `Consultant ${makeActive ? 'activated' : 'deactivated'} successfully!`, 
                'success'
            );
            setTimeout(() => window.location.reload(), 1500);
        } else {
            throw new Error(data.message || `Failed to ${makeActive ? 'activate' : 'deactivate'} consultant`);
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

function openEditModal(consultant) {
    document.getElementById('editConsultantId').value = consultant.user_id;
    document.getElementById('editName').value = consultant.name;
    document.getElementById('editEmail').value = consultant.email;
    document.getElementById('editExpertise').value = consultant.expertise;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

document.getElementById('editConsultantForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        user_id: document.getElementById('editConsultantId').value,
        name: document.getElementById('editName').value,
        email: document.getElementById('editEmail').value,
        expertise: document.getElementById('editExpertise').value
    };

    try {
        const response = await fetch('../../actions/updateConsultant.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();
        if (data.success) {
            showNotification('Consultant updated successfully!', 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            throw new Error(data.message || 'Failed to update consultant');
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
});

function confirmDelete() {
    const userId = document.getElementById('editConsultantId').value;
    closeEditModal();
    openDeleteModal(userId);
}

function openDeleteModal(userId) {
    window.currentUserId = userId;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

function confirmDeleteFinal() {
    if (window.currentUserId) {
        deleteConsultant(window.currentUserId);
        closeDeleteModal();
    }
}

async function deleteConsultant(userId) {
    try {
        const response = await fetch('../../actions/deleteConsultant.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId
            })
        });

        const data = await response.json();
        if (data.success) {
            showNotification('Consultant deleted successfully!', 'success');
            setTimeout(() => window.location.href = 'manage_consultants.php', 1500);
        } else {
            throw new Error(data.message || 'Failed to delete consultant');
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
} 