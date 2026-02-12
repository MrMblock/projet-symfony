import { Controller } from '@hotwired/stimulus';
import * as THREE from 'three';

export default class extends Controller {
    static targets = ['canvas'];

    connect() {
        this.scene = new THREE.Scene();
        this.clock = new THREE.Clock();
        this.meshes = [];
        this.mouse = { x: 0, y: 0 };

        this._initRenderer();
        this._initCamera();
        this._initLights();
        this._initShapes();
        this._initParticles();

        window.addEventListener('resize', this._onResize);
        window.addEventListener('mousemove', this._onMouseMove);

        this._animate();
    }

    disconnect() {
        window.removeEventListener('resize', this._onResize);
        window.removeEventListener('mousemove', this._onMouseMove);
        cancelAnimationFrame(this._raf);
        this.renderer.dispose();
    }

    /* ── Setup ── */

    _initRenderer() {
        this.renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        this.renderer.setSize(window.innerWidth, window.innerHeight);
        this.renderer.setClearColor(0x000000, 0);
        this.canvasTarget.appendChild(this.renderer.domElement);
    }

    _initCamera() {
        this.camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 100);
        this.camera.position.z = 20;
    }

    _initLights() {
        const ambient = new THREE.AmbientLight(0x8b5cf6, 0.4);
        this.scene.add(ambient);

        const point1 = new THREE.PointLight(0x6366f1, 1.5, 50);
        point1.position.set(10, 10, 10);
        this.scene.add(point1);

        const point2 = new THREE.PointLight(0xa855f7, 1.2, 50);
        point2.position.set(-10, -5, 8);
        this.scene.add(point2);
    }

    _initShapes() {
        const geometries = [
            new THREE.IcosahedronGeometry(1, 0),
            new THREE.OctahedronGeometry(1, 0),
            new THREE.TetrahedronGeometry(1, 0),
            new THREE.DodecahedronGeometry(0.8, 0),
            new THREE.TorusGeometry(0.7, 0.25, 8, 16),
        ];

        const colors = [0x6366f1, 0x818cf8, 0xa855f7, 0xc084fc, 0x7c3aed];

        for (let i = 0; i < 14; i++) {
            const geom = geometries[i % geometries.length];
            const color = colors[i % colors.length];

            const mat = new THREE.MeshPhongMaterial({
                color,
                wireframe: true,
                transparent: true,
                opacity: 0.35,
            });

            const mesh = new THREE.Mesh(geom, mat);
            const scale = 0.5 + Math.random() * 1.2;
            mesh.scale.set(scale, scale, scale);
            mesh.position.set(
                (Math.random() - 0.5) * 30,
                (Math.random() - 0.5) * 20,
                (Math.random() - 0.5) * 15
            );

            mesh.userData = {
                rotSpeed: { x: (Math.random() - 0.5) * 0.4, y: (Math.random() - 0.5) * 0.4 },
                floatSpeed: 0.3 + Math.random() * 0.5,
                floatOffset: Math.random() * Math.PI * 2,
                baseY: mesh.position.y,
            };

            this.scene.add(mesh);
            this.meshes.push(mesh);
        }
    }

    _initParticles() {
        const count = 120;
        const positions = new Float32Array(count * 3);
        for (let i = 0; i < count; i++) {
            positions[i * 3] = (Math.random() - 0.5) * 40;
            positions[i * 3 + 1] = (Math.random() - 0.5) * 30;
            positions[i * 3 + 2] = (Math.random() - 0.5) * 20;
        }

        const geom = new THREE.BufferGeometry();
        geom.setAttribute('position', new THREE.BufferAttribute(positions, 3));

        const mat = new THREE.PointsMaterial({
            color: 0x818cf8,
            size: 0.08,
            transparent: true,
            opacity: 0.6,
        });

        this.particles = new THREE.Points(geom, mat);
        this.scene.add(this.particles);
    }

    /* ── Loop ── */

    _animate = () => {
        this._raf = requestAnimationFrame(this._animate);
        const t = this.clock.getElapsedTime();

        for (const mesh of this.meshes) {
            const d = mesh.userData;
            mesh.rotation.x += d.rotSpeed.x * 0.01;
            mesh.rotation.y += d.rotSpeed.y * 0.01;
            mesh.position.y = d.baseY + Math.sin(t * d.floatSpeed + d.floatOffset) * 0.8;
        }

        this.particles.rotation.y = t * 0.02;
        this.particles.rotation.x = t * 0.01;

        this.camera.position.x += (this.mouse.x * 2 - this.camera.position.x) * 0.02;
        this.camera.position.y += (this.mouse.y * 2 - this.camera.position.y) * 0.02;
        this.camera.lookAt(this.scene.position);

        this.renderer.render(this.scene, this.camera);
    };

    /* ── Events ── */

    _onResize = () => {
        this.camera.aspect = window.innerWidth / window.innerHeight;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(window.innerWidth, window.innerHeight);
    };

    _onMouseMove = (e) => {
        this.mouse.x = (e.clientX / window.innerWidth) * 2 - 1;
        this.mouse.y = -(e.clientY / window.innerHeight) * 2 + 1;
    };
}
