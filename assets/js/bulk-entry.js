/**
 * 1. FONCTION DE GÉNÉRATION (Isolée)
 */
const dynamicAddLines = (quantity) => {
    const container = document.getElementById('units-container');
    if (!container) return;

    const prototype = container.dataset.prototype;
    if (!prototype) return;

    for (let i = 0; i < quantity; i++) {
        const index = container.querySelectorAll('.unit-row').length;
        const rowHtml = prototype.replace(/__name__/g, index);

        container.insertAdjacentHTML('beforeend', rowHtml);

        const lastRow = container.lastElementChild;
        const indexCol = lastRow.querySelector('.index-column');
        if (indexCol) indexCol.innerText = index + 1;
    }
};

/**
 * 2. INITIALISATION À CHAQUE PAGE (Turbo Load)
 */
window.addEventListener('turbo:load', () => {
    const container = document.getElementById('units-container');

    // On vérifie si on est sur la bonne page et si c'est vide
    if (container && container.querySelectorAll('.unit-row').length === 0) {
        dynamicAddLines(1);
    }
});

/**
 * 3. DÉLÉGATION DE CLIC GLOBALE (Sur window)
 * Cela permet de cliquer sur des boutons qui n'existaient pas au chargement initial
 */
window.addEventListener('click', (e) => {
    // Bouton GÉNÉRER
    const btnAdd = e.target.closest('#btn-generate');
    if (btnAdd) {
        e.preventDefault();
        const qtyInput = document.getElementById('bulk-qty');
        const qty = qtyInput ? (parseInt(qtyInput.value) || 1) : 1;
        dynamicAddLines(qty);
    }

    // Bouton SUPPRIMER
    const removeBtn = e.target.closest('.remove-line');
    if (removeBtn) {
        e.preventDefault();
        const container = document.getElementById('units-container');
        removeBtn.closest('tr').remove();

        if (container) {
            container.querySelectorAll('.unit-row').forEach((row, idx) => {
                const col = row.querySelector('.index-column');
                if (col) col.innerText = idx + 1;
            });
        }
    }
});