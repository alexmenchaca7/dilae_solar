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
        'canonical' => $base_url . strtok($_SERVER['REQUEST_URI'], '?'),
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
    
    <!-- Font Awesome -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" integrity="sha512-1sCRPdkRXhBV2PBLUdRb4tMg1w2YPf37qatUFeS7zlBy7jJI8Lf4VHwWfZZfpXtYSLy85pkm9GaYVYMfw5BC1A==" crossorigin="anonymous" referrerpolicy="no-referrer"/></noscript>

    <!-- Estilos criticos -->
    <style>
        @charset "UTF-8";@font-face{font-family:"Font Awesome 6 Free";font-style:normal;font-weight:400;font-display:block;src:url(https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/webfonts/fa-regular-400.woff2) format("woff2"),url(https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/webfonts/fa-regular-400.ttf) format("truetype")}@font-face{font-family:"Font Awesome 6 Free";font-style:normal;font-weight:900;font-display:block;src:url(https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/webfonts/fa-solid-900.woff2) format("woff2"),url(https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/webfonts/fa-solid-900.ttf) format("truetype")}.index-garantia__selectores{display:none;flex-direction:column;gap:2rem;order:1}.index-garantia__selector{background-color:#fff;box-shadow:0 2px 4px 0 rgba(0,0,0,.25);border-radius:5px;padding:2.5rem;position:relative}.index-garantia__selector p{font-family:Roboto,sans-serif;font-weight:500;color:#6e6e6e;text-align:center;margin:0}.index-garantia__selector::before{content:"";position:absolute;left:0;bottom:0;width:80%;height:8px;background-color:#b5b8b8}.index-garantia__selector::after{content:"";position:absolute;left:0;bottom:0;width:8px;height:50%;background-color:#b5b8b8}.index-garantia__selector.activo{position:relative}.index-garantia__selector.activo::before{background-color:#c7922a}.index-garantia__selector.activo::after{background-color:#c7922a}.index-garantia__selector.activo p{color:#001f3f}.index-proceso__imagen{display:none;width:90%;max-width:120rem}.index-proceso__imagen img{width:100%}.fa-solid{-moz-osx-font-smoothing:grayscale;-webkit-font-smoothing:antialiased;display:var(--fa-display,inline-block);font-style:normal;font-variant:normal;line-height:1;text-rendering:auto}.fa-bars:before{content:"\f0c9"}.fa-xmark:before{content:"\f00d"}:host,:root{--fa-font-brands:normal 400 1em/1 "Font Awesome 6 Brands"}:host,:root{--fa-font-regular:normal 400 1em/1 "Font Awesome 6 Free"}:host,:root{--fa-font-solid:normal 900 1em/1 "Font Awesome 6 Free"}.fa-solid{font-family:"Font Awesome 6 Free";font-weight:900}html{line-height:1.15;-webkit-text-size-adjust:100%}body{margin:0}h1{font-size:2em;margin:.67em 0}a{background-color:transparent}img{border-style:none}button{font-family:inherit;font-size:100%;line-height:1.15;margin:0}button{overflow:visible}button{text-transform:none}button{-webkit-appearance:button}button::-moz-focus-inner{border-style:none;padding:0}button:-moz-focusring{outline:ButtonText dotted 1px}::-webkit-file-upload-button{-webkit-appearance:button;font:inherit}body,html{margin:0;padding:0;width:100%;height:100%}html{font-size:62.5%;box-sizing:border-box}*,:after,:before{box-sizing:inherit}body{font-family:Inter,serif;font-size:1.6rem;line-height:1.5;background-color:#fdfdfd}p{color:#000}.contenedor{width:95%;max-width:120rem;margin:0 auto}a{text-decoration:none;color:#000}img,picture{width:100%;display:block}h1,h3{font-family:Roboto,sans-serif;font-weight:600;color:#000}h1{font-size:6.4rem}h3{font-size:3rem}.btn-cotizar{background-color:#001f3f;display:inline-block;color:#fff;font-weight:600;text-decoration:none;text-transform:uppercase;padding:1rem 3rem;text-align:center;border:none;text-transform:none;border-radius:5px;padding-inline:1.4rem}.barra{background-color:#fdfdfd;position:fixed;top:0;left:0;width:100%;z-index:1002;box-shadow:0 4px 8px rgba(0,0,0,.2)}.barra>.contenedor{display:flex;justify-content:space-between;align-items:center;height:8rem}.barra .logo{height:5rem;width:auto;max-width:100%}@media (min-width:768px){.barra .logo{height:8rem}}.navegacion{position:fixed;top:0;left:0;width:100%;height:100%;opacity:0;visibility:hidden;background-color:rgba(0,0,0,0)}.navegacion__panel{position:fixed;top:0;right:0;bottom:0;width:min(55%,30rem);background-color:#1c1c1c;padding:12rem 4rem 4rem;z-index:1001;transform:translateX(100%)}.nav-list{list-style-type:none;margin:0;padding:0;display:flex;flex-direction:column;align-items:flex-end;gap:3.5rem}.nav-list li{opacity:0;transform:translateY(20px)}.navegacion a{color:#fff;font-size:2rem;font-weight:500}.btn-cotizar{display:none}.hamburguesa{all:unset;box-sizing:border-box;display:initial;font-size:3rem;color:#001f3f}@media (min-width:900px){.navegacion__panel{all:unset}.navegacion{position:static;width:auto;height:auto;background-color:transparent;opacity:1;visibility:visible}.nav-list{flex-direction:row;align-items:center}.nav-list li{all:unset}.navegacion a{color:#18191f;font-size:initial;font-weight:initial}.btn-cotizar{display:initial}.hamburguesa{display:none}.cerrar-nav{display:none}}.cerrar-nav{all:unset;box-sizing:border-box;position:absolute;top:4rem;right:4rem;font-size:3rem;color:#fff}.hero{position:relative;background-position:center center;background-size:cover;background-attachment:scroll}@media (min-width:900px){.hero{background-attachment:fixed}}.hero .contenido-header{position:absolute;width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center}.hero .contenido-header h1{color:#fff;font-weight:700;font-size:4rem;line-height:100%;margin-top:0}@media (min-width:768px){.hero .contenido-header h1{font-size:6.4rem}}.hero .contenido-header p{color:#fff;text-align:justify}.hero-index{min-height:90rem;box-shadow:0 4px 4px 0 rgba(0,0,0,.25)}.hero-index .contenido-header{background-color:rgba(0,16,32,.58);justify-content:flex-start;padding-top:20rem}.hero-index .contenido-header h1{font-size:4rem;font-weight:400;text-align:center}@media (min-width:768px){.hero-index .contenido-header h1{font-size:7rem}}.hero-index .contenido-header p{font-size:1.8rem;font-weight:300;line-height:100%;text-align:center;max-width:78rem;margin-inline:auto}.hero-index .contenido-header .botones{margin-top:7rem;display:flex;justify-content:center;gap:3rem}.hero-index .contenido-header .botones .btn-calculadora-index,.hero-index .contenido-header .botones .btn-contacto-index{display:inline-block;text-align:center;border-radius:5px;font-family:Roboto,sans-serif;font-size:1.6rem;padding-block:.8rem;width:25rem}.hero-index .contenido-header .botones .btn-calculadora-index{color:#000;background-color:#fdfdfd}.hero-index .contenido-header .botones .btn-contacto-index{color:#fdfdfd;background-color:transparent;border:1px solid #fdfdfd}.index-garantia__contenido-item{display:none;align-items:center;justify-items:center;gap:2rem;animation:.5s ease-in-out fadeIn}.index-garantia__contenido-item img{height:10rem;width:auto}.index-garantia__contenido-item h3{font-family:Inter,serif;text-align:center;color:#001f3f;font-size:1.8rem;font-weight:700}@media (min-width:768px){.hero-index .contenido-header p{font-size:2.8rem}.hero-index .contenido-header .botones .btn-calculadora-index,.hero-index .contenido-header .botones .btn-contacto-index{font-size:2rem}.index-garantia__contenido-item{gap:7.4rem}.index-garantia__contenido-item img{height:15rem}.index-garantia__contenido-item h3{font-size:2rem}}.index-garantia__contenido-item h3::after{content:"";display:block;margin-top:1rem;margin-inline:auto;width:8rem;height:4px;background-color:#c7922a}.index-garantia__contenido-item p{font-size:1.2rem;color:#6e6e6e}.index-proceso__item svg{display:block;width:17rem;height:auto}@media (min-width:768px){.index-garantia__contenido-item p{font-size:1.4rem}.index-proceso__item svg{display:none}}@keyframes fadeIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}.layout{display:grid;min-height:100vh;min-width:0;grid-template-rows:auto 1fr auto}.layout__header{min-width:0;max-width:100%;padding-top:8rem}
    </style>
    
    <!-- App.css -->
    <link rel="preload" href="<?php echo get_asset('app.css'); ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="<?php echo get_asset('app.css'); ?>"></noscript>

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
    <script data-domain="dilaesolar.com" src="https://analytics.dilaesolar.com/js/script.js" defer></script>
    <script src="<?php echo get_asset('app.js'); ?>" defer></script>
</body>
</html>