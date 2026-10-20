/**
 * ==========================================================================
 * NSOROMMA — BROKS JAVASCRIPT VANILLA
 * - Recherche instantanée de livres
 * - Lecteur audio interactif avec suivi de progression
 * - Gestion du panier et animations
 * - Bascule d'onglets Books / AudioBooks
 * ==========================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    initSearch();
    initAudioPlayer();
    initCart();
    initTabs();
});

/**
 * 1. Recherche dynamique des livres côté client
 */
function initSearch() {
    const searchInput = document.getElementById('book-search-input');
    const searchClear = document.getElementById('search-clear-btn');
    const bookCards = document.querySelectorAll('.book-card');
    const noResultsMsg = document.getElementById('no-search-results');

    if (!searchInput) return;

    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase().trim();
        let matchCount = 0;

        if (searchClear) {
            searchClear.style.display = query.length > 0 ? 'block' : 'none';
        }

        bookCards.forEach(card => {
            const title = card.getAttribute('data-title') || '';
            const author = card.getAttribute('data-author') || '';

            if (title.includes(query) || author.includes(query)) {
                card.style.display = 'flex';
                matchCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (noResultsMsg) {
            noResultsMsg.style.display = (matchCount === 0 && query.length > 0) ? 'block' : 'none';
        }
    });

    if (searchClear) {
        searchClear.addEventListener('click', () => {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.focus();
        });
    }

    // Touche Escape pour réinitialiser
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && document.activeElement === searchInput) {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
        }
    });
}

/**
 * 2. Lecteur Audio Minimaliste
 */
function initAudioPlayer() {
    const playBtn = document.getElementById('audio-play-btn');
    const playIcon = document.getElementById('play-icon');
    const pauseIcon = document.getElementById('pause-icon');
    const progressFill = document.getElementById('audio-progress-fill');
    const progressWrap = document.getElementById('audio-progress-wrap');
    const currentTimeEl = document.getElementById('audio-current-time');
    const totalTimeEl = document.getElementById('audio-total-time');

    if (!playBtn) return;

    let isPlaying = false;
    let currentSeconds = 135; // 02:15
    const totalSeconds = 270; // 04:30
    let playInterval = null;

    function formatTime(sec) {
        const m = Math.floor(sec / 60);
        const s = Math.floor(sec % 60);
        return `${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
    }

    function updateProgress() {
        const pct = (currentSeconds / totalSeconds) * 100;
        if (progressFill) progressFill.style.width = `${pct}%`;
        if (currentTimeEl) currentTimeEl.textContent = formatTime(currentSeconds);
    }

    playBtn.addEventListener('click', () => {
        isPlaying = !isPlaying;

        if (isPlaying) {
            if (playIcon) playIcon.style.display = 'none';
            if (pauseIcon) pauseIcon.style.display = 'block';
            playBtn.setAttribute('aria-label', 'Mettre en pause');

            playInterval = setInterval(() => {
                currentSeconds++;
                if (currentSeconds >= totalSeconds) {
                    currentSeconds = 0;
                    isPlaying = false;
                    clearInterval(playInterval);
                    if (playIcon) playIcon.style.display = 'block';
                    if (pauseIcon) pauseIcon.style.display = 'none';
                }
                updateProgress();
            }, 1000);

            showToast("Lecture de l'extrait audio : « False Witness »");
        } else {
            clearInterval(playInterval);
            if (playIcon) playIcon.style.display = 'block';
            if (pauseIcon) pauseIcon.style.display = 'none';
            playBtn.setAttribute('aria-label', 'Lancer la lecture');
        }
    });

    // Clic pour se déplacer dans la barre de progression
    if (progressWrap) {
        progressWrap.addEventListener('click', (e) => {
            const rect = progressWrap.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const ratio = Math.max(0, Math.min(1, clickX / rect.width));
            currentSeconds = Math.floor(ratio * totalSeconds);
            updateProgress();
        });
    }

    updateProgress();
}

/**
 * 3. Panier d'achat interactif & Toasts
 */
function initCart() {
    const buyButtons = document.querySelectorAll('.buy-button');
    const cartCounter = document.getElementById('cart-counter');
    let cartTotal = 0;

    buyButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const bookTitle = button.getAttribute('data-book-title') || 'Livre';
            
            cartTotal++;
            if (cartCounter) {
                cartCounter.textContent = cartTotal;
                cartCounter.classList.add('bump');
                setTimeout(() => {
                    cartCounter.classList.remove('bump');
                }, 250);
            }

            // Effet visuel sur le bouton cliqué
            const originalText = button.innerHTML;
            button.classList.add('added');
            button.innerHTML = 'Ajouté ✓';

            setTimeout(() => {
                button.classList.remove('added');
                button.innerHTML = originalText;
            }, 1600);

            showToast(`« ${bookTitle} » ajouté à votre panier`);
        });
    });
}

/**
 * 4. Onglets Books / AudioBooks
 */
function initTabs() {
    const tabs = document.querySelectorAll('.nav-tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const tabName = tab.getAttribute('data-tab');
            if (tabName === 'audio') {
                showToast("Affichage de la collection d'AudioBooks");
            } else {
                showToast("Affichage de la collection des Livres Numériques");
            }
        });
    });
}

/**
 * Helper: Notification Toast éphémère
 */
function showToast(message) {
    let toast = document.getElementById('broks-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'broks-toast';
        toast.className = 'toast-msg';
        document.body.appendChild(toast);
    }

    toast.innerHTML = `<span>★</span><span>${message}</span>`;
    toast.classList.add('show');

    if (window.toastTimeout) {
        clearTimeout(window.toastTimeout);
    }

    window.toastTimeout = setTimeout(() => {
        toast.classList.remove('show');
    }, 2800);
}
