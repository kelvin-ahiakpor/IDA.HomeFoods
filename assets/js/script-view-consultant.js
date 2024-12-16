let originalData = {};

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-4 py-2 rounded-md text-white shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

function openEditModal(consultant) {
    originalData = {
        name: consultant.name,
        email: consultant.email,
        expertise: consultant.expertise,
        bio: consultant.bio || ''
    };
    
    document.getElementById('editConsultantId').value = consultant.user_id;
    document.getElementById('editName').value = consultant.name;
    document.getElementById('editEmail').value = consultant.email;
    document.getElementById('editExpertise').value = consultant.expertise;
    document.getElementById('editBio').value = consultant.bio || '';
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
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
        toggleConsultantStatus(window.currentUserId, false);
        closeDeactivateModal();
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

document.getElementById('editConsultantForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        user_id: document.getElementById('editConsultantId').value,
        name: document.getElementById('editName').value,
        email: document.getElementById('editEmail').value,
        expertise: document.getElementById('editExpertise').value,
        bio: document.getElementById('editBio').value
    };

    if (formData.name === originalData.name && 
        formData.email === originalData.email && 
        formData.expertise === originalData.expertise &&
        formData.bio === originalData.bio) {
        showNotification('No changes made', 'error');
        setTimeout(() => closeEditModal(), 2500);
        return;
    }

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
            closeEditModal();
            setTimeout(() => window.location.reload(), 2500);
        } else {
            throw new Error(data.message || 'Failed to update consultant');
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
}); 
