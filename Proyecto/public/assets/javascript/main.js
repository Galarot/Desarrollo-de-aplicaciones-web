// DBLegendsdle - Juego Clásico
// Sistema de adivinanza de personajes con comparación de atributos

(function () {

console.log("main.js cargado correctamente");
const API_BASE = '/api';
const userId = document.body.getAttribute('data-user-id');
let personajes = [];
let elegidosEnRonda = new Set();
let modoInfinito = false;
let diarioCompletado = false;

const input = document.getElementById("search-input");
const lista = document.getElementById("result");
const intentosVarios = document.getElementById("intentos-container");
const winButtons = document.getElementById("win-buttons");
const btnInfinite = document.getElementById("infinite-mode");
const streakCounter = document.getElementById("classic-streak-count");

let pruebaDia = {};

function getDailySeed() {
    const today = new Date();
    return today.getFullYear() * 10000 + (today.getMonth() + 1) * 100 + today.getDate();
}

function seededRandom(seed) {
    const x = Math.sin(seed) * 10000;
    return x - Math.floor(x);
}

function actualizarCristales(crystals) {
    if (window.updateCrystalCounter && typeof crystals !== 'undefined') {
        window.updateCrystalCounter(crystals);
    }
}

function actualizarRacha(streak) {
    if (streakCounter && typeof streak !== 'undefined') {
        streakCounter.textContent = Number(streak || 0).toString();
    }
}

function reclamarRecompensaInfinita() {
    if (userId === 'guest') return;

    fetch(API_BASE + '/rewards/infinite', { method: 'POST' })
        .then(r => r.json())
        .then(res => actualizarCristales(res.crystals))
        .catch(err => console.error("Error al guardar recompensa infinita:", err));
}

function actualizarBloqueoDiario(completado) {
    diarioCompletado = completado;

    if (winButtons && completado) {
        winButtons.classList.remove('hidden');
    }

    if (!input) return;

    if (completado && !modoInfinito) {
        input.value = "";
        input.disabled = true;
        input.placeholder = "Desafio diario clasico completado. Usa Modo Infinito.";
        input.classList.add('opacity-60', 'cursor-not-allowed');
        if (lista) lista.classList.add("hidden");
        return;
    }

    input.disabled = false;
    input.placeholder = modoInfinito ? "Modo infinito: escribe un personaje..." : "Escribe el nombre del personaje...";
    input.classList.remove('opacity-60', 'cursor-not-allowed');
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
    try {
        console.log("Iniciando sincronización para usuario:", userId);
        if (!userId) {
            console.warn("userId no encontrado en el body");
            return;
        }

        // Verificar progreso local
        const savedSeed = localStorage.getItem(`dailyWonIndex_${userId}`);
        if (savedSeed === getDailySeed().toString()) {
            console.log("Progreso detectado en localStorage");
            actualizarBloqueoDiario(true);
            if (userId === 'guest') {
                actualizarRacha(1);
            }
        }

        // Sincronizar con el servidor si está logueado
        if (userId !== 'guest') {
            if (!diarioCompletado && input) {
                input.disabled = true;
                input.placeholder = "Comprobando desafio diario...";
                input.classList.add('opacity-60', 'cursor-not-allowed');
            }

            fetch(API_BASE + '/progress/check')
                .then(r => {
                    if (!r.ok) throw new Error("Fallo en check progreso: " + r.status);
                    return r.json();
                })
                .then(data => {
                    console.log("Respuesta del servidor (check):", data);
                    if (data.streaks) {
                        actualizarRacha(data.streaks.classic);
                    }

                    if (data.classic) {
                        console.log("Servidor confirma victoria hoy");
                        localStorage.setItem(`dailyWonIndex_${userId}`, getDailySeed().toString());
                        actualizarBloqueoDiario(true);
                    } else {
                        actualizarBloqueoDiario(false);
                    }
                })
                .catch(err => {
                    console.error("Error sincronizando progreso:", err);
                    actualizarBloqueoDiario(false);
                });
        }
    } catch (e) {
        console.error("Error crítico en sincronizarProgreso:", e);
    }
}

// Ejecutar sincronización inmediatamente
sincronizarProgreso();

btnInfinite.addEventListener('click', () => {
    modoInfinito = true;
    actualizarBloqueoDiario(diarioCompletado);
    intentosVarios.innerHTML = "";
    elegidosEnRonda.clear();
    // No ocultamos winButtons, solo generamos nuevo personaje
    seleccionasPerso();
});

// Cargar datos desde la API
console.log("Cargando personajes desde la API...");
fetch(API_BASE + '/characters')
    .then(response => {
        console.log("Respuesta API characters recibida:", response.status);
        if (!response.ok) throw new Error("Error HTTP: " + response.status);
        return response.json();
    })
    .then(data => {
        console.log("Personajes procesados:", data.length);
        personajes = data;
        seleccionasPerso();
        // Si hay texto en el input, actualizar el dropdown
        if (input && input.value.trim()) {
            input.dispatchEvent(new Event('input'));
        }
    })
    .catch(error => console.error('Error cargando personajes:', error));

input.addEventListener('input', () => {
    if (diarioCompletado && !modoInfinito) {
        lista.classList.add("hidden");
        return;
    }

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
                <img src="${p.art_cart_url}" class="w-10 h-10 rounded-full border border-orange-500 mr-3" onerror="this.src='/assets/multimedia/logo.png'">
                <span>${p.nombre}</span>
            </div>
        `).join('');
});

function elegir(event, id) {
    event.preventDefault();
    event.stopPropagation();

    if (diarioCompletado && !modoInfinito) {
        lista.classList.add("hidden");
        return;
    }

    const p = personajes.find(pers => pers.id === id);
    elegidosEnRonda.add(id);
    compararAtributos(p);

    if (p.id === pruebaDia.id) {
        alert("¡Has ganado!");

        if (!modoInfinito) {
            localStorage.setItem(`dailyWonIndex_${userId}`, getDailySeed().toString());
            actualizarBloqueoDiario(true);

            if (userId !== 'guest') {
                console.log("Enviando victoria al servidor...");
                fetch(API_BASE + '/progress/save', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ mode: 'classic' })
                    })
                    .then(r => r.json())
                    .then(res => {
                        console.log("Resultado de guardado:", res);
                        actualizarCristales(res.crystals);
                        actualizarRacha(res.streak);
                    })
                    .catch(err => console.error("Error al guardar victoria:", err));
            }
        } else {
            reclamarRecompensaInfinita();
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

window.elegir = elegir;

function compararAtributos(usuario) {
    const fila = document.createElement("div");
    fila.className = "flex justify-center gap-2 mb-2 flex-nowrap min-w-max px-4";

    let html = `
        <div class="w-24 sm:w-28 md:w-32 h-20 sm:h-24 md:h-28 bg-slate-800 border-2 border-slate-700 rounded-md overflow-hidden flex-shrink-0">
            <img src="${usuario.art_cart_url}" class="w-full h-full object-cover" onerror="this.src='/assets/multimedia/logo.png'">
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
})();
