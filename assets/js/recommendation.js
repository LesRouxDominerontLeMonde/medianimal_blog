/**
 * Recommandation Component - Animation des numéros et extraits
 * Gère l'animation automatique des numéros 01, 02, 03 et leurs extraits correspondants
 * Permet le clic manuel sur les numéros avec arrêt/reprise de l'animation
 */

document.addEventListener('DOMContentLoaded', function() {
    const navNumbers = document.querySelectorAll('.recommandation-navigation .nav-number');
    const bannerNavNumbers = document.querySelectorAll('.recommandation-banner-navigation .nav-number');
    const articleExtracts = document.querySelectorAll('.article-extract');
    const bannerTitles = document.querySelectorAll('.recommandation-banner-title');

    
    if (navNumbers.length === 0 || articleExtracts.length === 0) {
        return; // Pas d'éléments trouvés, sortir
    }
    
    // URLs des images pour chaque index
    const imageUrls = [
        '/images/articles/1-6867b78ab4bc5573453580.jpg',
        '/images/articles/sailormouse-6867b30620dd8021359234.jpg',
        '/images/articles/pop-6867b2b7af065192175256.jpg'
    ];
    
    let currentActiveIndex = 0; // Index de l'élément actuellement actif (commence à 0 pour le premier)
    let autoAnimationInterval = null; // Référence pour l'interval d'animation automatique
    
    // Fonction pour changer l'élément actif (numéro + extrait + titre banner + image banner)
    function setActiveElement(index) {
        // Enlever les classes actives de tous les éléments
        navNumbers.forEach(num => num.classList.remove('activenumber'));
        bannerNavNumbers.forEach(num => num.classList.remove('activenumber'));
        articleExtracts.forEach(extract => extract.classList.remove('activeextract'));
        bannerTitles.forEach(title => title.classList.remove('activebannertext'));
        
        // Mettre à jour l'index actuel
        currentActiveIndex = index;
        
        // Ajouter les classes actives aux nouveaux éléments actifs
        navNumbers[currentActiveIndex].classList.add('activenumber');
        if (bannerNavNumbers[currentActiveIndex]) {
            bannerNavNumbers[currentActiveIndex].classList.add('activenumber');
        }
        articleExtracts[currentActiveIndex].classList.add('activeextract');
        if (bannerTitles[currentActiveIndex]) {
            bannerTitles[currentActiveIndex].classList.add('activebannertext');
        }
        
                // Changer l'URL de l'image du banner avec animation fluide
        if (imageUrls[currentActiveIndex]) {
            const currentActiveImage = document.querySelector('.recommandation-banner-image.active-image');
            const currentHiddenImage = document.querySelector('.recommandation-banner-image.hidden-image');
            
            if (currentActiveImage && currentHiddenImage) {
                // Préparer la nouvelle image (cachée)
                currentHiddenImage.src = imageUrls[currentActiveIndex];
                
                // Faire apparaître la nouvelle image et disparaître l'ancienne simultanément
                currentHiddenImage.style.opacity = '1';
                currentActiveImage.style.opacity = '0';
                
                // Après la transition, échanger les rôles
                setTimeout(() => {
                    // Échanger les classes
                    currentActiveImage.classList.remove('active-image');
                    currentActiveImage.classList.add('hidden-image');
                    currentHiddenImage.classList.remove('hidden-image');
                    currentHiddenImage.classList.add('active-image');
                    
                    // Réinitialiser les styles inline
                    currentActiveImage.style.opacity = '';
                    currentHiddenImage.style.opacity = '';
                }, 500); // 500ms correspond à la durée de la transition CSS
            }
        }
    }
    
    // Fonction pour l'animation automatique
    function changeActiveElement() {
        const nextIndex = (currentActiveIndex + 1) % Math.min(navNumbers.length, articleExtracts.length);
        setActiveElement(nextIndex);
    }
    
    // Fonction pour démarrer l'animation automatique
    function startAutoAnimation() {
        // Arrêter l'animation existante si elle existe
        if (autoAnimationInterval) {
            clearInterval(autoAnimationInterval);
        }
        // Démarrer l'animation avec un changement toutes les 10 secondes (10000ms)
        autoAnimationInterval = setInterval(changeActiveElement, 10000);
    }
    
    // Fonction pour arrêter l'animation automatique
    function stopAutoAnimation() {
        if (autoAnimationInterval) {
            clearInterval(autoAnimationInterval);
            autoAnimationInterval = null;
        }
    }
    
    // Ajouter les événements de clic sur les numéros
    navNumbers.forEach((navNumber, index) => {
        navNumber.addEventListener('click', function() {
            // Arrêter l'animation automatique
            stopAutoAnimation();
            
            // Activer l'élément cliqué
            setActiveElement(index);
            
            // Redémarrer l'animation automatique après 10 secondes
            setTimeout(() => {
                startAutoAnimation();
            }, 10000);
        });
        
        // Ajouter un style cursor pointer pour indiquer que c'est cliquable
        navNumber.style.cursor = 'pointer';
    });
    
    // Ajouter les événements de clic sur les numéros du banner aussi
    bannerNavNumbers.forEach((navNumber, index) => {
        navNumber.addEventListener('click', function() {
            // Arrêter l'animation automatique
            stopAutoAnimation();
            
            // Activer l'élément cliqué
            setActiveElement(index);
            
            // Redémarrer l'animation automatique après 10 secondes
            setTimeout(() => {
                startAutoAnimation();
            }, 10000);
        });
        
        // Ajouter un style cursor pointer pour indiquer que c'est cliquable
        navNumber.style.cursor = 'pointer';
    });
    
    // Démarrer l'animation automatique au chargement
    startAutoAnimation();
}); 