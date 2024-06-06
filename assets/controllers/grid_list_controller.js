import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['active'];

    connect() {
        this.url = new URL(window.location.href);
        this.viewType = this.url.searchParams.get('view') || null;
    }

    changeView(event) {
        const button = event.currentTarget;

        if (!button.classList.contains('active')) {
            this.viewType = button.id;
            this.url.searchParams.set('view', this.viewType);

            window.location.replace(this.url);
        }
    }
}
