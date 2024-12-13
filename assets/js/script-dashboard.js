let currentData = null;

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
        currentData = typeof consultantData === 'string' ? JSON.parse(consultantData) : consultantData;
        openModal('approvalModal');
    } catch (e) {
        console.error('Error parsing consultant data:', e);
    }
}

function openRejectModal(consultantData) {
    try {
        currentData = typeof consultantData === 'string' ? JSON.parse(consultantData) : consultantData;
        openModal('rejectModal');
    } catch (e) {
        console.error('Error parsing consultant data:', e);
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
function confirmActivate() {
    console.log('Activating:', currentData);
    closeActivateModal();
    location.reload();
}

function confirmDeactivate() {
    console.log('Deactivating:', currentData);
    closeDeactivateModal();
    location.reload();
}

function confirmApproval() {
    console.log('Approving consultant:', currentData);
    closeApprovalModal();
    location.reload();
}

function confirmRejection() {
    const reason = document.getElementById('rejectionReason')?.value || '';
    console.log('Rejecting consultant:', currentData, 'Reason:', reason);
    closeRejectModal();
    location.reload();
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
function approveCertification(certData) {
    const certification = JSON.parse(certData);
    if (confirm(`Approve certification: ${certification.name} from ${certification.issuer}?`)) {
        // Add API call here
        console.log('Approving certification:', certification);
        // Temporarily just reload the page
        location.reload();
    }
}

function approveSpecialization(specData) {
    const specialization = JSON.parse(specData);
    if (confirm(`Approve specialization: ${specialization.name}?`)) {
        // Add API call here
        console.log('Approving specialization:', specialization);
        // Temporarily just reload the page
        location.reload();
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
});