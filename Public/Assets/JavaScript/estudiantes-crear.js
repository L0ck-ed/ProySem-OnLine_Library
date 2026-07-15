document.addEventListener('DOMContentLoaded', () => {
    const facultadSelect = document.getElementById('id_facultad');
    const carreraSelect = document.getElementById('id_carrera');
    const estadoCarreras = document.getElementById('estadoCarreras');

    if (!facultadSelect || !carreraSelect || !estadoCarreras) {
        return;
    }

    const urlCarreras = facultadSelect.dataset.urlCarreras;

    if (!urlCarreras) {
        estadoCarreras.textContent = 'No se configuró la ruta para cargar las carreras.';

        return;
    }

    async function cargarCarreras(idFacultad, idCarreraSeleccionada = 0) {
        carreraSelect.disabled = true;

        carreraSelect.innerHTML = '<option value="">Cargando carreras...</option>';

        estadoCarreras.textContent = 'Consultando las carreras disponibles.';

        if (!idFacultad) {
            carreraSelect.innerHTML = '<option value="">Primero seleccione una facultad</option>';

            estadoCarreras.textContent = 'Las carreras se cargarán según la facultad.';

            return;
        }

        try {
            const url = new URL(urlCarreras, window.location.origin);

            url.searchParams.set('id_facultad', idFacultad);

            const respuesta = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
            });

            const datos = await respuesta.json();

            if (!respuesta.ok || !datos.success) {
                throw new Error(datos.message || 'No se pudieron cargar las carreras.');
            }

            carreraSelect.innerHTML = '<option value="">Seleccione una carrera</option>';

            datos.carreras.forEach((carrera) => {
                const opcion = document.createElement('option');

                opcion.value = carrera.id_carrera;
                opcion.textContent = carrera.nombre;

                if (Number(carrera.id_carrera) === Number(idCarreraSeleccionada)) {
                    opcion.selected = true;
                }

                carreraSelect.appendChild(opcion);
            });

            const tieneCarreras = datos.carreras.length > 0;

            carreraSelect.disabled = !tieneCarreras;

            estadoCarreras.textContent = tieneCarreras
                ? `${datos.carreras.length} carrera(s) disponible(s).`
                : 'Esta facultad no tiene carreras activas.';
        } catch (error) {
            carreraSelect.innerHTML =
                '<option value="">No se pudieron cargar las carreras</option>';

            carreraSelect.disabled = true;

            estadoCarreras.textContent =
                error instanceof Error ? error.message : 'Ocurrió un error al cargar las carreras.';
        }
    }

    facultadSelect.addEventListener('change', () => {
        carreraSelect.dataset.carreraSeleccionada = '0';

        cargarCarreras(facultadSelect.value);
    });

    const facultadInicial = facultadSelect.value;

    const carreraInicial = Number(carreraSelect.dataset.carreraSeleccionada || 0);

    if (facultadInicial) {
        cargarCarreras(facultadInicial, carreraInicial);
    }
});
