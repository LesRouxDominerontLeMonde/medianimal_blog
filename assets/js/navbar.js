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

// Fonction pour ajuster la position du banner selon la hauteur de la navbar
function adjustBannerPosition() {
    const navbar = document.querySelector('.navbar');
    const banner = document.querySelector('.blog-banner');
    const body = document.body;
    
    if (!navbar) {
        // Réessayer après un court délai si la navbar n'est pas encore trouvée
        setTimeout(adjustBannerPosition, 50);
        return;
    }
    
    // Mesurer la hauteur réelle de la navbar
    const navbarHeight = navbar.offsetHeight;
    
    // Vérifier que la hauteur est valide (parfois 0 si le CSS n'est pas encore appliqué)
    if (navbarHeight === 0) {
        setTimeout(adjustBannerPosition, 50);
        return;
    }
    
    // Appliquer le padding-top au body pour compenser la navbar fixed
    body.style.paddingTop = navbarHeight + 'px';
    
    // Si le banner existe, le positionner juste après la navbar
    if (banner) {
        banner.style.marginTop = '0px';
    }
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
        // Même sans éléments navbar, ajuster la position
        adjustBannerPosition();
        return;
    }
    
    // S'assurer que le menu est fermé au chargement
    closeMenu();
    
    // Ajuster la position du banner
    adjustBannerPosition();
});

// Réajuster au redimensionnement de la fenêtre
window.addEventListener('resize', function() {
    // Petit délai pour s'assurer que le DOM est stable après le resize
    setTimeout(adjustBannerPosition, 10);
});

// Réajuster après le chargement complet (polices, etc.)
window.addEventListener('load', adjustBannerPosition);

// Support pour Turbo (navigations sans rechargement)
document.addEventListener('turbo:load', adjustBannerPosition);
document.addEventListener('turbo:render', adjustBannerPosition);