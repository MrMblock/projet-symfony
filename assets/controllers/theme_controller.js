import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['icon'];

    connect() {
        const theme = localStorage.getItem('theme') || 'light';
        this.updateIcon(theme);
        // Ensure the attribute is set correctly on connect (e.g. after navigation)
        document.documentElement.setAttribute('data-theme', theme);
    }

    toggle() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';

        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        this.updateIcon(newTheme);
    }

    updateIcon(theme) {
        if (theme === 'dark') {
            this.iconTarget.classList.remove('bi-moon');
            this.iconTarget.classList.add('bi-sun');
        } else {
            this.iconTarget.classList.remove('bi-sun');
            this.iconTarget.classList.add('bi-moon');
        }
    }
}
