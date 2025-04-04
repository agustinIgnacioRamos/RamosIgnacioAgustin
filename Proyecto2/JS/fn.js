let persona1 = {
    nombre: 'Agustín',
    apellido: 'Ramos',
    edad: 18, 
    DNI: 47176861, colores: ['azul', 'rojo', 'celeste']
}
let persona2 = {
    nombre: 'Sofía', 
    apellido : 'Veronesi', 
    edad: 21, 
    DNI: 44980709, 
    colores : ['rosa', 'violeta', 'amarillo']
}

function fc (persona1, persona2) {
    
    if (persona1.edad > persona2.edad) {

        console.log (persona1.nombre + ' Es más grande que ' + persona2.nombre);
        for (let i = 0; i < 3; i++) {

            if (persona1.colores[i] === 'azul') {
                console.log ('Si le gusta el color azul')
            } else {
                console.log ('No le gusta el color azul')
                
            }
        }
            
        
    } else { 
        console.log(persona2.nombre + ' Es más grande que ' + persona1.nombre);
        for (let i = 0; i < 3; i++) {

            if (persona2.colores[i] === 'azul') {
                console.log ('Si le gusta el color azul a Sofia')
            } else {
                console.log ('No le gusta el color azul')
                
            }
        }
    }


}

let fuc = fc(persona1, persona2)

