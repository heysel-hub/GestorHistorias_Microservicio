class Mensaje {
    #contenedor;

    constructor(idContenedor) {
        this.#contenedor = document.getElementById(idContenedor);
    }

    ok(texto) {
        this.#mostrar('mensaje ok', texto);
    }

    error(texto) {
        this.#mostrar('mensaje error', texto);
    }

    limpiar() {
        Dom.vaciar(this.#contenedor);
    }

    #mostrar(clases, texto) {
        this.limpiar();
        this.#contenedor.appendChild(Dom.elemento('div', clases, texto));
    }
}
