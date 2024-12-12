// Profile Modal Toggle
const profileBtn = document.getElementById('profileBtn');
const profileModal = document.getElementById('profileModal');

profileBtn.addEventListener('click', () => {
    profileModal.classList.toggle('hidden');
});

document.addEventListener('click', (e) => {
    if (!profileModal.contains(e.target) && !profileBtn.contains(e.target)) {
        profileModal.classList.add('hidden');
    }
});

// Mobile Menu Toggle
const menuBtn = document.getElementById('menuBtn');
const mobileMenu = document.getElementById('mobileMenu');
const mobileMenuContent = document.getElementById('mobileMenuContent');

menuBtn.addEventListener('click', () => {
    mobileMenu.classList.toggle('hidden');
    mobileMenuContent.classList.toggle('-translate-x-full');
});

mobileMenu.addEventListener('click', (e) => {
    if (e.target === mobileMenu) {
        mobileMenu.classList.add('hidden');
        mobileMenuContent.classList.add('-translate-x-full');
    }
});