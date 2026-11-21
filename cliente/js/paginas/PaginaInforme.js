class PaginaInforme {
    #informesApi = new InformesApi();
    #mensaje = new Mensaje('mensaje');
    #contenedor = document.getElementById('contenido-informe');

    async iniciar() {
        new Menu('menu').pintar();
        try {
            this.#pintar(await this.#informesApi.obtener());
        } catch (error) {
            this.#mensaje.error(error.message);
        }
    }

    #pintar(informe) {
        Dom.vaciar(this.#contenedor);

        this.#contenedor.appendChild(Dom.elemento('h2', '', 'General'));
        this.#contenedor.appendChild(this.#crearResumen(informe.general));

        const encabezados = ['Total', 'Finalizadas', 'Pendientes', 'Con impedimento'];

        this.#contenedor.appendChild(Dom.elemento('h2', '', 'Por responsable'));
        this.#contenedor.appendChild(Dom.tabla(
            ['Responsable'].concat(encabezados),
            informe.por_responsable.map((fila) => this.#fila(fila.responsable, fila))
        ));

        this.#contenedor.appendChild(Dom.elemento('h2', '', 'Por sprint'));
        this.#contenedor.appendChild(Dom.tabla(
            ['Sprint'].concat(encabezados),
            informe.por_sprint.map((fila) => this.#fila(fila.nombre, fila))
        ));
    }

    #fila(nombre, datos) {
        return [nombre, datos.total, datos.finalizadas, datos.pendientes, datos.impedimentos];
    }

    #crearResumen(general) {
        const resumen = Dom.elemento('div', 'resumen');
        const tarjetas = [
            { clase: 'total', numero: general.total, texto: 'Total' },
            { clase: 'finalizadas', numero: general.finalizadas, texto: 'Finalizadas' },
            { clase: 'pendientes', numero: general.pendientes, texto: 'Pendientes' },
            { clase: 'impedimentos', numero: general.impedimentos, texto: 'Con impedimento' }
        ];

        for (const tarjeta of tarjetas) {
            const dato = Dom.elemento('div', 'dato ' + tarjeta.clase);
            dato.appendChild(Dom.elemento('strong', '', tarjeta.numero));
            dato.appendChild(document.createTextNode(tarjeta.texto));
            resumen.appendChild(dato);
        }
        return resumen;
    }
}

document.addEventListener('DOMContentLoaded', () => new PaginaInforme().iniciar());
