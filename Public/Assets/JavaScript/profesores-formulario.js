document.addEventListener('DOMContentLoaded', () => {
    const facultadSelect = document.getElementById('id_facultad');

    const departamentoSelect = document.getElementById('id_departamento');

    const estadoDepartamentos = document.getElementById('estadoDepartamentos');

    if (!facultadSelect || !departamentoSelect || !estadoDepartamentos) {
        return;
    }

    const urlDepartamentos = facultadSelect.dataset.urlDepartamentos;

    if (!urlDepartamentos) {
        estadoDepartamentos.textContent = 'No se configuró la ruta de departamentos.';

        return;
    }

    async function cargarDepartamentos(idFacultad, idDepartamentoSeleccionado = 0) {
        departamentoSelect.disabled = true;

        departamentoSelect.innerHTML = '<option value="">Cargando departamentos...</option>';

        estadoDepartamentos.textContent = 'Consultando los departamentos disponibles.';

        if (!idFacultad) {
            departamentoSelect.innerHTML =
                '<option value="">Primero seleccione una facultad</option>';

            estadoDepartamentos.textContent = 'Los departamentos se cargarán según la facultad.';

            return;
        }

        try {
            const url = new URL(urlDepartamentos, window.location.origin);

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
                throw new Error(datos.message || 'No se pudieron cargar los departamentos.');
            }

            departamentoSelect.innerHTML = '<option value="">Seleccione un departamento</option>';

            datos.departamentos.forEach((departamento) => {
                const opcion = document.createElement('option');

                opcion.value = departamento.id_departamento;

                opcion.textContent = departamento.nombre;

                if (Number(departamento.id_departamento) === Number(idDepartamentoSeleccionado)) {
                    opcion.selected = true;
                }

                departamentoSelect.appendChild(opcion);
            });

            const tieneDepartamentos = datos.departamentos.length > 0;

            departamentoSelect.disabled = !tieneDepartamentos;

            estadoDepartamentos.textContent = tieneDepartamentos
                ? `${datos.departamentos.length} departamento(s) disponible(s).`
                : 'Esta facultad no tiene departamentos activos.';
        } catch (error) {
            departamentoSelect.innerHTML =
                '<option value="">No se pudieron cargar los departamentos</option>';

            departamentoSelect.disabled = true;

            estadoDepartamentos.textContent =
                error instanceof Error
                    ? error.message
                    : 'Ocurrió un error al cargar los departamentos.';
        }
    }

    facultadSelect.addEventListener('change', () => {
        departamentoSelect.dataset.departamentoSeleccionado = '0';

        cargarDepartamentos(facultadSelect.value);
    });

    const facultadInicial = facultadSelect.value;

    const departamentoInicial = Number(departamentoSelect.dataset.departamentoSeleccionado || 0);

    if (facultadInicial) {
        cargarDepartamentos(facultadInicial, departamentoInicial);
    }
});
