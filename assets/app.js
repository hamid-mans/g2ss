import './styles/app.css';
import './js/bulk-entry';
import * as Turbo from "@hotwired/turbo";

// Gestion du thème (définie à l'extérieur pour être réutilisable)
const applyTheme = (theme) => {
    document.documentElement.setAttribute('data-theme', theme);
    if (theme === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    localStorage.setItem('theme', theme);
};

// Initialisation du thème dès le premier chargement
//const savedTheme = localStorage.getItem('theme') || 'g2ss-light';
//applyTheme(savedTheme);

// Tout ce qui doit être ré-initialisé à chaque changement de page Turbo
document.addEventListener('turbo:load', () => {
    // 1. Gestion des lignes cliquables
    document.addEventListener('click', (e) => {
        const row = e.target.closest('.clickable-row');
        if (row && !e.target.closest('a, button, label, input, .modal')) {
            Turbo.visit(row.dataset.href);
        }
    });

    // 2. Gestion du switcher de thème
    const switcher = document.getElementById('theme-switcher');
    if (switcher) {
        // On supprime l'ancien listener s'il existe (pour éviter les déclenchements multiples)
        switcher.onclick = () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const nextTheme = currentTheme === 'g2ss-light' ? 'g2ss-dark' : 'g2ss-light';
            applyTheme(nextTheme);
        };
    }
});