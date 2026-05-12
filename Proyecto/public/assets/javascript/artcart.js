// DBLegendsdle - Juego Art Cart
// Sistema de adivinanza por arte/splash con revelación progresiva

const API_BASE = '/api';
let personajes = [];
let personajeDelDia = {};
let intentos = 0;
let elegidosEnRonda = new Set();
let esquina = '';

const input = document.getElementById("searchInput");
const lista = document.getElementById("suggestions");
const intentosVarios = document.getElementById("guessesGrid");
const imagenArte = document.getElementById("artImage");
const revelarArte = document.getElementById("artReveal");
const contadorIntentos = document.getElementById("attempts");

function seleccionarPersonaje() {
    if (personajes.length > 0) {
        const aleatorio = Math.floor(Math.random() * personajes.length);
        personajeDelDia = personajes[aleatorio];

        const esquinas = ['top-left', 'top-right', 'bottom-left', 'bottom-right'];
        esquina = esquinas[Math.floor(Math.random() * esquinas.length)];

        imagenArte.src = personajeDelDia.art_url;
        actualizarZoom();

        console.log("Personaje del día:", personajeDelDia.nombre);
    }
}

// Cargar datos desde la API
fetch(API_BASE + '/splash')
    .then(response => response.json())
    .then(data => {
        personajes = data;
        seleccionarPersonaje();
    })
    .catch(error => console.error('Error cargando personajes:', error));

input.addEventListener('input', () => {
    const text = input.value.toLowerCase().trim();
    lista.classList.toggle("hidden", !text);

    if (!text) return;

    lista.innerHTML = personajes
        .filter(p =>
            p.nombre.toLowerCase().includes(text) &&
            !elegidosEnRonda.has(p.id)
        )
        .map(p => `
            <div onclick="elegir(event, ${p.id})" class="flex items-center p-3 hover:bg-orange-600/20 cursor-pointer border-b border-white/10 text-white font-['Edo_SZ']">
                <img src="${p.art_url}" class="w-10 h-10 rounded-full border border-orange-500 mr-3" onerror="this.src='https://via.placeholder.com/40'">
                <span>${p.nombre}</span>
            </div>
        `).join('');
});

function elegir(event, id) {
    event.preventDefault();
    event.stopPropagation();

    const p = personajes.find(pers => pers.id === id);

    elegidosEnRonda.add(id);
    intentos++;
    contadorIntentos.textContent = intentos;

    agregarIntento(p, p.id === personajeDelDia.id);

    input.value = "";
    lista.classList.add("hidden");

    if (p.id === personajeDelDia.id) {
        imagenArte.style.transition = 'transform 0.5s ease';
        imagenArte.style.transform = 'scale(1.0)';
        imagenArte.style.transformOrigin = 'center';

        alert("¡Has ganado!");
        setTimeout(() => {
            intentosVarios.innerHTML = "";
            elegidosEnRonda.clear();
            intentos = 0;
            contadorIntentos.textContent = intentos;
            imagenArte.style.transition = 'none';
            seleccionarPersonaje();
        }, 2000);
    } else {
        imagenArte.style.transition = 'transform 0.5s ease';
        actualizarZoom();
    }
}

function agregarIntento(personaje, correcto) {
    const fila = document.createElement("div");
    const color = correcto ? 'border-green-500 bg-green-600/20' : 'border-red-500 bg-red-600/20';

    fila.className = `flex items-center gap-4 p-4 ${color} rounded-xl border-2 transition-all duration-300`;
    fila.innerHTML = `
        <img src="${personaje.art_url}" class="w-16 h-16 rounded-lg object-cover border-2 border-orange-400" onerror="this.src='https://via.placeholder.com/64'">
        <span class="flex-1 text-xl text-white font-bold font-['Edo_SZ']">${personaje.nombre}</span>
    `;

    intentosVarios.insertBefore(fila, intentosVarios.firstChild);
}

function actualizarZoom() {
    const zooms = ['5.0', '4.5', '4.0', '3.5', '3.0', '2.5', '2.0'];
    let zoom;

    if (intentos < zooms.length) {
        zoom = zooms[intentos];
    } else {
        zoom = '2.0';
    }

    const origenes = {
        'top-left': 'top left',
        'top-right': 'top right',
        'bottom-left': 'bottom left',
        'bottom-right': 'bottom right'
    };

    revelarArte.style.width = '100%';
    revelarArte.style.height = '100%';
    revelarArte.style.top = '0';
    revelarArte.style.left = '0';

    imagenArte.style.transform = `scale(${zoom})`;
    imagenArte.style.transformOrigin = origenes[esquina];
}