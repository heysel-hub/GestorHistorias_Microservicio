 #http = new ClienteHttp(Configuracion.urlHistorias);

    listar() {
        return this.#http.listar();
    }

    obtener(id) {
        return this.#http.obtener(id);
    }

    crear(historia) {
        return this.#http.crear(historia);
    }

    modificar(id, historia) {
        return this.#http.modificar(id, historia);
    }

    eliminar(id) {
        return this.#http.eliminar(id);
    }
}
