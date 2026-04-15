import './stimulus_bootstrap.js';
import './styles/app.css';
import './js/bulk-entry';
import './js/charts';
import * as Turbo from "@hotwired/turbo";

// --- 1. GESTION DU THÈME (GLOBALE) ---
const applyTheme = (theme) => {
    document.documentElement.setAttribute('data-theme', theme);
    if (theme === 'g2ss-dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    localStorage.setItem('theme', theme);
};

// Initialisation immédiate (évite le flash blanc au chargement)
const savedTheme = localStorage.getItem('theme') || 'g2ss-light';
applyTheme(savedTheme);

// --- 2. DÉLÉGATION DE CLIC UNIQUE (SUR WINDOW) ---
// On définit cet écouteur UNE SEULE FOIS en dehors du turbo:load
window.addEventListener('click', (e) => {

    // A. Gestion des lignes cliquables (Redirection URL)
    const row = e.target.closest('.clickable-row');
    if (row && row.dataset.href && !e.target.closest('a, button, label, input, .modal, .dropdown-content')) {
        Turbo.visit(row.dataset.href);
        return;
    }

    // B. Gestion de l'ouverture des Modals via TR (nouveau)
    const modalTrigger = e.target.closest('[data-modal-trigger]');
    if (modalTrigger && !e.target.closest('a, button, label, input')) {
        const modalId = modalTrigger.dataset.modalTrigger;
        const checkbox = document.getElementById(modalId);
        if (checkbox) {
            checkbox.checked = true;
        }
        return;
    }

    // C. Switcher de thème (Délégation pour qu'il marche partout)
    const switcher = e.target.closest('#theme-switcher');
    if (switcher) {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const nextTheme = currentTheme === 'g2ss-light' ? 'g2ss-dark' : 'g2ss-light';
        applyTheme(nextTheme);
    }
});

document.addEventListener('turbo:load', () => {
    console.log("Page chargée via Turbo");
});