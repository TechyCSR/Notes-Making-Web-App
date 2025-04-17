const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
const renderer = new THREE.WebGLRenderer({ alpha: true });

renderer.setSize(window.innerWidth, window.innerHeight);
document.body.appendChild(renderer.domElement);

// Create floating notes effect
const geometry = new THREE.BoxGeometry(1, 1.4, 0.1);
const material = new THREE.MeshPhongMaterial({
    color: 0x00ff00,
    transparent: true,
    opacity: 0.6
});

const notes = [];
for (let i = 0; i < 20; i++) {
    const note = new THREE.Mesh(geometry, material);
    note.position.set(
        Math.random() * 20 - 10,
        Math.random() * 20 - 10,
        Math.random() * 20 - 10
    );
    notes.push(note);
    scene.add(note);
}

// Add lighting
const light = new THREE.PointLight(0xffffff, 1, 100);
light.position.set(10, 10, 10);
scene.add(light);

camera.position.z = 5;

function animate() {
    requestAnimationFrame(animate);
    
    notes.forEach(note => {
        note.rotation.x += 0.01;
        note.rotation.y += 0.01;
    });
    
    renderer.render(scene, camera);
}

animate(); 