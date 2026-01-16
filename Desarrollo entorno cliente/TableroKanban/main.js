const inputNum = document.getElementById("num");
const contenido = document.getElementById("tabla");
const form = document.getElementById("formulario");


function obtener (){
    const datos = localStorage.getItem("tablero");
    if(datos){
        return JSON.parse(datos);
    } else {
        return [];
    }
}

function guardar(arrayDatos){
    localStorage.setItem("tablero", JSON.stringify(arrayDatos));
}

form.addEventListener("submit", (e) =>{
    e.preventDefault();
    const cant = parseInt(inputNum.value);

    for (let i = 0; i <cantidad; i++){
        const div = document.createElement("div");

        const inputNombre = document.createElement("input");
        inputNombre.type = "text";
        inputNombre.placeholder = "Nombre: ";
    }
})

function mostrarTabla(){
    contenido.innerHTML= "";
    const tablaActivo = obtener();

    tablaActivo.forEach((texto, indice) =>{
        const tab = document.createElement("tablero");
        tab.textContent = texto;

        
    })
}

