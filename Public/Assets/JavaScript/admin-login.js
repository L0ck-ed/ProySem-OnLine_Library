document.addEventListener("DOMContentLoaded", () => {
  const passwordInput = document.getElementById("password");
  const toggleButton = document.getElementById("toggleAdminPassword");

  if (!passwordInput || !toggleButton) {
    return;
  }

  toggleButton.addEventListener("click", () => {
    const mostrar = passwordInput.type === "password";

    passwordInput.type = mostrar ? "text" : "password";
    toggleButton.setAttribute("aria-pressed", String(mostrar));
    toggleButton.setAttribute(
      "aria-label",
      mostrar ? "Ocultar contraseña" : "Mostrar contraseña",
    );

    const icono = toggleButton.querySelector("i");

    if (icono) {
      icono.className = mostrar
        ? "fa-solid fa-eye-slash"
        : "fa-solid fa-eye";
    }
  });
});
