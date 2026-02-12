import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['track', 'item', 'prev', 'next'];
    static values = {
        visible: { type: Number, default: 3 },
        gap: { type: Number, default: 24 } // Gap in pixels
    };

    connect() {
        this.currentIndex = 0;
        this.updateLayout();
        window.addEventListener('resize', this.updateLayout.bind(this));

        // Disable prev button initially
        this.updateButtons();
    }

    disconnect() {
        window.removeEventListener('resize', this.updateLayout.bind(this));
    }

    updateLayout() {
        // Calculate items per view based on viewport width
        // Mobile: 1, Tablet: 2, Desktop: 3 (default)
        if (window.innerWidth < 768) {
            this.visibleValue = 1;
        } else if (window.innerWidth < 1200) {
            this.visibleValue = 2;
        } else {
            this.visibleValue = 3;
        }

        const containerWidth = this.element.offsetWidth;
        // Total gap space = (visible items - 1) * gap
        const totalGap = (this.visibleValue - 1) * this.gapValue;
        // Item width = (container width - total gap space) / visible items
        const itemWidth = (containerWidth - totalGap) / this.visibleValue;

        this.itemTargets.forEach(item => {
            item.style.width = `${itemWidth}px`;
            // Reset flex basis/grow/shrink to be fixed width
            item.style.flex = `0 0 ${itemWidth}px`;
        });

        this.trackTarget.style.gap = `${this.gapValue}px`;
        this.slide();
    }

    next() {
        if (this.currentIndex < this.itemTargets.length - this.visibleValue) {
            this.currentIndex++;
            this.slide();
            this.updateButtons();
        }
    }

    prev() {
        if (this.currentIndex > 0) {
            this.currentIndex--;
            this.slide();
            this.updateButtons();
        }
    }

    slide() {
        const itemWidth = this.itemTargets[0].offsetWidth;
        const moveAmount = (itemWidth + this.gapValue) * this.currentIndex;
        this.trackTarget.style.transform = `translateX(-${moveAmount}px)`;
    }

    updateButtons() {
        this.prevTarget.disabled = this.currentIndex <= 0;
        this.nextTarget.disabled = this.currentIndex >= this.itemTargets.length - this.visibleValue;

        this.prevTarget.classList.toggle('opacity-50', this.prevTarget.disabled);
        this.nextTarget.classList.toggle('opacity-50', this.nextTarget.disabled);
    }
}
