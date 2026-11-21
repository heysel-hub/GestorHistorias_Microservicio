class Configuracion {
    static #SPRINTS = '../servicios/sprints/public/sprints';
    static #HISTORIAS = '../servicios/historias/public/historias';
    static #INFORMES = '../servicios/informes/public/informes';

    static get urlSprints() { return Configuracion.#SPRINTS; }
    static get urlHistorias() { return Configuracion.#HISTORIAS; }
    static get urlInformes() { return Configuracion.#INFORMES; }
}
