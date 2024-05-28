import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['header'];

    connect() {
        const url = new URL(window.location.href);
        this.sortField = url.searchParams.get('sort') || null;
        this.sortOrder = url.searchParams.get('order') || 'asc';
        this.currentPage = url.searchParams.get('page') || 1;
    }

    sort(event) {
        const header = event.currentTarget;
        const field = header.dataset.field;

        console.log('sortField', this.sortField)
        console.log('field', field)

        if (this.sortField === field) {
            this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortField = field;
            this.sortOrder = 'asc';
        }

        this.updateIcons(header);
        this.navigateToSortedPage();
    }

    updateIcons(currentHeader) {
        this.headerTargets.forEach(header => {
            const icon = header.querySelector('svg');
            if (header !== currentHeader) {
                this.updateIcon(header);
            } else {
                icon.classList.remove('transform', 'rotate-180');
            }
        });
    }

    updateIcon(header) {
        const icon = header.querySelector('svg');
        if (this.sortOrder === 'asc') {
            icon.classList.add('transform', 'rotate-180');
        } else {
            icon.classList.remove('transform', 'rotate-180');
        }
    }

    navigateToSortedPage() {
        const url = new URL(window.location);
        url.searchParams.set('page', this.currentPage);
        url.searchParams.set('sort', this.sortField);
        url.searchParams.set('order', this.sortOrder);
        window.location.href = url.toString();
    }
}
