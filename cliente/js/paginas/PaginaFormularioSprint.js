class PaginaFormularioSprint {
    #sprintsApi = new SprintsApi();
    #mensaje = new Mensaje('mensaje');
    #formulario = document.getElementById('formulario');

    iniciar() {
        new Menu('menu').pintar();
        this.#formulario.addEventListener('submit', (evento) => this.#guardar(evento));
    }

    async #guardar(evento) {
        evento.preventDefault();
        const campos = this.#formulario.elements;

        try {
            await this.#sprintsApi.crear({
                nombre: campos['nombre'].value,
                fecha_inicio: campos['fecha_inicio'].value,
                fecha_fin: campos['fecha_fin'].value
            });
            window.location.href = 'sprints.html?ok=creado';
        } catch (error) {
            this.#mensaje.error(error.message);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => new PaginaFormularioSprint().iniciar());
