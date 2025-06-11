<?php

namespace Controllers;

use MVC\Router;
use Classes\Email;
use Classes\Paginacion;

class PaginasController {
    public static function index(Router $router) {

        // --- DATOS ESTRUCTURADOS (SCHEMA) PARA LA PÁGINA DE INICIO ---
        $schema = [
            "@context" => "https://schema.org",
            "@type" => "Organization", // O "LocalBusiness" si tienes una ubicación física
            "name" => "Dilae Solar",
            "url" => $_ENV['HOST'],
            "logo" => $_ENV['HOST'] . "/build/img/logo.png",
            "contactPoint" => [
                [
                    "@type" => "ContactPoint",
                    "telephone" => "+52-81-3876-0590", // Tu teléfono principal
                    "contactType" => "customer service"
                ],
                [
                    "@type" => "ContactPoint",
                    "email" => "contacto@dilaesolar.com",
                    "contactType" => "customer service"
                ]
            ],
            "sameAs" => [ // Opcional: añade tus redes sociales
                // "https://www.facebook.com/tu-pagina",
                // "https://www.linkedin.com/company/tu-empresa"
            ]
        ];

        $datos_vista = [
            'titulo' => 'Sitio en Construcción',
            'meta_description' => 'Página oficial de Dilae Solar. Estamos trabajando para ofrecerte la mejor experiencia. ¡Vuelve pronto!',
            'schema' => '<script type="application/ld+json">' . json_encode($schema) . '</script>',
            'inicio' => true // Para que el título en layout.php sea el correcto
        ];
        
        $router->render('paginas/construccion', [
            $datos_vista
        ], 'layout-vacio'); // Usamos un layout vacío sin header/footer
    }
}