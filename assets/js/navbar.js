/**
 * Navbar Component - Menu Hamburger
 * Gère l'ouverture/fermeture du menu mobile
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('🍔 Navbar JS chargé !');
    
    const navbarToggle = document.getElementById('navbar-toggle');
    const navbarMenu = document.getElementById('navbar-menu');
    
    console.log('navbarToggle:', navbarToggle);
    console.log('navbarMenu:', navbarMenu);
    
    // Vérification que les éléments existent
    if (!navbarToggle || !navbarMenu) {
        console.error('❌ Éléments navbar introuvables !');
        return;
    }
    
    console.log('✅ Éléments navbar trouvés !');
    
    // Toggle du menu hamburger
    navbarToggle.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('🍔 Clic sur le hamburger !');
        
        navbarMenu.classList.toggle('active');
        navbarToggle.classList.toggle('active');
        
        console.log('Menu actif:', navbarMenu.classList.contains('active'));
        
        // Accessibilité - mise à jour de l'attribut aria-expanded
        const isExpanded = navbarMenu.classList.contains('active');
        navbarToggle.setAttribute('aria-expanded', isExpanded);
    });
    
    // Fermer le menu quand on clique sur un lien
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            navbarMenu.classList.remove('active');
            navbarToggle.classList.remove('active');
            navbarToggle.setAttribute('aria-expanded', 'false');
        });
    });
    
    // Fermer le menu avec la touche Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && navbarMenu.classList.contains('active')) {
            navbarMenu.classList.remove('active');
            navbarToggle.classList.remove('active');
            navbarToggle.setAttribute('aria-expanded', 'false');
        }
    });
    
    // Fermer le menu si on clique en dehors
    document.addEventListener('click', function(e) {
        if (!navbarToggle.contains(e.target) && !navbarMenu.contains(e.target)) {
            navbarMenu.classList.remove('active');
            navbarToggle.classList.remove('active');
            navbarToggle.setAttribute('aria-expanded', 'false');
        }
    });
});