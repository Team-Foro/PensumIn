/* Función para el Filtro de Búsqueda de la Tabla de Consulta */
if (document.querySelector("#searcher-form")) {
    let searcher = document.querySelector("#searcher-form");

    searcher.onsubmit = function(e) {
        e.preventDefault();

        let search = document.querySelector("#searcher").value;
        
    }
}