class PaginaFormularioHistoria {
    static #ESTADOS = ['nueva', 'activa', 'finalizada', 'impedimento'];

    #historiasApi = new HistoriasApi();
    #sprintsApi = new SprintsApi();
    #mensaje = new Mensaje('mensaje');
    #formulario = document.getElementById('formulario');
    #avisoSinSprints = document.getElementById('aviso-sin-sprints');
    #titulo = document.getElementById('titulo-pagina');
    #idHistoria = new URLSearchParams(window.location.search).get('id');   // null = modo crear

    async iniciar() {
        new Menu('menu').pintar();
        this.#llenarEstados();
        this.#formulario.addEventListener('submit', (evento) => this.#guardar(evento));

        try {
            const sprints = await this.#sprintsApi.listar();
            if (sprints.length === 0) {
                this.#avisoSinSprints.classList.remove('oculto');
                return;
            }
            this.#llenarSprints(sprints);

            if (this.#idHistoria !== null) {
                this.#titulo.textContent = 'Modificar historia de usuario';
                this.#rellenar(await this.#historiasApi.obtener(this.#idHistoria));
            }
            this.#formulario.classList.remove('oculto');
        } catch (error) {
            this.#mensaje.error(error.message);
        }
    }

    #llenarEstados() {
        const lista = this.#formulario.elements['estado'];
        for (const estado of PaginaFormularioHistoria.#ESTADOS) {
            lista.appendChild(this.#opcion(estado, estado));
        }
    }

    #llenarSprints(sprints) {
        const lista = this.#formulario.elements['sprint_id'];
        for (const sprint of sprints) {
            lista.appendChild(this.#opcion(sprint.id, sprint.nombre));
        }
    }

    #opcion(valor, texto) {
        const opcion = Dom.elemento('option', '', texto);
        opcion.value = valor;
        return opcion;
    }

    #rellenar(historia) {
        const campos = this.#formulario.elements;
        campos['titulo'].value = historia.titulo;
        campos['descripcion'].value = historia.descripcion;
        campos['responsable'].value = historia.responsable;
        campos['puntos'].value = historia.puntos;
        campos['estado'].value = historia.estado;
        campos['sprint_id'].value = historia.sprint_id;
    }

    #leerDatos() {
        const campos = this.#formulario.elements;
        return {
            titulo: campos['titulo'].value,
            descripcion: campos['descripcion'].value,
            responsable: campos['responsable'].value,
            puntos: campos['puntos'].value,
            estado: campos['estado'].value,
            sprint_id: campos['sprint_id'].value
        };
    }

    async #guardar(evento) {
        evento.preventDefault();
        const datos = this.#leerDatos();

        try {
            if (this.#idHistoria === null) {
                await this.#historiasApi.crear(datos);
                window.location.href = 'index.html?ok=creada';
            } else {
                await this.#historiasApi.modificar(this.#idHistoria, datos);
                window.location.href = 'index.html?ok=modificada';
            }
        } catch (error) {
            this.#mensaje.error(error.message);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => new PaginaFormularioHistoria().iniciar());
