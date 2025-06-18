<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dilae Solar | <?php echo $titulo ?? 'En Construcción'; ?></title>
    <meta name="description" content="<?php echo $meta_description ?? 'Sitio web oficial de Dilae Solar. Próximamente con más información.'; ?>">

    <link rel="icon" type="image/png" href="/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Dilae Solar" />
    <link rel="manifest" href="/favicon/site.webmanifest" />

    <link rel="stylesheet" href="/build/css/app.css?v=<?= time() ?>">
</head>
<body class="construccion-bg">
    <?php echo $contenido; ?>
</body>
</html>