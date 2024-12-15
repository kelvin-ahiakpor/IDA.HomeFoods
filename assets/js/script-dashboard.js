let currentData = null;
let bookingChart, revenueChart;

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

// Modal Element References
const modals = {
    activate: document.getElementById('activateModal'),
    deactivate: document.getElementById('deactivateModal'),
    approve: document.getElementById('approvalModal'),
    reject: document.getElementById('rejectModal'),
    edit: document.getElementById('editModal'),
    delete: document.getElementById('deleteModal')
};

// Modal Functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.remove('hidden');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.add('hidden');
    if (modalId === 'rejectModal') {
        const reasonInput = document.getElementById('rejectionReason');
        if (reasonInput) reasonInput.value = '';
    }
    currentData = null;
}

// Specific Modal Functions
function openActivateModal(userId, type = 'consultant') {
    currentData = { userId, type };
    document.getElementById('activateModal').classList.remove('hidden');
}

function openDeactivateModal(userId, type = 'consultant') {
    currentData = { userId, type };
    document.getElementById('deactivateModal').classList.remove('hidden');
}

function openApprovalModal(consultantData) {
    try {
        console.log('Opening approval modal with data:', consultantData);
        currentData = typeof consultantData === 'string' ? JSON.parse(consultantData) : consultantData;
        console.log('Parsed currentData:', currentData);
        const modal = document.getElementById('approvalModal');
        console.log('Modal element:', modal);
        modal.classList.remove('hidden');
    } catch (e) {
        console.error('Error in openApprovalModal:', e);
        showNotification('Error opening approval modal', 'error');
    }
}

function openRejectModal(consultantData) {
    try {
        console.log('Opening reject modal with data:', consultantData);
        currentData = typeof consultantData === 'string' ? JSON.parse(consultantData) : consultantData;
        console.log('Parsed currentData:', currentData);
        const modal = document.getElementById('rejectModal');
        console.log('Modal element:', modal);
        modal.classList.remove('hidden');
    } catch (e) {
        console.error('Error in openRejectModal:', e);
        showNotification('Error opening reject modal', 'error');
    }
}

// Close Modal Functions
function closeActivateModal() { closeModal('activateModal'); }
function closeDeactivateModal() { closeModal('deactivateModal'); }
function closeApprovalModal() { closeModal('approvalModal'); }
function closeRejectModal() { closeModal('rejectModal'); }
function closeEditModal() { closeModal('editModal'); }
function closeDeleteModal() { closeModal('deleteModal'); }

// Confirmation Functions
async function confirmActivate() {
    console.log('Activating:', currentData);
    closeActivateModal();
    location.reload();
}

function confirmDeactivate() {
    console.log('Deactivating:', currentData);
    closeDeactivateModal();
    location.reload();
}

async function confirmApproval() {
    try {
        console.log('Confirming approval with currentData:', currentData);
        
        if (!currentData || !currentData.user_id) {
            throw new Error('No consultant data available');
        }
        if (!currentData.application_id) {
            throw new Error('No application ID available');
        }

        const formData = new FormData();
        formData.append('application_id', currentData.application_id);
        formData.append('user_id', currentData.user_id);

        console.log('Sending approval request with:', {
            application_id: currentData.application_id,
            user_id: currentData.user_id
        });

        const response = await fetch('../../actions/approveConsultant.php', {
            method: 'POST',
            body: formData
        });

        console.log('Got response:', response);
        const result = await response.json();
        console.log('Parsed result:', result);
        
        if (result.success) {
            showNotification('Consultant application approved successfully');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.message || 'Failed to approve consultant');
        }
    } catch (error) {
        console.error('Error approving consultant:', error);
        showNotification(error.message, 'error');
    } finally {
        closeApprovalModal();
    }
}

async function confirmRejection() {
    try {
        if (!currentData || !currentData.user_id) {
            throw new Error('No consultant data available');
        }

        const reason = document.getElementById('rejectionReason')?.value;
        if (!reason?.trim()) {
            throw new Error('Please provide a reason for rejection');
        }

        const formData = new FormData();
        formData.append('application_id', currentData.application_id);
        formData.append('user_id', currentData.user_id);
        formData.append('reason', reason);

        const response = await fetch('../../actions/rejectConsultant.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        
        if (result.success) {
            showNotification('Consultant application rejected');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.message || 'Failed to reject consultant');
        }
    } catch (error) {
        console.error('Error rejecting consultant:', error);
        showNotification(error.message, 'error');
    } finally {
        closeRejectModal();
    }
}

function openEditModal(data) {
    document.getElementById('editModal').classList.remove('hidden');
    
    // Check if it's a client or consultant based on available fields
    if (data.first_name) { // Client
        document.getElementById('editClientId').value = data.user_id;
        document.getElementById('editFirstName').value = data.first_name;
        document.getElementById('editLastName').value = data.last_name;
        document.getElementById('editEmail').value = data.email;
    } else { // Consultant
        document.getElementById('editConsultantId').value = data.email;
        document.getElementById('editName').value = data.name;
        document.getElementById('editEmail').value = data.email;
        document.getElementById('editExpertise').value = data.expertise;
    }
}

function confirmDelete() {
    closeEditModal();
    document.getElementById('deleteModal').classList.remove('hidden');
}

// Add these functions for handling updates approval/rejection
async function approveCertification(certData) {
    try {
        const certification = typeof certData === 'string' ? JSON.parse(certData) : certData;
        
        const formData = new FormData();
        formData.append('type', 'certification');
        formData.append('user_id', currentData.user_id);
        formData.append('name', certification.name);

        const response = await fetch('../../actions/approveConsultantUpdate.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        
        if (result.success) {
            showNotification('Certification approved successfully');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.message || 'Failed to approve certification');
        }
    } catch (error) {
        console.error('Error approving certification:', error);
        showNotification(error.message, 'error');
    }
}

async function approveSpecialization(specData) {
    try {
        const specialization = typeof specData === 'string' ? JSON.parse(specData) : specData;
        
        const formData = new FormData();
        formData.append('type', 'specialization');
        formData.append('user_id', currentData.user_id);
        formData.append('name', specialization.name);

        const response = await fetch('../../actions/approveConsultantUpdate.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        
        if (result.success) {
            showNotification('Specialization approved successfully');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.message || 'Failed to approve specialization');
        }
    } catch (error) {
        console.error('Error approving specialization:', error);
        showNotification(error.message, 'error');
    }
}

function rejectUpdate(type, name) {
    if (confirm(`Reject this ${type}: ${name}?`)) {
        // Add API call here
        console.log('Rejecting', type, name);
        // Temporarily just reload the page
        location.reload();
    }
}

// Add these client management functions
function deactivateClient(clientId) {
    // Store the client ID for the confirmation
    document.getElementById('deactivateModal').dataset.clientId = clientId;
    document.getElementById('deactivateModal').classList.remove('hidden');
}

function activateClient(clientId) {
    document.getElementById('activateModal').dataset.clientId = clientId;
    document.getElementById('activateModal').classList.remove('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    // Profile Modal Toggle
    const profileBtn = document.getElementById('profileBtn');
    const profileModal = document.getElementById('profileModal');

    profileBtn?.addEventListener('click', () => {
        profileModal?.classList.toggle('hidden');
    });

    document.addEventListener('click', (e) => {
        if (profileModal && !profileModal.contains(e.target) && !profileBtn.contains(e.target)) {
            profileModal.classList.add('hidden');
        }
    });

    // Mobile Menu Toggle
    const menuBtn = document.getElementById('menuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileMenuContent = document.getElementById('mobileMenuContent');

    menuBtn?.addEventListener('click', () => {
        mobileMenu?.classList.toggle('hidden');
        mobileMenuContent?.classList.toggle('-translate-x-full');
    });

    mobileMenu?.addEventListener('click', (e) => {
        if (e.target === mobileMenu) {
            mobileMenu.classList.add('hidden');
            mobileMenuContent.classList.add('-translate-x-full');
        }
    });

    // Tab Switching Logic
    const tabs = document.querySelectorAll('.tab-btn');
    const consultantsTab = document.querySelector('[data-tab="consultants"]');
    const pendingRequestsTab = document.querySelector('[data-tab="pending"]');
    const searchResultsTab = document.querySelector('[data-tab="search"]');

    const consultantsContent = document.getElementById('consultantsContent');
    const pendingContent = document.getElementById('pendingContent');
    const searchContent = document.getElementById('searchContent');

    // Function to switch tabs
    function switchTab(activeTab, activeContent) {
        // Remove active states from all tabs
        tabs.forEach(tab => {
            tab.classList.remove('text-idafu-primary', 'border-b-2', 'border-idafu-primary');
            tab.classList.add('text-gray-500');
        });

        // Hide all content
        [consultantsContent, pendingContent, searchContent].forEach(content => {
            if (content) content.classList.add('hidden');
        });

        // Activate selected tab
        activeTab.classList.remove('text-gray-500');
        activeTab.classList.add('text-idafu-primary', 'border-b-2', 'border-idafu-primary');
        
        // Show selected content
        if (activeContent) activeContent.classList.remove('hidden');
    }

    // Add click events to tabs
    consultantsTab?.addEventListener('click', () => {
        switchTab(consultantsTab, consultantsContent);
    });

    pendingRequestsTab?.addEventListener('click', () => {
        switchTab(pendingRequestsTab, pendingContent);
    });

    searchResultsTab?.addEventListener('click', () => {
        switchTab(searchResultsTab, searchContent);
    });

    // Initialize with consultants tab active
    if (consultantsTab && consultantsContent) {
        switchTab(consultantsTab, consultantsContent);
    }

    // Search Functionality
    const searchInput = document.querySelector('input[placeholder*="Search"]');
    
    searchInput?.addEventListener('input', (e) => {
        const query = e.target.value.trim();
            if (query.length > 0) {
            // Show and activate search tab
            searchResultsTab?.classList.remove('hidden');
            switchTab(searchResultsTab, searchContent);
            
            // Update search results message
            const searchResultsBody = searchContent.querySelector('tbody');
            if (searchResultsBody) {
                searchResultsBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center py-4 text-xs sm:text-sm">
                            Searching for "${query}"...
                        </td>
                    </tr>
                `;
                
                // Here you would typically make an API call to search
                // For now, we'll just simulate a search with the mock data
                setTimeout(() => {
                    // Mock search results (you'll replace this with actual API call)
                    const results = [...activeConsultants, ...inactiveConsultants, ...pendingRequests].filter(consultant => 
                        consultant.name.toLowerCase().includes(query.toLowerCase()) ||
                        consultant.email.toLowerCase().includes(query.toLowerCase()) ||
                        consultant.expertise.toLowerCase().includes(query.toLowerCase())
                    );
                    
                    if (results.length > 0) {
                        searchResultsBody.innerHTML = results.map(consultant => `
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-2 sm:px-4 py-3">${consultant.name}</td>
                                <td class="px-2 sm:px-4 py-3">${consultant.email}</td>
                                <td class="px-2 sm:px-4 py-3">${consultant.expertise}</td>
                                <td class="px-2 sm:px-4 py-3 space-x-2">
                                    <button class="text-idafu-primary hover:bg-idafu-lightBlue px-2 py-1 rounded transition-colors duration-200"
                                            onclick="window.open('./view_consultant.php?id=${encodeURIComponent(consultant.email)}, '_blank')">
                                        View
                                    </button>
                                    <button class="text-idafu-primary hover:bg-idafu-lightBlue px-2 py-1 rounded transition-colors duration-200">
                                        Edit
                                    </button>
                                    <button class="text-idafu-accentDeeper hover:bg-red-50 px-2 py-1 rounded transition-colors duration-200">
                                        Deactivate
                                    </button>
                                </td>
                            </tr>
                        `).join('');
                    } else {
                        searchResultsBody.innerHTML = `
                            <tr>
                                <td colspan="4" class="text-center py-4 text-xs sm:text-sm">
                                    No results found for "${query}"
                                </td>
                            </tr>
                        `;
                    }
                }, 300); // Simulate API delay
            }
        } else {
            // Hide search tab and switch back to consultants tab
            searchResultsTab?.classList.add('hidden');
            switchTab(consultantsTab, consultantsContent);
        }
    });

    // Form submission handler
    document.getElementById('editConsultantForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Saving changes...');
        closeEditModal();
    });

    // Close modals when clicking outside
    window.addEventListener('click', function(e) {
        const modals = [
            { element: document.getElementById('editModal'), close: closeEditModal },
            { element: document.getElementById('deactivateModal'), close: closeDeactivateModal },
            { element: document.getElementById('deleteModal'), close: closeDeleteModal },
            { element: document.getElementById('activateModal'), close: closeActivateModal }
        ];

        modals.forEach(({ element, close }) => {
            if (e.target === element) {
                close();
            }
        });
    });

    // Update the DOMContentLoaded event listener to include the edit form handler
    document.getElementById('editClientForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Saving client changes...');
        closeEditModal();
        // Temporarily just reload the page
        location.reload();
    });

    // Initialize charts
    const bookingChartElement = document.getElementById('bookingTrendsChart');
    const revenueChartElement = document.getElementById('revenueTrendsChart');
    let bookingChart, revenueChart;

    if (bookingChartElement && revenueChartElement) {
        bookingChart = new Chart(bookingChartElement, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Total Bookings',
                    data: [65, 78, 90, 85, 95, 110],
                    borderColor: '#435F6F',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        revenueChart = new Chart(revenueChartElement, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Revenue',
                    data: [8500, 9200, 11000, 10500, 12500, 13800],
                    backgroundColor: 'rgba(67, 95, 111, 0.2)',
                    borderColor: '#435F6F',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Handle window resize
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                bookingChart?.resize();
                revenueChart?.resize();
            }, 250);
        });
    }
});