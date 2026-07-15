USE BibliotecaDigitalDB;
GO

-- Ejecuta este script solo cuando necesites reiniciar las pruebas del login.
-- Desbloquea las cuentas y devuelve el contador de intentos a cero.
UPDATE usuarios
SET
    intentos_fallidos = 0,
    bloqueado = 0,
    bloqueado_hasta = NULL;
GO

SELECT
    id_usuario,
    usuario,
    intentos_fallidos,
    bloqueado,
    bloqueado_hasta
FROM usuarios
ORDER BY id_usuario;
GO
