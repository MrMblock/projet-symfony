import { Controller } from '@hotwired/stimulus';

// État global persistant entre les navigations Turbo
if (!window.__jukebox) {
    window.__jukebox = {
        audio: new Audio(),
        isPlaying: false,
        currentIndex: 0,
        songs: null,
        wasMinimized: false,
        volume: 1, // Default volume
    };
}

export default class extends Controller {
    static targets = ['panel', 'cover', 'title', 'playBtn', 'mini', 'miniCover', 'volume'];
    static values = {
        songs: Array
    };

    get jb() { return window.__jukebox; }

    connect() {
        // Mélanger seulement la première fois
        if (!this.jb.songs) {
            this.jb.songs = this._shuffle([...this.songsValue]);
            this.jb.audio.addEventListener('ended', () => this.next());
        }

        // Restaurer le volume
        this.jb.audio.volume = this.jb.volume;
        if (this.hasVolumeTarget) {
            this.volumeTarget.value = this.jb.volume;
        }

        // Restaurer l'état visuel
        this._updateDisplay();

        if (this.jb.isPlaying) {
            this.playBtnTarget.textContent = 'PAUSE';
            this.coverTarget.classList.add('spinning');
            if (this.hasMiniCoverTarget) this.miniCoverTarget.classList.add('spinning');
        }

        // Si la musique jouait, afficher le mini rond
        if (this.jb.isPlaying && this.jb.wasMinimized) {
            this.miniTarget.classList.add('jukebox-mini-visible');
        } else if (this.jb.isPlaying) {
            this.panelTarget.classList.add('jukebox-open');
        }
    }

    disconnect() {
        // Ne PAS arrêter l'audio ! Juste sauvegarder l'état visuel
        this.jb.wasMinimized = this.miniTarget.classList.contains('jukebox-mini-visible') ||
            !this.panelTarget.classList.contains('jukebox-open');
    }

    _shuffle(arr) {
        for (let i = arr.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [arr[i], arr[j]] = [arr[j], arr[i]];
        }
        return arr;
    }

    _updateDisplay() {
        const song = this.jb.songs[this.jb.currentIndex];
        this.coverTarget.src = song.cover;
        this.titleTarget.textContent = song.title;
        if (this.hasMiniCoverTarget) this.miniCoverTarget.src = song.cover;
    }

    toggle() {
        if (this.panelTarget.classList.contains('jukebox-open')) {
            this.minimize();
        } else {
            this.panelTarget.classList.add('jukebox-open');
            this.miniTarget.classList.remove('jukebox-mini-visible');

            if (!this.jb.isPlaying) {
                this.play();
            }
        }
    }

    minimize() {
        this.panelTarget.classList.remove('jukebox-open');
        this.miniTarget.classList.add('jukebox-mini-visible');
        if (this.jb.isPlaying) {
            this.miniCoverTarget.classList.add('spinning');
        }
    }

    restore() {
        this.miniTarget.classList.remove('jukebox-mini-visible');
        this.panelTarget.classList.add('jukebox-open');
    }

    close() {
        this.jb.audio.pause();
        this.jb.isPlaying = false;
        this.panelTarget.classList.remove('jukebox-open');
        this.miniTarget.classList.remove('jukebox-mini-visible');
        this.playBtnTarget.textContent = 'PLAY';
        this.coverTarget.classList.remove('spinning');
        if (this.hasMiniCoverTarget) this.miniCoverTarget.classList.remove('spinning');
    }

    play() {
        if (this.jb.isPlaying) {
            this.jb.audio.pause();
            this.jb.isPlaying = false;
            this.playBtnTarget.textContent = 'PLAY';
            this.coverTarget.classList.remove('spinning');
            if (this.hasMiniCoverTarget) this.miniCoverTarget.classList.remove('spinning');
        } else {
            const song = this.jb.songs[this.jb.currentIndex];
            if (this.jb.audio.src !== song.src) {
                this.jb.audio.src = song.src;
            }
            this.jb.audio.play();
            this.jb.isPlaying = true;
            this.playBtnTarget.textContent = 'PAUSE';
            this.coverTarget.classList.add('spinning');
            if (this.hasMiniCoverTarget) this.miniCoverTarget.classList.add('spinning');
        }
    }

    next() {
        this.jb.currentIndex = (this.jb.currentIndex + 1) % this.jb.songs.length;
        this._loadAndPlay();
    }

    prev() {
        this.jb.currentIndex = (this.jb.currentIndex - 1 + this.jb.songs.length) % this.jb.songs.length;
        this._loadAndPlay();
    }

    changeVolume() {
        this.jb.volume = parseFloat(this.volumeTarget.value);
        this.jb.audio.volume = this.jb.volume;
    }

    _loadAndPlay() {
        const song = this.jb.songs[this.jb.currentIndex];
        this.coverTarget.src = song.cover;
        this.titleTarget.textContent = song.title;
        if (this.hasMiniCoverTarget) this.miniCoverTarget.src = song.cover;
        this.jb.audio.src = song.src;
        if (this.jb.isPlaying) {
            this.jb.audio.play();
            this.coverTarget.classList.add('spinning');
            if (this.hasMiniCoverTarget) this.miniCoverTarget.classList.add('spinning');
        }
    }
}
