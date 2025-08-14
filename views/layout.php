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

    <link rel="icon" type="image/png" href="/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Dilae Solar" />
    <link rel="manifest" href="/favicon/site.webmanifest" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" integrity="sha512-1sCRPdkRXhBV2PBLUdRb4tMg1w2YPf37qatUFeS7zlBy7jJI8Lf4VHwWfZZfpXtYSLy85pkm9GaYVYMfw5BC1A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?php echo get_asset('app.css'); ?>">

    <script defer="defer" type="text/javascript" src="https://dashboard.dilaesolar.com/im_livechat/loader/2"></script>
    <script defer="defer" type="text/javascript" src="https://dashboard.dilaesolar.com/im_livechat/assets_embed.js"></script>

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
                            <img loading="lazy" src="/build/img/logo.png" alt="Logo de Dilae Solar" class="logo">
                        </picture>
                    </a>

                    <nav class="navegacion">
                        <a href="/nosotros">Nosotros</a>
                        <a href="/soluciones">Soluciones</a>
                        <a href="/calculadora">Calculadora</a>
                        <a href="/blog">Blog</a>
                        <a href="/contacto">Contacto</a>
                    </nav>

                    <a href="/contacto" class="btn-cotizar">Obtén una Cotización</a>
                </div>
            </div>
            
            <?php if ($inicio): ?>
                <section class="hero <?php echo $inicio ? 'inicio' : ''; ?>">
                    <div class="contenido-header">
                        <div class="contenedor">
                            <h1>Deje de pagarle a CFE. Invierta en el activo más importante: <span>su hogar.</span></h1>
                            <p>Instalación de paneles solares en Guadalajara que se pagan solos y aumentan la plusvalía de su propiedad.</p>
                            <a href="/contacto" class="btn-contacto">Solicitar mi Estudio de Ahorro</a>
                        </div>
                    </div>
                </section>
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
                            <img loading="lazy" src="/build/img/logo-white.png" alt="Logo de Dilae Solar">
                        </picture>
                    </a>
                    <p>Copyright © <?php echo date('Y'); ?> <a href="/">DILAE</a></p>
                    <p>Todos los derechos reservados</p>
                    <div class="footer-social">
                        <div class="logo-container">
                            <a rel="noopener noreferrer" target="_blank" href="https://www.instagram.com">
                                <i class="fa-brands fa-instagram"></i>
                            </a>
                        </div>
                        <div class="logo-container">
                            <a rel="noopener noreferrer" target="_blank" href="https://www.facebook.com/p/DILAE-100063075438310/">
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
                        <div class="logo-container"><a href="/blog">Blog</a></div>
                        <div class="logo-container"><a href="/contacto">Contacto</a></div>
                    </div>
                </div>

                <div class="footer-info">
                    <h3>Oficinas</h3>
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
                        <div class="logo-container"><a href="/privacidad">Aviso de Privacidad</a></div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <script src="<?php echo get_asset('app.js'); ?>" defer></script>
</body>
</html>