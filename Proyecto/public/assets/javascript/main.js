// DBLegendsdle - Juego Clásico
// Sistema de adivinanza de personajes con comparación de atributos

const API_BASE = '/index.php/api';
const userId = document.body.getAttribute('data-user-id');
let personajes = [];
let elegidosEnRonda = new Set();
let modoInfinito = false;

const input = document.getElementById("search-input");
const lista = document.getElementById("result");
const intentosVarios = document.getElementById("intentos-container");
const winButtons = document.getElementById("win-buttons");
const btnInfinite = document.getElementById("infinite-mode");

let pruebaDia = {};

function getDailySeed() {
    const today = new Date();
    return today.getFullYear() * 10000 + (today.getMonth() + 1) * 100 + today.getDate();
}

function seededRandom(seed) {
    const x = Math.sin(seed) * 10000;
    return x - Math.floor(x);
}

function seleccionasPerso() {
    const idMin = 1;
    const idMax = 689;
    const rango = personajes.filter(p => p.id >= idMin && p.id <= idMax);

    if (personajes.length > 0) {
        if (!modoInfinito) {
            const seed = getDailySeed();
            const aleatorio = Math.floor(seededRandom(seed) * rango.length);
            pruebaDia = rango[aleatorio];
            console.log("Objetivo del día:", pruebaDia.id, pruebaDia.nombre);
        } else {
            const aleatorio = Math.floor(Math.random() * rango.length);
            pruebaDia = rango[aleatorio];
            console.log("Objetivo infinito:", pruebaDia.id, pruebaDia.nombre);
        }
    }
}

function sincronizarProgreso() {
    console.log("Iniciando sincronización para usuario:", userId);
    // Verificar progreso local
    if (localStorage.getItem(`dailyWonIndex_${userId}`) === getDailySeed().toString()) {
        console.log("Progreso detectado en localStorage");
        winButtons.classList.remove('hidden');
    }

    // Sincronizar con el servidor si está logueado
    if (userId !== 'guest') {
        fetch(API_BASE + '/progress/check')
            .then(r => r.json())
            .then(data => {
                console.log("Respuesta del servidor (check):", data);
                if (data.classic) {
                    console.log("Servidor confirma victoria hoy");
                    winButtons.classList.remove('hidden');
                    localStorage.setItem(`dailyWonIndex_${userId}`, getDailySeed().toString());
                }
            })
            .catch(err => console.error("Error sincronizando progreso:", err));
    }
}

// Ejecutar sincronización inmediatamente
sincronizarProgreso();

btnInfinite.addEventListener('click', () => {
    modoInfinito = true;
    intentosVarios.innerHTML = "";
    elegidosEnRonda.clear();
    // No ocultamos winButtons, solo generamos nuevo personaje
    seleccionasPerso();
});

// Cargar datos desde la API
fetch(API_BASE + '/characters')
    .then(response => response.json())
    .then(data => {
        personajes = data;
        seleccionasPerso();
        // Si hay texto en el input, actualizar el dropdown
        if (input.value.trim()) {
            input.dispatchEvent(new Event('input'));
        }
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
                <img src="${p.art_cart_url}" class="w-10 h-10 rounded-full border border-orange-500 mr-3">
                <span>${p.nombre}</span>
            </div>
        `).join('');
});

function elegir(event, id) {
    event.preventDefault();
    event.stopPropagation();

    const p = personajes.find(pers => pers.id === id);
    elegidosEnRonda.add(id);
    compararAtributos(p);

    if (p.id === pruebaDia.id) {
        alert("¡Has ganado!");

        if (!modoInfinito) {
            localStorage.setItem(`dailyWonIndex_${userId}`, getDailySeed().toString());
            winButtons.classList.remove('hidden');

            if (userId !== 'guest') {
                console.log("Enviando victoria al servidor...");
                fetch(API_BASE + '/progress/save', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ mode: 'classic' })
                    })
                    .then(r => r.json())
                    .then(res => console.log("Resultado de guardado:", res))
                    .catch(err => console.error("Error al guardar victoria:", err));
            }
        }

        setTimeout(() => {
            if (modoInfinito) {
                intentosVarios.innerHTML = "";
                elegidosEnRonda.clear();
                seleccionasPerso();
            }
        }, 2000);
    }

    input.value = "";
    lista.classList.add("hidden");
}

function compararAtributos(usuario) {
    const fila = document.createElement("div");
    fila.className = "flex justify-center gap-2 mb-2 flex-nowrap min-w-max px-4";

    let html = `
        <div class="w-24 sm:w-28 md:w-32 h-20 sm:h-24 md:h-28 bg-slate-800 border-2 border-slate-700 rounded-md overflow-hidden flex-shrink-0">
            <img src="${usuario.art_cart_url}" class="w-full h-full object-cover" onerror="this.src='https://via.placeholder.com/150'">
        </div>
    `;

    Object.keys(pruebaDia.atributos).forEach(key => {
        const opcionUsu = usuario.atributos[key];
        const opcionAtri = pruebaDia.atributos[key];

        let color;
        if (opcionUsu === opcionAtri) {
            color = "bg-green-600";
        } else if (key === "raza" || key === "afinidad") {
            const valsUsu = opcionUsu.split("/");
            const valsAtri = opcionAtri.split("/");
            const tieneCoincidencia = valsUsu.some(v => valsAtri.includes(v));
            color = tieneCoincidencia ? "bg-orange-400" : "bg-red-600";
        } else {
            color = "bg-red-600";
        }

        html += `
            <div class="${color} w-28 sm:w-32 md:w-36 lg:w-40 h-20 sm:h-24 md:h-28 flex items-center justify-center text-center text-xs sm:text-sm md:text-base lg:text-lg font-bold rounded-md border-2 border-white/10 p-1 text-black uppercase font-['Edo_SZ']">
                ${opcionUsu}
            </div>
        `;
    });

    const colorAnio = usuario.anio === pruebaDia.anio ? "bg-green-600" : "bg-red-600";
    const flecha = usuario.anio < pruebaDia.anio ? "↑" : "↓";
    const textoFlecha = usuario.anio === pruebaDia.anio ? "" : flecha;

    html += `
        <div class="${colorAnio} w-24 sm:w-28 md:w-32 h-20 sm:h-24 md:h-28 flex flex-col items-center justify-center font-bold rounded-md border-2 border-white/10 text-black font-['Edo_SZ'] text-xs sm:text-sm md:text-base">
            <span>${usuario.anio}</span>
            <span class="text-xs">${textoFlecha}</span>
        </div>
    `;

    fila.innerHTML = html;
    intentosVarios.prepend(fila);
}