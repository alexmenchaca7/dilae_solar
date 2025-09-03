<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <a class="dashboard__boton" href="/admin/suscripciones/exportar">
        <i class="fa-solid fa-file-csv"></i>
        Exportar a CSV
    </a>
</div>

<div class="dashboard__contenedor">
    <?php if (!empty($suscripciones)) : ?>
        <table class="table">
            <thead class="table__thead">
                <tr>
                    <th scope="col" class="table__th">Email</th>
                    <th scope="col" class="table__th">Fecha de Suscripción</th>
                    <th scope="col" class="table__th">Confirmado</th>
                </tr>
            </thead>
            <tbody class="table__tbody">
                <?php foreach ($suscripciones as $suscripcion) : ?>
                    <tr class="table__tr">
                        <td class="table__td"><?php echo htmlspecialchars($suscripcion->email); ?></td>
                        <td class="table__td"><?php echo date('d/m/Y', strtotime($suscripcion->fecha_suscripcion)); ?></td>
                        <td class="table__td"><?php echo $suscripcion->confirmado ? 'Sí' : 'No'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p class="t-align-center">Aún no hay suscriptores confirmados.</p>
    <?php endif; ?>
</div>

<?php echo $paginacion; ?>