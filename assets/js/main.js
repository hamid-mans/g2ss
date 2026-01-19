function initSemanticUI() {
    $('.ui.modal').modal()
    $('.ui.dropdown').dropdown()
    $('.menu .item').tab();
}

document.addEventListener('turbo:load', initSemanticUI);
