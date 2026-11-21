class InformesApi {
    #http = new ClienteHttp(Configuracion.urlInformes);

    obtener() {
        return this.#http.listar();   // GET /informes devuelve el informe completo
    }
}
