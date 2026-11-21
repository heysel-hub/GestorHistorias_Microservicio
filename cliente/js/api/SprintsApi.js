class SprintsApi {
    #http = new ClienteHttp(Configuracion.urlSprints);

    listar() {
        return this.#http.listar();
    }

    crear(sprint) {
        return this.#http.crear(sprint);
    }
}
