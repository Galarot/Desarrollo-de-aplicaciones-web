// DBLegendsdle - Juego Clásico
// Sistema de adivinanza de personajes con comparación de atributos

const API_BASE = '/api';
let personajes = [];
let elegidosEnRonda = new Set();

const input = document.getElementById("search-input");
const lista = document.getElementById("result");
const intentosVarios = document.getElementById("intentos-container");

let pruebaDia = {};

function seleccionasPerso() {
    const idMin = 1;
    const idMax = 459;
    const rango = personajes.filter(p => p.id >= idMin && p.id <= idMax);

    if (personajes.length > 0) {
        const aleatorio = Math.floor(Math.random() * rango.length);
        pruebaDia = rango[aleatorio];
        console.log("Objetivo del día:", pruebaDia.id, pruebaDia.nombre);
    }
}

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
        setTimeout(() => {
            intentosVarios.innerHTML = "";
            elegidosEnRonda.clear();
            seleccionasPerso();
        }, 2000);
    }

    input.value = "";
    lista.classList.add("hidden");
}

function compararAtributos(usuario) {
    const fila = document.createElement("div");
    fila.className = "col-span-full flex justify-center gap-1 sm:gap-2 mb-2 overflow-x-auto";

    let html = `
        <div class="w-14 sm:w-16 md:w-18 h-14 sm:h-16 md:h-18 bg-slate-800 border-2 border-slate-700 rounded-md overflow-hidden flex-shrink-0">
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
            <div class="${color} w-14 sm:w-16 md:w-20 lg:w-24 h-14 sm:h-16 md:h-18 flex items-center justify-center text-center text-xs sm:text-sm md:text-base lg:text-lg font-bold rounded-md border-2 border-white/10 p-1 text-black uppercase font-['Edo_SZ']">
                ${opcionUsu}
            </div>
        `;
    });

    const colorAnio = usuario.anio === pruebaDia.anio ? "bg-green-600" : "bg-red-600";
    const flecha = usuario.anio < pruebaDia.anio ? "↑" : "↓";
    const textoFlecha = usuario.anio === pruebaDia.anio ? "" : flecha;

    html += `
        <div class="${colorAnio} w-14 sm:w-16 md:w-20 h-14 sm:h-16 md:h-18 flex flex-col items-center justify-center font-bold rounded-md border-2 border-white/10 text-black font-['Edo_SZ'] text-xs sm:text-sm md:text-base">
            <span>${usuario.anio}</span>
            <span class="text-xs">${textoFlecha}</span>
        </div>
    `;

    fila.innerHTML = html;
    intentosVarios.prepend(fila);
}