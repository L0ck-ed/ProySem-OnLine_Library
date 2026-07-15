document.addEventListener('DOMContentLoaded', () => {
    const contenedor = document.getElementById('selectorAcceso');

    const botonRegular = document.getElementById('mostrarRegular');

    const botonAdministrativo = document.getElementById('mostrarAdministrativo');

    if (!contenedor || !botonRegular || !botonAdministrativo) {
        return;
    }

    const mostrarRegular = () => {
        contenedor.classList.add('activo');

        sessionStorage.setItem('tipoAccesoSeleccionado', 'regular');
    };

    const mostrarAdministrativo = () => {
        contenedor.classList.remove('activo');

        sessionStorage.setItem('tipoAccesoSeleccionado', 'administrativo');
    };

    botonRegular.addEventListener('click', mostrarRegular);

    botonAdministrativo.addEventListener('click', mostrarAdministrativo);

    const accesoGuardado = sessionStorage.getItem('tipoAccesoSeleccionado');

    if (accesoGuardado === 'regular') {
        contenedor.classList.add('activo');
    }
});
