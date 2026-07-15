document.addEventListener("DOMContentLoaded", () => {
  const inputClave = document.getElementById("clave");
  const botonMostrar = document.getElementById("mostrarClave");

  if (!inputClave || !botonMostrar) {
    return;
  }

  botonMostrar.addEventListener("click", () => {
    const mostrar = inputClave.type === "password";

    inputClave.type = mostrar ? "text" : "password";

    const icono = botonMostrar.querySelector("i");

    if (icono) {
      icono.className = mostrar
        ? "fa-solid fa-eye-slash"
        : "fa-solid fa-eye";
    }

    botonMostrar.setAttribute(
      "aria-label",
      mostrar ? "Ocultar contraseña" : "Mostrar contraseña",
    );
  });
});
