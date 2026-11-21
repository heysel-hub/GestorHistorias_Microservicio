class Dom {
    static elemento(etiqueta, clase = '', texto = '') {
        const nodo = document.createElement(etiqueta);
        if (clase !== '') {
            nodo.className = clase;
        }
        if (texto !== '') {
            nodo.textContent = String(texto);
        }
        return nodo;
    }

    static vaciar(nodo) {
        while (nodo.firstChild) {
            nodo.removeChild(nodo.firstChild);
        }
    }

        const tabla = Dom.elemento('table');

        const cabecera = Dom.elemento('tr');
        for (const encabezado of encabezados) {
            cabecera.appendChild(Dom.elemento('th', '', encabezado));
        }
        const thead = Dom.elemento('thead');
        thead.appendChild(cabecera);
        tabla.appendChild(thead);

        const tbody = Dom.elemento('tbody');
        for (const datos of filas) {
            const fila = Dom.elemento('tr');
            for (const dato of datos) {
                fila.appendChild(Dom.elemento('td', '', dato));
            }
            tbody.appendChild(fila);
        }
        tabla.appendChild(tbody);

        return tabla;
    }
}
