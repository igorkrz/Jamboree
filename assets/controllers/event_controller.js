import { Controller } from '@hotwired/stimulus';
import axios from 'axios';

export default class extends Controller {
    static targets = ['parent'];

    add(event) {
        const eventToAdd = event.currentTarget;
        const path = eventToAdd.dataset.path;

        axios.post(path);
        eventToAdd.classList.add('hidden');
    }

    remove(event) {
        const eventToRemove = event.currentTarget;
        const path = eventToRemove.dataset.path;
        const id = eventToRemove.id;

        const parent = this.parentTargets.filter(target => target.id === id);

        if (parent.length !== 1) {
            return;
        }

        axios.delete(path)
        parent[0].classList.add('hidden');
    }
}
