(() => {
    'use strict';

    const form = document.querySelector('.estructura-form');
    const facultadSelect = document.getElementById('id_facultad');
    const departamentoSelect = document.getElementById('id_departamento');

    if (!form || !facultadSelect || !departamentoSelect) {
        return;
    }

    const endpoint = form.dataset.departamentosUrl || '';
    let selectedDepartment = Number(form.dataset.departamentoSeleccionado || 0);

    const setLoading = (loading) => {
        departamentoSelect.disabled = loading || !facultadSelect.value;
        departamentoSelect.innerHTML = loading
            ? '<option value="">Cargando departamentos…</option>'
            : '<option value="">Sin departamento específico</option>';
    };

    const loadDepartments = async () => {
        const facultyId = Number(facultadSelect.value || 0);

        if (!facultyId || !endpoint) {
            setLoading(false);
            departamentoSelect.disabled = true;
            selectedDepartment = 0;
            return;
        }

        setLoading(true);

        try {
            const separator = endpoint.includes('?') ? '&' : '?';
            const response = await fetch(`${endpoint}${separator}id_facultad=${facultyId}`, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                throw new Error('No se pudieron cargar los departamentos.');
            }

            const payload = await response.json();
            departamentoSelect.innerHTML = '<option value="">Sin departamento específico</option>';

            (payload.departamentos || []).forEach((department) => {
                const option = document.createElement('option');
                option.value = String(department.id_departamento);
                option.textContent = department.nombre;
                option.selected = Number(department.id_departamento) === selectedDepartment;
                departamentoSelect.appendChild(option);
            });

            departamentoSelect.disabled = false;
        } catch (error) {
            console.error(error);
            departamentoSelect.innerHTML = '<option value="">No se pudieron cargar los departamentos</option>';
            departamentoSelect.disabled = true;
        }
    };

    facultadSelect.addEventListener('change', () => {
        selectedDepartment = 0;
        loadDepartments();
    });

    if (facultadSelect.value) {
        loadDepartments();
    } else {
        departamentoSelect.disabled = true;
    }
})();
