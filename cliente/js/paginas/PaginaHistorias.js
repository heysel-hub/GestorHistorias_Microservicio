class PaginaHistorias {
    static #AVISOS = new Map([
        ['creada', 'Historia registrada correctamente.'],
        ['modificada', 'Historia modificada correctamente.']
    ]);

    #historiasApi = new HistoriasApi();
    #sprintsApi = new SprintsApi();
    #mensaje = new Mensaje('mensaje');
    #listado = document.getElementById('listado');

    async iniciar() {
        new Menu('menu').pintar();
        this.#mostrarAvisoDeLaUrl();
        await this.#cargar();
    }

    #mostrarAvisoDeLaUrl() {
        const clave = new URLSearchParams(window.location.search).get('ok');
        const texto = PaginaHistorias.#AVISOS.get(clave);
        if (texto) {
            this.#mensaje.ok(texto);
        }
    }

    async #cargar() {
        try {
            // Se piden los dos servicios al mismo tiempo.
            const [sprints, historias] = await Promise.all([
                this.#sprintsApi.listar(),
                this.#historiasApi.listar()
            ]);
            this.#pintar(sprints, historias);
        } catch (error) {
            this.#mensaje.error(error.message);
        }
    }

    #pintar(sprints, historias) {
        Dom.vaciar(this.#listado);

        if (sprints.length === 0) {
            this.#listado.appendChild(Dom.elemento('p', 'vacio centrado', 'Todavía no hay sprints. Crea uno desde el menú.'));
            return;
        }

        for (const sprint of sprints) {
            const delSprint = historias.filter((historia) => historia.sprint_id === sprint.id);
            this.#listado.appendChild(this.#crearSeccion(sprint, delSprint));
        }
    }

    #crearSeccion(sprint, historias) {
        const seccion = Dom.elemento('section', 'sprint');

        const encabezado = Dom.elemento('div', 'sprint-encabezado');
        encabezado.appendChild(Dom.elemento('h2', '', sprint.nombre));
        encabezado.appendChild(Dom.elemento('span', 'fechas', sprint.fecha_inicio + ' → ' + sprint.fecha_fin));
        seccion.appendChild(encabezado);

        const tarjetas = Dom.elemento('div', 'tarjetas');
        if (historias.length === 0) {
            tarjetas.appendChild(Dom.elemento('p', 'vacio', 'Este sprint aún no tiene historias.'));
        }
        for (const historia of historias) {
            tarjetas.appendChild(this.#crearTarjeta(historia));
        }
        seccion.appendChild(tarjetas);

        return seccion;
    }

    #crearTarjeta(historia) {
        const tarjeta = Dom.elemento('div', 'tarjeta ' + historia.estado);
        tarjeta.appendChild(Dom.elemento('h3', '', historia.titulo));

        const parrafoEstado = Dom.elemento('p');
        parrafoEstado.appendChild(Dom.elemento('span', 'estado ' + historia.estado, historia.estado));
        tarjeta.appendChild(parrafoEstado);

        tarjeta.appendChild(Dom.elemento('p', '', historia.descripcion));
        tarjeta.appendChild(this.#dato('Responsable: ', historia.responsable));
        tarjeta.appendChild(this.#dato('Puntos: ', historia.puntos));
        tarjeta.appendChild(this.#dato('Creada: ', historia.fecha_creacion));
        if (historia.fecha_finalizacion) {
            tarjeta.appendChild(this.#dato('Finalizada: ', historia.fecha_finalizacion));
        }

        const acciones = Dom.elemento('div', 'acciones');

        const modificar = Dom.elemento('a', '', 'Modificar');
        modificar.href = 'formulario_historia.html?id=' + historia.id;
        acciones.appendChild(modificar);

        const borrar = Dom.elemento('button', 'borrar', 'Borrar');
        borrar.type = 'button';
        borrar.addEventListener('click', () => this.#borrar(historia.id));
        acciones.appendChild(borrar);

        tarjeta.appendChild(acciones);
        return tarjeta;
    }

    #dato(etiqueta, valor) {
        const parrafo = Dom.elemento('p');
        parrafo.appendChild(Dom.elemento('b', '', etiqueta));
        parrafo.appendChild(document.createTextNode(String(valor)));
        return parrafo;
    }

    async #borrar(id) {
        if (!window.confirm('¿Borrar esta historia?')) {
            return;
        }
        try {
            await this.#historiasApi.eliminar(id);
            this.#mensaje.ok('Historia borrada correctamente.');
            await this.#cargar();
        } catch (error) {
            this.#mensaje.error(error.message);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => new PaginaHistorias().iniciar());
