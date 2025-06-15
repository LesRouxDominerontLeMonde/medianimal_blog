/**
 * Navbar Component - Menu Hamburger
 * Gère l'ouverture/fermeture du menu mobile
 */

// Fonctions utilitaires pour la navbar
function getNavbarElements() {
    return {
        toggle: document.getElementById('navbar-toggle'),
        menu: document.getElementById('navbar-menu')
    };
}

function closeMenu() {
    const { toggle, menu } = getNavbarElements();
    if (!toggle || !menu) return;
    
    menu.classList.remove('active');
    toggle.classList.remove('active');
    toggle.setAttribute('aria-expanded', 'false');
}

function openMenu() {
    const { toggle, menu } = getNavbarElements();
    if (!toggle || !menu) return;
    
    menu.classList.add('active');
    toggle.classList.add('active');
    toggle.setAttribute('aria-expanded', 'true');
}

// Gestionnaire d'événements avec délégation
document.addEventListener('click', function(e) {
    // Gestion du bouton hamburger
    if (e.target.closest('#navbar-toggle')) {
        e.preventDefault();
        e.stopPropagation();
        
        const { toggle, menu } = getNavbarElements();
        if (!toggle || !menu) return;
        
        const isOpen = menu.classList.contains('active');
        
        if (isOpen) {
            closeMenu();
        } else {
            openMenu();
        }
        return;
    }
    
    // Gestion des liens de navigation
    if (e.target.closest('.nav-link')) {
        closeMenu();
        
        // Double sécurité pour la fermeture
        setTimeout(() => {
            closeMenu();
        }, 50);
        return;
    }
    
    // Fermer le menu si on clique en dehors
    const { toggle, menu } = getNavbarElements();
    if (toggle && menu && menu.classList.contains('active')) {
        if (!toggle.contains(e.target) && !menu.contains(e.target)) {
            closeMenu();
        }
    }
});

// Fermer le menu avec la touche Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const { menu } = getNavbarElements();
        if (menu && menu.classList.contains('active')) {
            closeMenu();
        }
    }
});

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    const { toggle, menu } = getNavbarElements();
    
    if (!toggle || !menu) {
        return;
    }
    
    // S'assurer que le menu est fermé au chargement
    closeMenu();
});