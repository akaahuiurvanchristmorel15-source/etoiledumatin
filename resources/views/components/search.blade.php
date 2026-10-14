<div class="search-container" role="search">
    <label for="book-search-input" class="sr-only" style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0;">
        Rechercher par titre, auteur ou sujet
    </label>

    <input 
        type="text" 
        id="book-search-input" 
        class="search-input" 
        placeholder="Titles, author, or topics" 
        autocomplete="off" 
        spellcheck="false"
        aria-label="Recherche de livres"
    >

    <span class="search-icon" aria-hidden="true">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.3-4.3"></path>
        </svg>
    </span>

    <button type="button" id="search-clear-btn" style="display:none;position:absolute;right:1rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#8B857C;cursor:pointer;font-size:0.9rem;" aria-label="Effacer la recherche">
        ✕
    </button>
</div>
