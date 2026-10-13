const pageSearch = document.getElementById('pageSearch');
const pageRows = [...document.querySelectorAll('#pageRows tr')];
const pageEmptyState = document.getElementById('emptyState');
const pageResultCount = document.getElementById('resultCount');
const pageMenuToggle = document.getElementById('menuToggle');
const pageSidebarScrim = document.getElementById('sidebarScrim');
const compactMode = localStorage.getItem('absenku-compact-mode') === 'true';
const reducedMotion = localStorage.getItem('absenku-reduced-motion') === 'true';
document.body.classList.toggle('compact-ui', compactMode);
document.body.classList.toggle('reduce-motion', reducedMotion);

if (pageSearch && pageRows.length > 0) {
    pageSearch.addEventListener('input', () => {
        const searchTerm = pageSearch.value.trim().toLocaleLowerCase('id');
        let visibleCount = 0;

        pageRows.forEach((row) => {
            const isVisible = row.textContent.toLocaleLowerCase('id').includes(searchTerm);
            row.hidden = !isVisible;
            if (isVisible) visibleCount += 1;
        });

        if (pageEmptyState) {
            pageEmptyState.hidden = visibleCount > 0;
            pageEmptyState.querySelector('strong').textContent = 'Siswa tidak ditemukan';
            pageEmptyState.querySelector('p').textContent = 'Coba kata kunci yang berbeda.';
        }
        if (pageResultCount) pageResultCount.textContent = `Menampilkan ${visibleCount} dari ${pageRows.length} siswa`;
    });
}

function setPageMenuOpen(isOpen) {
    document.body.classList.toggle('menu-open', isOpen);
    pageMenuToggle.setAttribute('aria-expanded', String(isOpen));
    pageMenuToggle.setAttribute('aria-label', isOpen ? 'Tutup menu' : 'Buka menu');
}

if (pageMenuToggle && pageSidebarScrim) {
    pageMenuToggle.addEventListener('click', () => setPageMenuOpen(pageMenuToggle.getAttribute('aria-expanded') !== 'true'));
    pageSidebarScrim.addEventListener('click', () => setPageMenuOpen(false));
    document.querySelectorAll('.sidebar .nav-link').forEach((link) => {
        link.addEventListener('click', () => setPageMenuOpen(false));
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setPageMenuOpen(false);
    });
}

document.addEventListener('keydown', (event) => {
    if (event.key === '/' && pageSearch && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
        event.preventDefault();
        pageSearch.focus();
    }
});
