<?php

namespace Controllers;

use MVC\Router;
use Model\Suscripcion;
use Classes\Paginacion;

class SuscripcionesController {

    public static function index(Router $router) {
        if (!is_auth()) {
            header('Location: /login');
            exit;
        }

        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
        if ($pagina_actual < 1) {
            header('Location: /admin/suscripciones?page=1');
            exit;
        }

        $registros_por_pagina = 10;
        
        // Contar solo las suscripciones confirmadas para la paginación
        $total = Suscripcion::totalCondiciones(['confirmado' => 1]);
        
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);

        if ($paginacion->total_paginas() < $pagina_actual && $pagina_actual > 1 && $total > 0) {
            header('Location: /admin/suscripciones?page=1');
            exit;
        }

        // Obtener solo las suscripciones confirmadas, ordenadas por fecha
        $suscripciones = Suscripcion::metodoSQL([
            'condiciones' => ['confirmado' => 1],
            'orden' => 'fecha_suscripcion DESC',
            'limite' => $registros_por_pagina,
            'offset' => $paginacion->offset()
        ]);

        $router->render('admin/suscripciones/index', [
            'titulo' => 'Suscripciones al Boletín',
            'suscripciones' => $suscripciones,
            'paginacion' => $paginacion->paginacion()
        ], 'admin-layout');
    }

    public static function exportar() {
        if (!is_auth()) {
            header('Location: /login');
            exit;
        }

        // Obtener solo los suscriptores confirmados para el export
        $suscripciones = Suscripcion::whereArray(['confirmado' => 1], 'fecha_suscripcion ASC');

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=suscripciones-confirmadas-dilae-solar-' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');

        fputcsv($output, ['Email', 'Fecha de Suscripcion', 'Confirmado']);

        foreach ($suscripciones as $suscripcion) {
            fputcsv($output, [
                $suscripcion->email,
                $suscripcion->fecha_suscripcion,
                $suscripcion->confirmado ? 'Si' : 'No'
            ]);
        }

        fclose($output);
        exit;
    }
}