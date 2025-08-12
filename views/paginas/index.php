<main class="garantia contenedor seccion">
    <h2>Nuestra Garantía de Confianza</h2>

    <p>Tres pilares que aseguran el éxito de su inversión y su total tranquilidad.</p>

    <div class="garantia__indicadores">
        <span class="garantia__indicador active"></span>
        <span class="garantia__indicador"></span>
        <span class="garantia__indicador"></span>
    </div>

    <div class="garantia__contenido">
        <div class="garantia__card-principal">
            <?php foreach ($garantia_cards as $key => $card) { ?>
                <div class="garantia__card-contenido <?php echo ($key === 0) ? 'active' : ''; ?>" id="garantia-<?php echo $key + 1; ?>">
                    <div class="garantia__card-texto">
                        <h3><?php echo $card['title']; ?></h3>
                        <p><?php echo $card['text']; ?></p>
                    </div>
                    <div class="garantia__card-imagen">
                        <img src="<?php echo $card['icon']; ?>" alt="Icono representativo de <?php echo $card['title']; ?>">
                    </div>
                </div>
            <?php } ?>
        </div>

        <div class="garantia__triggers">
            <?php foreach ($garantia_cards as $key => $card) { ?>
                <div class="garantia__trigger <?php echo ($key === 0) ? 'active' : ''; ?>" data-target="#garantia-<?php echo $key + 1; ?>" data-index="<?php echo $key; ?>">
                    <p><?php echo $card['title']; ?></p>
                </div>
            <?php } ?>
        </div>
    </div>
</main>