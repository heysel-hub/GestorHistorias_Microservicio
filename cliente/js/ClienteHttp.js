class ClienteHttp {
    #urlBase;

    constructor(urlBase) {
        this.#urlBase = urlBase;
    }

    listar() {
        return this.#enviar('GET', '');
    }

    obtener(id) {
        return this.#enviar('GET', this.#ruta(id));
    }

    crear(datos) {
        return this.#enviar('POST', '', datos);
    }

    modificar(id, datos) {
        return this.#enviar('PUT', this.#ruta(id), datos);
    }

    eliminar(id) {
        return this.#enviar('DELETE', this.#ruta(id));
    }

    #ruta(id) {
        return '/' + encodeURIComponent(id);
    }

    async #enviar(metodo, ruta, cuerpo) {
        const opciones = { method: metodo, headers: { 'Accept': 'application/json' } };
        if (cuerpo !== undefined) {
            opciones.headers['Content-Type'] = 'application/json';
            opciones.body = JSON.stringify(cuerpo);
        }

        let respuesta;
        try {
            respuesta = await fetch(this.#urlBase + ruta, opciones);
        } catch (error) {
            throw new Error('No se pudo conectar con el servicio. ¿Está encendido Apache?');
        }

        let contenido;
        try {
            contenido = await respuesta.json();
        } catch (error) {
            throw new Error('El servicio devolvió una respuesta que no se pudo leer.');
        }

        if (!respuesta.ok || !contenido.ok) {
           throw new Error(contenido.mensaje || contenido.message || 'El servicio respondió con un error.');
        }
        return contenido.datos;
    }
}
