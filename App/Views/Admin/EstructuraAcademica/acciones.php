<?php

use App\Config\Config;
use App\Middleware\Auth;

$idCampo = 'id_' . $tipoFila;
$idFila = (int) ($fila[$idCampo] ?? 0);
$estadoFila = (int) ($fila['estado'] ?? 0);
?>
<div class="admin-actions justify-content-end">
    <?php if (Auth::tienePermiso('estructura.editar')): ?>
        <a
            href="<?= Config::url('estructura-academica/editar?tipo=' . $tipoFila . '&id=' . $idFila) ?>"
            class="btn btn-sm btn-outline-primary"
            title="Editar"
        >
            <i class="fa-solid fa-pen-to-square"></i>
            Editar
        </a>
    <?php endif; ?>

    <?php if (Auth::tienePermiso('estructura.eliminar')): ?>
        <form
            method="POST"
            action="<?= Config::url('estructura-academica/cambiar-estado') ?>"
            onsubmit="return confirm('¿Seguro que deseas <?= $estadoFila === 1 ? 'desactivar' : 'activar' ?> este registro?');"
        >
            <input type="hidden" name="tipo" value="<?= $escapar($tipoFila) ?>">
            <input type="hidden" name="id_registro" value="<?= $idFila ?>">
            <input type="hidden" name="estado" value="<?= $estadoFila === 1 ? 0 : 1 ?>">
            <button type="submit" class="btn btn-sm <?= $estadoFila === 1 ? 'btn-warning' : 'btn-success' ?>">
                <i class="fa-solid <?= $estadoFila === 1 ? 'fa-ban' : 'fa-check' ?>"></i>
                <?= $estadoFila === 1 ? 'Desactivar' : 'Activar' ?>
            </button>
        </form>

        <form
            method="POST"
            action="<?= Config::url('estructura-academica/eliminar') ?>"
            onsubmit="return confirm('Esta acción elimina el registro definitivamente si no tiene datos relacionados. ¿Deseas continuar?');"
        >
            <input type="hidden" name="tipo" value="<?= $escapar($tipoFila) ?>">
            <input type="hidden" name="id_registro" value="<?= $idFila ?>">
            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar definitivamente">
                <i class="fa-solid fa-trash"></i>
            </button>
        </form>
    <?php endif; ?>
</div>
<?php
unset($tipoFila, $fila, $idCampo, $idFila, $estadoFila);
?>
