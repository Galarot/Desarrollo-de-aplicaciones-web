(function () {
    const API_BASE = '/api';
    const userId = document.body.getAttribute('data-user-id');
    let personajes = [];
    let personajeDelDia = {};
    let intentos = 0;
    let elegidosEnRonda = new Set();
    let esquina = '';
    let modoInfinitoArt = false;
    let diarioArtCompletado = false;

    const input = document.getElementById("searchInput");
    const lista = document.getElementById("suggestions");
    const intentosVarios = document.getElementById("guessesGrid");
    const imagenArte = document.getElementById("artImage");
    const revelarArte = document.getElementById("artReveal");
    const contadorIntentos = document.getElementById("attempts");
    const btnInfiniteArt = document.getElementById("infinite-mode-art");
    const streakCounter = document.getElementById("artcart-streak-count");

// saca la semilla diaria para el modo artcart
function getDailySeed() {
    const today = new Date();
    return today.getFullYear() * 10000 + (today.getMonth() + 1) * 100 + today.getDate();
}

// genera un numero al azar con una semilla
function seededRandom(seed) {
    const x = Math.sin(seed) * 10000;
    return x - Math.floor(x);
}

// actualiza el contador de cristales en la pantalla
function actualizarCristales(crystals) {
    if (window.updateCrystalCounter && typeof crystals !== 'undefined') {
        window.updateCrystalCounter(crystals);
    }
}

// actualiza el numero de la racha de artcart
function actualizarRachaArt(streak) {
    if (streakCounter && typeof streak !== 'undefined') {
        streakCounter.textContent = Number(streak || 0).toString();
    }
}

// pide al servidor la recompensa del modo infinito art
function reclamarRecompensaInfinita() {
    if (userId === 'guest') return;

    fetch(API_BASE + '/rewards/infinite', { method: 'POST' })
        .then(r => r.json())
        .then(res => actualizarCristales(res.crystals))
        .catch(err => console.error("Error al guardar recompensa infinita art:", err));
}

// bloquea el input si ya se ha ganado hoy artcart
function actualizarBloqueoDiarioArt(completado) {
    diarioArtCompletado = completado;

    if (btnInfiniteArt && completado) {
        btnInfiniteArt.classList.remove('hidden');
    }

    if (!input) return;

    if (completado && !modoInfinitoArt) {
        input.value = "";
        input.disabled = true;
        input.placeholder = "Desafio diario Art Cart completado. Usa Modo Infinito.";
        input.classList.add('opacity-60', 'cursor-not-allowed');
        if (lista) lista.classList.add("hidden");
        return;
    }

    input.disabled = false;
    input.placeholder = modoInfinitoArt ? "Modo infinito: escribe un personaje..." : "Escribe el nombre del personaje...";
    input.classList.remove('opacity-60', 'cursor-not-allowed');
}

// elige el personaje de hoy para el arte
function seleccionarPersonaje() {
    if (personajes.length > 0) {
        if (!modoInfinitoArt) {
            const seed = getDailySeed();
            const aleatorio = Math.floor(seededRandom(seed + 1) * personajes.length);
            personajeDelDia = personajes[aleatorio];
        } else {
            const aleatorio = Math.floor(Math.random() * personajes.length);
            personajeDelDia = personajes[aleatorio];
        }

        const esquinas = ['top-left', 'top-right', 'bottom-left', 'bottom-right'];
        if (!modoInfinitoArt) {
            esquina = esquinas[Math.floor(seededRandom(getDailySeed() + 2) * esquinas.length)];
        } else {
            esquina = esquinas[Math.floor(Math.random() * esquinas.length)];
        }

        imagenArte.src = personajeDelDia.art_url;
        console.log("Personaje objetivo (Art Cart):", personajeDelDia.nombre);
        actualizarZoom();
    }
}

// mira el progreso del usu en el modo artcart
function sincronizarProgresoArt() {
    if (localStorage.getItem(`dailyWonArt_${userId}`) === getDailySeed().toString()) {
        actualizarBloqueoDiarioArt(true);
        if (userId === 'guest') {
            actualizarRachaArt(1);
        }
    }

    if (userId !== 'guest') {
        if (!diarioArtCompletado && input) {
            input.disabled = true;
            input.placeholder = "Comprobando desafio diario...";
            input.classList.add('opacity-60', 'cursor-not-allowed');
        }

        fetch(API_BASE + '/progress/check')
            .then(r => r.json())
            .then(data => {
                if (data.streaks) {
                    actualizarRachaArt(data.streaks.artcart);
                }

                if (data.artcart) {
                    localStorage.setItem(`dailyWonArt_${userId}`, getDailySeed().toString());
                    actualizarBloqueoDiarioArt(true);
                } else {
                    actualizarBloqueoDiarioArt(false);
                }
            })
            .catch(err => {
                actualizarBloqueoDiarioArt(false);
            });
    }
}

// arranca la sincronizacion art al cargar
sincronizarProgresoArt();

// activa el modo infinito para el arte
btnInfiniteArt.addEventListener('click', () => {
    modoInfinitoArt = true;
    actualizarBloqueoDiarioArt(diarioArtCompletado);
    intentosVarios.innerHTML = "";
    elegidosEnRonda.clear();
    intentos = 0;
    contadorIntentos.textContent = intentos;
    imagenArte.style.transition = 'none';
    seleccionarPersonaje();
});

// carga los splash arts desde la api
fetch(API_BASE + '/splash')
    .then(response => response.json())
    .then(data => {
        personajes = data;
        seleccionarPersonaje();
    })
    .catch(error => console.error('Error cargando personajes:', error));

// busca personajes para el modo arte
input.addEventListener('input', () => {
    if (diarioArtCompletado && !modoInfinitoArt) {
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
                <img src="${p.art_url}" class="w-10 h-10 rounded-full border border-orange-500 mr-3" onerror="this.src='https://via.placeholder.com/40'">
                <span>${p.nombre}</span>
            </div>
        `).join('');
});

// hace la seleccion de un personaje en artcart
function elegir(event, id) {
    event.preventDefault();
    event.stopPropagation();

    if (diarioArtCompletado && !modoInfinitoArt) {
        lista.classList.add("hidden");
        return;
    }

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

        if (window.showVictoryModal) {
            window.showVictoryModal(personajeDelDia.art_url, () => {
                if (modoInfinitoArt) {
                    intentosVarios.innerHTML = "";
                    elegidosEnRonda.clear();
                    intentos = 0;
                    contadorIntentos.textContent = intentos;
                    imagenArte.style.transition = 'none';
                    seleccionarPersonaje();
                }
            });
        }

        if (!modoInfinitoArt) {
            localStorage.setItem(`dailyWonArt_${userId}`, getDailySeed().toString());
            actualizarBloqueoDiarioArt(true);

            if (userId !== 'guest') {
                fetch(API_BASE + '/progress/save', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ mode: 'artcart' })
                })
                    .then(r => r.json())
                    .then(res => {
                        actualizarCristales(res.crystals);
                        actualizarRachaArt(res.streak);
                    })
                    .catch(err => console.error("Error al guardar victoria art:", err));
            }
        } else {
            reclamarRecompensaInfinita();
        }
    } else {
        imagenArte.style.transition = 'transform 0.5s ease';
        actualizarZoom();
    }
}

window.elegir = elegir;

// mete una fila nueva con el intento de arte
function agregarIntento(personaje, correcto) {
    const fila = document.createElement("div");
    const color = correcto ? 'border-green-500 bg-green-600/20' : 'border-red-500 bg-red-600/20';

    fila.className = `flex items-center gap-4 p-4 ${color} rounded-xl border-2 transition-all duration-300`;
    fila.innerHTML = `
        <img src="${personaje.art_url}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-lg object-cover border-2 border-orange-400" onerror="this.src='https://via.placeholder.com/96'">
        <span class="flex-1 text-2xl text-white font-bold font-['Edo_SZ']">${personaje.nombre}</span>
    `;

    intentosVarios.insertBefore(fila, intentosVarios.firstChild);
}

// hace el zoom progresivo segun los intentos
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
})();
