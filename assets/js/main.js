function initSemanticUI() {
    $('.ui.modal').modal()
    $('.ui.dropdown').dropdown()
}

document.addEventListener('turbo:load', initSemanticUI);
