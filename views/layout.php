<?php
    if(!isset($inicio)) {
        $inicio = false;
    }

    // Configuración base
    $site_name = "Dilae Solar";
    $base_url = $_ENV['HOST']; // Asegúrate que HOST en tu .env es la URL completa (ej. https://dilaesolar.com)

    // Estructura de datos SEO
    $seo = [
        'title' => $titulo ?? 'Paneles Solares en Guadalajara',
        'meta_description' => $meta_description ?? 'Instalación profesional de energía solar en Guadalajara. Invierta en paneles solares para su hogar o negocio y reduzca su factura de CFE hasta un 90%',
        'canonical' => rtrim($base_url, '/') . strtok($_SERVER['REQUEST_URI'], '?'),
        'og_type' => $og_type ?? 'website',
        'og_image' => $og_image ?? $base_url . '/build/img/logo.png', // Crea una imagen para redes sociales
        'og_image_alt' => $og_image_alt ?? 'Logo de DILAE Solar',
    ];

    // Generar el título final de la página
    $final_title = $inicio ? $site_name . ' | Paneles Solares en Guadalajara' : $seo['title'] . ' | ' . $site_name;
?>

<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Preconnect to Required Origins -->
    <link rel="preconnect" href="https://analytics.dilaesolar.com" crossorigin>
    <link rel="dns-prefetch" href="https://analytics.dilaesolar.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    
    <title><?php echo htmlspecialchars($final_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seo['meta_description']); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($seo['canonical']); ?>" />

    <!-- SEO para Redes Sociales -->
    <meta property="og:title" content="<?php echo htmlspecialchars($final_title); ?>" />
    <meta property="og:description" content="<?php echo htmlspecialchars($seo['meta_description']); ?>" />
    <meta property="og:type" content="<?php echo htmlspecialchars($seo['og_type']); ?>" />
    <meta property="og:url" content="<?php echo htmlspecialchars($seo['canonical']); ?>" />
    <meta property="og:image" content="<?php echo htmlspecialchars($seo['og_image']); ?>" />
    <meta property="og:image:alt" content="<?php echo htmlspecialchars($seo['og_image_alt']); ?>" />
    <meta property="og:site_name" content="<?php echo htmlspecialchars($site_name); ?>" />
    <meta property="og:locale" content="es_MX" />

    <!-- Schema de la empresa -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Dilae Solar",
        "url": "https://www.dilaesolar.com/",
        "logo": "https://www.dilaesolar.com/build/img/logo.png",
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+52-33-4632-3029",
            "contactType": "Customer Service"
        }
    }
    </script>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Dilae Solar" />
    <link rel="manifest" href="/favicon/site.webmanifest" />

    <!-- Preload imagenes Hero -->
    <?php if (isset($lcp_image)): ?>
        <link 
            rel="preload" 
            as="image" 
            href="/build/img/<?php echo $lcp_image; ?>.webp" 
            imagesrcset="/build/img/<?php echo $lcp_image; ?>.avif type=image/avif, /build/img/<?php echo $lcp_image; ?>.webp type=image/webp"
            fetchpriority="high"
        >
    <?php endif; ?>
    
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="<?php echo get_asset('app.css'); ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    
    <noscript><link rel="stylesheet" href="<?php echo get_asset('app.css'); ?>"></noscript>
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css"></noscript>

    <?php if (isset($schema)) echo $schema; ?>
</head>

<body class="layout">
    <div class="layout__header">
        <header class="header">
            <div class="barra">
                <div class="contenedor">
                    <a href="/">
                        <picture>
                            <source srcset="/build/img/logo.webp" type="image/webp">
                            <source srcset="/build/img/logo.png" type="image/png">
                            <img loading="lazy" width="437" height="246" src="/build/img/logo.png" alt="Logo de Dilae Solar" class="logo">
                        </picture>
                    </a>

                    <button class="hamburguesa" id="hamburguesa" aria-label="Abrir navegación">
                        <i class="fa-solid fa-bars"></i>
                    </button>

                    <nav class="navegacion" id="navegacion">
                        <div class="navegacion__panel">
                            <button class="cerrar-nav" id="cerrar-nav" aria-label="Cerrar navegación">
                                <i class="fa-solid fa-xmark"></i>
                            </button>

                            <ul class="nav-list">
                                <li><a href="/nosotros">Nosotros</a></li>
                                <li><a href="/soluciones">Soluciones</a></li>
                                <li><a href="/calculadora">Calculadora</a></li>
                                <li><a href="/blogs">Blogs</a></li>
                                <li><a href="/contacto">Contacto</a></li>
                            </ul>
                        </div>
                    </nav>

                    <a href="/contacto" class="btn-cotizar">Obtén una Cotización</a>
                </div>
            </div>
            
            <?php if (isset($hero)): ?>
                <?php include __DIR__ . "/{$hero}.php"; ?>
            <?php endif; ?>
        </header>
    </div>

    <div class="layout__contenido">
        <?php echo $contenido; ?>
    </div>
    
    <div class="layout__footer">
        <footer class="footer">
            <div class="contenedor footer-contenedor">
                <div class="footer-logo">
                    <a href="/" class="footer-logo__brand">
                        <picture>
                            <source srcset="/build/img/logo-white.webp" type="image/webp">
                            <source srcset="/build/img/logo-white.png" type="image/png">
                            <img loading="lazy" width="1920" height="1080" src="/build/img/logo-white.png" alt="Logo de Dilae Solar">
                        </picture>
                    </a>
                    <p>Copyright © <?php echo date('Y'); ?> <a href="/">DILAE</a></p>
                    <p>Todos los derechos reservados</p>
                    <div class="footer-social">
                        <div class="logo-container">
                            <a rel="noopener noreferrer" target="_blank" href="https://www.instagram.com/dilae.solar/">
                                <i class="fa-brands fa-instagram"></i>
                            </a>
                        </div>
                        <div class="logo-container">
                            <a rel="noopener noreferrer" target="_blank" href="https://www.facebook.com/profile.php?id=61576200926732">
                                <i class="fa-brands fa-facebook"></i>
                            </a>
                        </div>
                        <div class="logo-container">
                            <a rel="noopener noreferrer" target="_blank" href="https://www.youtube.com/@dilaesadecv3250">
                                <i class="fa-brands fa-youtube"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="footer-links">
                    <h3>Enlaces</h3>

                    <div class="footer-links__enlaces">
                        <div class="logo-container"><a href="/nosotros">Nosotros</a></div>
                        <div class="logo-container"><a href="/soluciones">Soluciones</a></div>
                        <div class="logo-container"><a href="/calculadora">Calculadora</a></div>
                        <div class="logo-container"><a href="/blogs">Blogs</a></div>
                        <div class="logo-container"><a href="/contacto">Contacto</a></div>
                    </div>
                </div>

                <div class="footer-info">
                    <h3>Oficina</h3>
                    <a class="logo-container" rel="noopener noreferrer" target="_blank" href="https://maps.app.goo.gl/Nz3JU3Pzct4UUZea8">
                        Calzada de las Flores #1111-L4, Zapopan, Jalisco, México
                    </a>

                    <a class="logo-container" href="tel:+523346323029">
                        <i class="fa-solid fa-phone"></i>
                        +52 (33) 4632-3029
                    </a>

                    <a class="logo-container" href="mailto:contacto@dilaesolar.com">
                        <i class="fa-solid fa-envelope"></i>
                        contacto@dilaesolar.com
                    </a>

                    <p class="logo-container">Horario: L-V, 9:00 - 18:00 hrs</p>
                </div>

                <div class="footer-contacto">
                    <div class="footer-subscribe">
                        <h3>Mantente actualizado</h3>
                        <form action="/subscribe" method="POST" class="form-suscripcion-ajax">
                            <input type="email" name="email" placeholder="Tu correo electrónico" required>
                            <button type="submit" aria-label="Suscribirse">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>

                    <div class="footer-soporte">
                        <h3>Soporte</h3>
                        <div class="logo-container"><a href="/terminos">Términos de Servicio</a></div>
                        <div class="logo-container"><a href="/privacy">Aviso de Privacidad</a></div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-BSSJTDK4SQ"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-BSSJTDK4SQ');
    </script>

    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
        var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
        (function(){
        var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
        s1.async=true;
        s1.src='https://embed.tawk.to/68b8802d46ae59192624737c/1j48b9d1c';
        s1.charset='UTF-8';
        s1.setAttribute('crossorigin','*');
        s0.parentNode.insertBefore(s1,s0);
        })();
    </script>
    
    <script src="<?php echo get_asset('app.js'); ?>" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-annotation/3.0.1/chartjs-plugin-annotation.min.js" defer></script>
</body>
</html>