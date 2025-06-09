  // Cantidad de Pokémon por equipo
const integrantes = 3;

// Función que devuelve un número al azar entre 1 y 1000 (IDs válidos de la PokéAPI)
function azar(){
  return Math.floor(Math.random() * 1000) + 1;
}

// IDs aleatorios para los equipos
const arrayEquipoAzul = Array.from({ length: integrantes }, () => azar());
const arrayEquipoRojo = Array.from({ length: integrantes }, () => azar());

// Estructura de datos para guardar los Pokémon de cada equipo
const arrayDatosAzul = Array.from({ length: integrantes }, () => ({ nombre: "", ataque: 0, defensa: 0, url: "" }));
const arrayDatosRojo = Array.from({ length: integrantes }, () => ({ nombre: "", ataque: 0, defensa: 0, url: "" }));

// Totales de ataque y defensa de cada equipo
let totalAtaqueAzul = 0, totalDefensaAzul = 0;
let totalAtaqueRojo = 0, totalDefensaRojo = 0;

// Función principal que carga los equipos desde la PokeAPI
async function pelear() {
  try {
    // Función para cargar un equipo desde la API
    async function cargarEquipos(ids, datos) {
      for (let i = 0; i < ids.length; i++) {
        const url = `https://pokeapi.co/api/v2/pokemon/${ids[i]}/`;
        const res = await fetch(url);
        const data = await res.json();

        datos[i].nombre = data.forms[0].name;
        datos[i].ataque = data.stats[1].base_stat;
        datos[i].defensa = data.stats[2].base_stat;
        datos[i].url = data.sprites.front_default;
      }
    }

    // Cargar ambos equipos
    await cargarEquipos(arrayEquipoAzul, arrayDatosAzul);
    await cargarEquipos(arrayEquipoRojo, arrayDatosRojo);

    // Mostrar los datos en pantalla
    const mostrarEquipo = (datos) => datos.map(p =>
      `<div class="card">
        <img src="${p.url}" alt="${p.nombre}" style="display:block; margin:auto;">
        <div ">${p.nombre}</div>
        <p><strong>Ataque:</strong> ${p.ataque}</p>
        <p><strong>Defensa:</strong> ${p.defensa}</p>
      </div>`
    ).join("");

    document.getElementById("infoEquipoAzul").innerHTML = mostrarEquipo(arrayDatosAzul);
    document.getElementById("infoEquipoRojo").innerHTML = mostrarEquipo(arrayDatosRojo);

    // Calcular totales
    arrayDatosAzul.forEach(p => {
      totalAtaqueAzul += p.ataque;
      totalDefensaAzul += p.defensa;
    });

    arrayDatosRojo.forEach(p => {
      totalAtaqueRojo += p.ataque;
      totalDefensaRojo += p.defensa;
    });

    // Mostrar totales
    function mostrarTotales (totalA, totalD, id) {
      document.getElementById(`${id}`).innerHTML = `
      <div class="card">
        <div ">Totales Azul</div>
        <p><strong>Ataque:</strong> ${totalA}</p>
        <p><strong>Defensa:</strong> ${totalD}</p>
      </div>`;
    }

    mostrarTotales(totalAtaqueAzul, totalDefensaAzul, "totalesAzul")
    mostrarTotales(totalAtaqueRojo, totalDefensaRojo, "totalesRojo")

  } catch (error) {
    console.error("Error con el fetch:", error);
  }
}


// Arrays para guardar las tiradas de dados
let dadosA = [], dadosB = [];

// Contadores de tiradas hechas
let intentosA = 0, intentosB = 0;

// Tirar dados para el Equipo Azul
function tirarDadosA() {
  if (intentosA >= 3) return; // Ya tiró 3 veces

  const dado1 = Math.floor(Math.random() * 6) + 1;
  const dado2 = Math.floor(Math.random() * 6) + 1;
  const suma = dado1 + dado2;

  dadosA.push(suma); // Guardamos la tirada
  intentosA++;

  // Mostramos visualmente la tirada
  document.getElementById("infoDadoA").innerHTML += `
    <div class="card">
      <div ">Tirada ${intentosA}</div>
      <p>${dado1} + ${dado2} = <strong>${suma}</strong></p>
    </div>`;

  if (intentosA === 3) {
    document.getElementById("boton2").disabled = true;
    chequearTiradasCompletas();
  }
}

// Tirar dados para el Equipo Rojo (idéntico a A)
function tirarDadosB() {
  if (intentosB >= 3) return;

  const dado1 = Math.floor(Math.random() * 6) + 1;
  const dado2 = Math.floor(Math.random() * 6) + 1;
  const suma = dado1 + dado2;

  dadosB.push(suma);
  intentosB++;

  document.getElementById("infoDadoB").innerHTML += `
    <div class="card">
      <div ">Tirada ${intentosB}</div>
      <p>${dado1} + ${dado2} = <strong>${suma}</strong></p>
    </div>`;

  if (intentosB === 3) {
    document.getElementById("boton3").disabled = true;
    chequearTiradasCompletas();
  }
}

// Cuando ambos equipos completan las 3 tiradas, se habilita el botón para pelear
function chequearTiradasCompletas() {
  if (intentosA === 3 && intentosB === 3) {
    document.getElementById("enfrentar").disabled = false;
  }
}

// Lógica de pelea final: ataque vs defensa + posible desempate por dados
function fpelea() {
  const azulVsRojo = totalAtaqueAzul - totalDefensaRojo;
  const rojoVsAzul = totalAtaqueRojo - totalDefensaAzul;

  let ganador = "";
  let detalleDesempate = "";

  if (azulVsRojo > rojoVsAzul) {
    ganador = "Equipo Azul";
  } else if (rojoVsAzul > azulVsRojo) {
    ganador = "Equipo Rojo";
  } else {
    // Empate: se define por la mejor tirada de dados
    const maxA = Math.max(...dadosA);
    const maxB = Math.max(...dadosB);
    const tiradaA = dadosA.indexOf(maxA) + 1;
    const tiradaB = dadosB.indexOf(maxB) + 1;

    if (maxA > maxB) {
      ganador = "Equipo Azul (por dados)";
    } else if (maxB > maxA) {
      ganador = "Equipo Rojo (por dados)";
    } else {
      ganador = "Empate total";
    }

    detalleDesempate = `
      <p><strong>Desempate:</strong></p>
      <p>Mayor tirada Azul: ${maxA} (Tirada ${tiradaA})</p>
      <p>Mayor tirada Rojo: ${maxB} (Tirada ${tiradaB})</p>
    `;
  }

  // Mostrar resultado final
  document.getElementById("Victoria").innerHTML = `
    <div class="card">
      <div ">Resultado Final</div>
      <p><strong>Ganador:</strong> ${ganador}</p>
      <p>Ataque Azul: ${totalAtaqueAzul} vs Defensa Roja: ${totalDefensaRojo}</p>
      <p>Ataque Rojo: ${totalAtaqueRojo} vs Defensa Azul: ${totalDefensaAzul}</p>
      <p>Diferencia Azul: ${azulVsRojo}</p>
      <p>Diferencia Rojo: ${rojoVsAzul}</p>
      ${detalleDesempate}
    </div>`;
}

// Ejecutar al cargar la página
pelear();
