<?php
    if(!isset($inicio)) {
        $inicio = false;
    }

    // Configuración base
    $site_name = "Dilae Solar";
    $base_url = $_ENV['HOST']; // Asegúrate que HOST en tu .env es la URL completa (ej. https://www.dilaesolar.com)

    // Estructura de datos SEO
    $seo = [
        'title' => $titulo ?? 'Soluciones en Iluminación',
        'meta_description' => $meta_description ?? 'Empresa mexicana especialista en proyección, diseño y consultoría en iluminación y ahorro de energía. Soluciones para proyectos de cualquier magnitud.',
        'canonical' => $base_url . strtok($_SERVER['REQUEST_URI'], '?'),
        'og_type' => $og_type ?? 'website',
        'og_image' => $og_image ?? $base_url . '/build/img/logo.png', // Crea una imagen para redes sociales
        'og_image_alt' => $og_image_alt ?? 'Logo de DILAE Solar',
    ];

    // Generar el título final de la página
    $final_title = $inicio ? $site_name . ' | Soluciones Integrales de Iluminación' : $seo['title'] . ' | ' . $site_name;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?php echo htmlspecialchars($final_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seo['meta_description']); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($seo['canonical']); ?>" />

    <meta property="og:title" content="<?php echo htmlspecialchars($final_title); ?>" />
    <meta property="og:description" content="<?php echo htmlspecialchars($seo['meta_description']); ?>" />
    <meta property="og:type" content="<?php echo htmlspecialchars($seo['og_type']); ?>" />
    <meta property="og:url" content="<?php echo htmlspecialchars($seo['canonical']); ?>" />
    <meta property="og:image" content="<?php echo htmlspecialchars($seo['og_image']); ?>" />
    <meta property="og:image:alt" content="<?php echo htmlspecialchars($seo['og_image_alt']); ?>" />
    <meta property="og:site_name" content="<?php echo htmlspecialchars($site_name); ?>" />
    <meta property="og:locale" content="es_MX" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" integrity="sha512-1sCRPdkRXhBV2PBLUdRb4tMg1w2YPf37qatUFeS7zlBy7jJI8Lf4VHwWfZZfpXtYSLy85pkm9GaYVYMfw5BC1A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="/build/css/app.css?v=<?= time() ?>">

    <?php if (isset($schema)) echo $schema; ?>
</head>

<body>
    <div class="layout__header">

    </div>

    <div class="layout__contenido">
        <?php echo $contenido; ?>
    </div>
    
    <div class="layout__footer">
        
    </div>
    
    <script src="/build/js/app.js?v=<?= time() ?>"></script>
</body>
</html>