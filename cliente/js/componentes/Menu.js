class Menu {
    static #ENLACES = [
        { texto: 'Historias', destino: 'index.html' },
        { texto: 'Nueva historia', destino: 'formulario_historia.html' },
        { texto: 'Sprints', destino: 'sprints.html' },
        { texto: 'Informe', destino: 'informe.html' }
    ];

    #contenedor;

    constructor(idContenedor) {
        this.#contenedor = document.getElementById(idContenedor);
    }

    pintar() {
        for (const enlace of Menu.#ENLACES) {
            const a = Dom.elemento('a', '', enlace.texto);
            a.href = enlace.destino;
            this.#contenedor.appendChild(a);
        }
    }
}
