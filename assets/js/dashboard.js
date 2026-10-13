const searchInput = document.getElementById('studentSearch');
const statusFilter = document.getElementById('statusFilter');
const attendanceRows = [...document.querySelectorAll('#attendanceRows tr')];
const emptyState = document.getElementById('emptyState');
const resultCount = document.getElementById('resultCount');
const menuToggle = document.getElementById('menuToggle');
const sidebarScrim = document.getElementById('sidebarScrim');
const compactMode = localStorage.getItem('absenku-compact-mode') === 'true';
const reducedMotion = localStorage.getItem('absenku-reduced-motion') === 'true';
document.body.classList.toggle('compact-ui', compactMode);
document.body.classList.toggle('reduce-motion', reducedMotion);

function filterStudents() {
    const searchTerm = searchInput.value.trim().toLocaleLowerCase('id');
    const selectedStatus = statusFilter.value;
    let visibleCount = 0;

    attendanceRows.forEach((row) => {
        const matchesSearch = row.textContent.toLocaleLowerCase('id').includes(searchTerm);
        const matchesStatus = selectedStatus === 'all' || row.dataset.status === selectedStatus;
        const isVisible = matchesSearch && matchesStatus;
        row.hidden = !isVisible;
        if (isVisible) visibleCount += 1;
    });

    emptyState.hidden = visibleCount > 0;
    if (attendanceRows.length > 0) {
        emptyState.querySelector('strong').textContent = 'Tidak ada siswa yang cocok';
        emptyState.querySelector('p').textContent = 'Coba ubah kata kunci atau filter status.';
    }
    resultCount.textContent = `Menampilkan ${visibleCount} dari ${attendanceRows.length} siswa`;
}

function setMenuOpen(isOpen) {
    document.body.classList.toggle('menu-open', isOpen);
    menuToggle.setAttribute('aria-expanded', String(isOpen));
    menuToggle.setAttribute('aria-label', isOpen ? 'Tutup menu' : 'Buka menu');
}

searchInput.addEventListener('input', filterStudents);
statusFilter.addEventListener('change', filterStudents);
menuToggle.addEventListener('click', () => setMenuOpen(menuToggle.getAttribute('aria-expanded') !== 'true'));
sidebarScrim.addEventListener('click', () => setMenuOpen(false));
document.querySelectorAll('.sidebar .nav-link').forEach((link) => {
    link.addEventListener('click', () => setMenuOpen(false));
});
document.addEventListener('keydown', (event) => {
    if (event.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
        event.preventDefault();
        searchInput.focus();
    }
    if (event.key === 'Escape') setMenuOpen(false);
});
