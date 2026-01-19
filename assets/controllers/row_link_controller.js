import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static values = {
        url: String
    }

    connect() {
        this.element.style.cursor = 'pointer'
    }

    click(event) {
        if (event.target.closest('a')) {
            return
        }

        Turbo.visit(this.urlValue)
    }
}