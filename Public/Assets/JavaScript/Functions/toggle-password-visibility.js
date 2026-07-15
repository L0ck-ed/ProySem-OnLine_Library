console.log("El archivo de contraseña cargó correctamente.");

document.addEventListener("click", function (event) {
  const button = event.target.closest("#togglePassword");

  if (!button) {
    return;
  }

  const passwordInput = document.getElementById("password");

  if (!passwordInput) {
    console.error("No se encontró el campo #password.");
    return;
  }

  const estaOculta = passwordInput.type === "password";

  passwordInput.type = estaOculta ? "text" : "password";
  button.textContent = estaOculta ? "Ocultar" : "Ver";
});
