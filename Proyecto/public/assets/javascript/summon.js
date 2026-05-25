(function () {
    const API_BASE = '/api';
    const fallbackImage = '/assets/multimedia/logo.png';
    const summonButton = document.getElementById('summon-button');
    const message = document.getElementById('summon-message');
    const resultsGrid = document.getElementById('summon-results');
    const collectionToggle = document.getElementById('collection-toggle');
    const collectionGrid = document.getElementById('collection-grid');

    let characters = [];
    let ownedCharacterIds = new Set();
    let authenticated = false;

    // pone un mensaje en el panel de invocacion
    function setMessage(text, type) {
        message.textContent = text;
        message.className = 'min-h-6 text-center font-semibold ' + (type === 'error' ? 'text-red-200' : 'text-yellow-100');
    }

    // pone una imagen por defecto si no hay ruta
    function normalizeImage(path) {
        return path || fallbackImage;
    }

    // dibuja la carta de un personaje en la grid
    function renderCard(character, locked) {
        const card = document.createElement('div');
        card.className = (locked ? 'character-locked ' : '') + 'bg-slate-950/80 border border-orange-500/50 rounded-lg p-2 text-center min-w-0';
        card.innerHTML = `
            <div class="aspect-square rounded-md overflow-hidden bg-slate-800 border border-white/10">
                <img src="${normalizeImage(character.art_cart_url)}" class="w-full h-full object-cover" onerror="this.src='${fallbackImage}'" alt="">
            </div>
            <p class="mt-2 text-xs sm:text-sm text-white font-['Edo_SZ'] leading-tight break-words">${character.nombre}</p>
        `;

        return card;
    }

    // pinta toda la lista de personajes del juego
    function renderCollection() {
        collectionGrid.innerHTML = '';
        characters.forEach((character) => {
            collectionGrid.appendChild(renderCard(character, !ownedCharacterIds.has(Number(character.id))));
        });
    }

    // pinta los personajes que han salido en la tirada
    function renderResults(results) {
        resultsGrid.innerHTML = '';
        results.forEach((character) => {
            resultsGrid.appendChild(renderCard(character, false));
        });
    }

    // actualiza las gemas que se ven arriba
    function syncCounter(crystals) {
        if (window.updateCrystalCounter) {
            window.updateCrystalCounter(crystals);
        }
    }

    // trae el estado del usu y su coleccion
    function loadStatus() {
        fetch(API_BASE + '/summon/status')
            .then((response) => response.json())
            .then((data) => {
                authenticated = Boolean(data.authenticated);
                characters = data.characters || [];
                ownedCharacterIds = new Set((data.ownedCharacterIds || []).map(Number));
                syncCounter(data.crystals || 0);
                renderCollection();

                if (!authenticated) {
                    setMessage('Inicia sesion para hacer invocaciones.', 'error');
                }
            })
            .catch(() => setMessage('No se pudo cargar la invocacion.', 'error'));
    }

    // maneja el click para tirar al banner
    summonButton.addEventListener('click', () => {
        if (!authenticated) {
            setMessage('Inicia sesion para hacer invocaciones.', 'error');
            return;
        }

        summonButton.disabled = true;
        setMessage('Invocando...', 'info');

        fetch(API_BASE + '/summon/pull', { method: 'POST' })
            .then((response) => response.json().then((data) => ({ ok: response.ok, data })))
            .then(({ ok, data }) => {
                if (!ok) {
                    setMessage(data.error || 'No se pudo invocar.', 'error');
                    syncCounter(data.crystals || 0);
                    return;
                }

                ownedCharacterIds = new Set((data.ownedCharacterIds || []).map(Number));
                syncCounter(data.crystals || 0);
                renderResults(data.results || []);
                renderCollection();
                setMessage('Invocacion completada.', 'info');
            })
            .catch(() => setMessage('No se pudo invocar ahora.', 'error'))
            .finally(() => {
                summonButton.disabled = false;
            });
    });

    // muestra u oculta la lista de personajes
    collectionToggle.addEventListener('click', () => {
        collectionGrid.classList.toggle('hidden');
        collectionToggle.textContent = collectionGrid.classList.contains('hidden') ? 'Ver todos los personajes' : 'Ocultar personajes';
    });

    
    loadStatus();
})();
