let personajes = [];
//prsonajes ya usados
let elegidosEnRonda = new Set();

const input = document.getElementById("busqueda");
const lista = document.getElementById("result");
const intentosVarios = document.getElementById("intentos");

let pruebaDia = {};

function seleccionasPerso() {
    const idMin = 1;
    const idMax = 322;
    //temporal ya que algunos en la base de datos no estan definidos del tdo
    const rango = personajes.filter(p => p.id >= idMin && p.id <= idMax);

    if (personajes.length > 0) {
        const aleatorio = Math.floor(Math.random() * rango.length);
        pruebaDia = rango[aleatorio];
        console.log("Objetivo del día:", pruebaDia.id, pruebaDia.nombre);
    }
}

//carga del json, alternativa a hacerlo nativamente
fetch("./src/data/characters.json")
    .then(response => response.json()) //convertir a json al responder
    .then(data => {
        personajes = data;
        seleccionasPerso();
    }) //guardar datos array local
    .catch(error => {
        console.error(error);
    });

//al haber algo en el campo de texto se activa
input.oninput = () => {
    const text = input.value.toLowerCase().trim();
    lista.classList.toggle("hidden", !text); //muestra y ocuylta si hay texto

    if (!text) return; //volver si no hay texto

    lista.innerHTML = personajes
        .filter(p =>
            p.nombre.toLowerCase().includes(text) &&
            !elegidosEnRonda.has(p.id) // no mostrar los ya elegidos
        )
        .map(p => `
            <div onclick="elegir(${p.id})" class="flex items-center p-3 hover:bg-orange-600/20 cursor-pointer border-b border-white/10 text-white font-['Edo_SZ']">
                <img src="${p.art_cart_url}" class="w-10 h-10 rounded-full border border-orange-500 mr-3">
                <span>${p.nombre}</span>
            </div>
        `).join('');
};

function elegir(id) {
    const p = personajes.find(pers => pers.id === id); //buscar personaje por id

    elegidosEnRonda.add(id);

    compararAtributos(p);

    //alert que avisa que se encontró el personaje del dia
    if (p.id === pruebaDia.id) {
        alert("Has ganao");
        setTimeout(() => {
            intentosVarios.innerHTML = ""; //limpiar cuando se reinicia
            elegidosEnRonda.clear(); // Limpiar la lista de descartados para la nueva partida
            seleccionasPerso();
        }, 2000);
    }

    input.value = ""; //limpiar
    lista.classList.add("hidden");
}

function compararAtributos(usuario) {
    const fila = document.createElement("div");
    fila.className = "flex justify-center gap-2 mb-2";

    //imagen personaje del usuario
    let html = `
        <div class="w-18 h-18 bg-slate-800 border-2 border-slate-700 rounded-md overflow-hidden flex-shrink-0">
            <img src="${usuario.art_cart_url}" class="w-full h-full object-cover" onerror="this.src='https://via.placeholder.com/150'">
        </div>
    `;

    //atributos
    Object.keys(pruebaDia.atributos).forEach(key => {
        const opcionUsu = usuario.atributos[key];
        const opcionAtri = pruebaDia.atributos[key];

        let color;
        if (opcionUsu === opcionAtri) {
            color = "bg-green-600";
        } else if (key === "raza") {
            const razasUsu = opcionUsu.split("/");
            const razasAtri = opcionAtri.split("/");
            const tieneCoincidencia = razasUsu.some(raza => razasAtri.includes(raza));
            color = tieneCoincidencia ? "bg-orange-400" : "bg-red-600";
        } else {
            color = "bg-red-600";
        }

        html += `
        <div class="${color} w-24 h-18 flex items-center justify-center text-center text-[18px] font-bold rounded-md border-2 border-white/10 p-1 text-black uppercase font-['Edo_SZ']">
                ${opcionUsu} 
            </div>
        `;
    });

    //comparar año
    const colorAnio = usuario.anio === pruebaDia.anio ? "bg-green-600" : "bg-red-600";
    const flecha = usuario.anio < pruebaDia.anio ? "↑" : "↓";
    // fecha.className="text-[18px]";
    const textoFlecha = usuario.anio === pruebaDia.anio ? "" : flecha;

    html += `
        <div class="${colorAnio} w-20 h-16 flex flex-col items-center justify-center font-bold rounded-md border-2 border-white/10 text-black font-['Edo_SZ']">
            <span>${usuario.anio}</span>
            <span class="text-xs">${textoFlecha}</span>
        </div>
    `;

    fila.innerHTML = html;
    intentosVarios.prepend(fila); //intentos arriba uno de otro
}