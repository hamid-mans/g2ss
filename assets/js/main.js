function initSemanticUI() {
    // Initialisation Fomantic UI
    $('.ui.mini.modal').modal();
    $('.ui.dropdown').dropdown();
    $('.menu .item').tab();

    // Toggle formulaire de recherche

    $('.searchModal').css('display', 'none')
}
$('.searchModal').css('display', 'none')
$('.searchButton').click(() => {
    console.log("ici")
    $('.searchModal').transition('slide down');
});

// Initialisation au chargement de la page Turbo
document.addEventListener('turbo:load', initSemanticUI);