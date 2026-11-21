class PaginaSprints {
    static #AVISOS = new Map([
        ['creado', 'Sprint registrado correctamente.']
    ]);

    #sprintsApi = new SprintsApi();
    #mensaje = new Mensaje('mensaje');
    #contenedor = document.getElementById('tabla-sprints');

    async iniciar() {
        new Menu('menu').pintar();

        const clave = new URLSearchParams(window.location.search).get('ok');
        const aviso = PaginaSprints.#AVISOS.get(clave);
        if (aviso) {
            this.#mensaje.ok(aviso);
        }

        try {
            this.#pintar(await this.#sprintsApi.listar());
        } catch (error) {
            this.#mensaje.error(error.message);
        }
    }

    #pintar(sprints) {
        Dom.vaciar(this.#contenedor);
        const filas = sprints.map((sprint) => [sprint.nombre, sprint.fecha_inicio, sprint.fecha_fin]);
        this.#contenedor.appendChild(Dom.tabla(['Nombre', 'Inicio', 'Fin'], filas));
    }
}

document.addEventListener('DOMContentLoaded', () => new PaginaSprints().iniciar());
