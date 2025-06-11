<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dilae Solar | <?php echo $titulo; ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="/build/css/app.css?v=<?= time() ?>">
</head>
<body class="<?php echo $body_class ?? ''; ?>">
    <?php echo $contenido; ?>
</body>
</html>