// Fonction utilitaire pour mélanger les ratios
function shuffleArray(array) {
    const shuffled = [...array];
    for (let i = shuffled.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
    }
    return shuffled;
}

// Fonction principale d'initialisation
function initializeMasonryShuffler() {
    const container = document.querySelector('.articles-masonry-grid');
    const cards = document.querySelectorAll('.masonry-card');
    const shuffleBtn = document.getElementById('shuffleBtn');
    
    if (!container || !cards.length || !shuffleBtn) {
        return;
    }
    
    const ratios = ['ratio-wide', 'ratio-tall', 'ratio-square'];
    let isAnimating = false;
    
    // Fonction pour assigner des ratios aléatoires
    function assignRandomRatios() {
        cards.forEach((card) => {
            // Supprimer les anciennes classes de ratio
            ratios.forEach(ratio => card.classList.remove(ratio));
            
            // Assigner un ratio aléatoire
            const randomRatio = ratios[Math.floor(Math.random() * ratios.length)];
            card.classList.add(randomRatio);
        });
    }
    
    // Fonction pour remettre le bouton à la normale
    function resetButton() {
        shuffleBtn.disabled = false;
        shuffleBtn.style.transform = 'rotate(0deg)';
        shuffleBtn.style.opacity = '1';
        isAnimating = false;
    }
    
    // Fonction de mélange des ratios
    function shuffleRatios() {
        if (isAnimating) return;
        
        isAnimating = true;
        
        // Rafraîchir les cartes
        const currentCards = document.querySelectorAll('.masonry-card');
        
        if (currentCards.length === 0) {
            resetButton();
            return;
        }
        
        // Désactiver le bouton temporairement
        shuffleBtn.disabled = true;
        shuffleBtn.style.transform = 'rotate(180deg)';
        shuffleBtn.style.opacity = '0.6';
        
        // Animer les cartes
        currentCards.forEach((card, index) => {
            setTimeout(() => {
                // Ajouter une classe temporaire pour l'animation
                card.style.transition = 'all 0.3s ease';
                card.style.transform = 'scale(0.95)';
                card.style.opacity = '0.8';
                
                setTimeout(() => {
                    // Supprimer les anciennes classes de ratio
                    ratios.forEach(ratio => card.classList.remove(ratio));
                    
                    // Assigner un nouveau ratio aléatoire
                    const randomRatio = ratios[Math.floor(Math.random() * ratios.length)];
                    card.classList.add(randomRatio);
                    
                    // Remettre l'animation normale
                    card.style.transform = 'scale(1)';
                    card.style.opacity = '1';
                    
                }, 150);
                
            }, index * 80);
        });
        
        // Remettre le bouton à la normale après toutes les animations
        const totalAnimationTime = (currentCards.length * 80) + 300;
        setTimeout(() => {
            resetButton();
        }, totalAnimationTime);
    }
    
    // Nettoyer les anciens event listeners
    const oldHandler = shuffleBtn.onclick;
    if (oldHandler) {
        shuffleBtn.removeEventListener('click', oldHandler);
    }
    
    // Ajouter l'événement au bouton
    shuffleBtn.addEventListener('click', function(e) {
        e.preventDefault();
        shuffleRatios();
    });
    
    // Assigner des ratios initiaux si les cartes n'en ont pas
    const hasRatios = Array.from(cards).some(card => 
        ratios.some(ratio => card.classList.contains(ratio))
    );
    
    if (!hasRatios) {
        assignRandomRatios();
    }
    
    // S'assurer que le bouton est dans un état normal
    resetButton();
}

// Initialiser dès que possible
function tryInitialize() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeMasonryShuffler);
    } else {
        initializeMasonryShuffler();
    }
}

// Initialiser immédiatement
tryInitialize();

// Réinitialiser lors des changements de page
window.addEventListener('load', () => {
    setTimeout(initializeMasonryShuffler, 100);
});

// Réinitialiser lors de la navigation
window.addEventListener('popstate', () => {
    setTimeout(initializeMasonryShuffler, 100);
});

// Réinitialiser périodiquement si nécessaire (fallback)
setInterval(() => {
    const shuffleBtn = document.getElementById('shuffleBtn');
    if (shuffleBtn && !shuffleBtn.onclick && !shuffleBtn.disabled) {
        initializeMasonryShuffler();
    }
}, 2000); 