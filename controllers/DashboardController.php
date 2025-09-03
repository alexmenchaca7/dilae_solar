<?php

namespace Controllers;

use MVC\Router;
use Google\Client;
use Google\Service\AnalyticsData;

class DashboardController {
    public static function index(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
            exit;
        }

        $analyticsData = self::getAnalyticsData();
        
        $router->render('admin/dashboard/index', [
            'titulo' => 'Panel de Administración',
            'usuariosEnLinea' => $analyticsData['usuariosEnLinea'],
            'visitasHoy' => $analyticsData['visitasHoy'],
            'visitasPorDia' => json_encode($analyticsData['visitasPorDia']),
            'visitasPorMes' => json_encode($analyticsData['visitasPorMes'] ?? ['labels' => [], 'data' => []]),
            'paginasMasVisitadas' => $analyticsData['paginasMasVisitadas'],
            'paginasMasVisitadasAnual' => $analyticsData['paginasMasVisitadasAnual'] ?? [],
            'trafficSources' => $analyticsData['trafficSources'] ?? ['sources' => [], 'totalSessions' => 0],
            'deviceData' => json_encode($analyticsData['deviceData'] ?? ['labels' => [], 'data' => []])
        ], 'admin-layout');
    }

    private static function getAnalyticsData(): array {
        $cacheFile = __DIR__ . '/../cache/analytics_data.json';
        $cacheTime = 3600; // 1 hora en segundos

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
            $cachedData = json_decode(file_get_contents($cacheFile), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $cachedData;
            }
        }

        try {
            $propertyId = '492445778'; 
            $keyFilePath = __DIR__ . '/../config/google-credentials-produccion.json';

            $client = new Client();
            $client->setApplicationName("Dilae Analytics Dashboard");
            $client->setAuthConfig($keyFilePath);
            $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
            $analytics = new AnalyticsData($client);

            $data = [];

            // 1. Usuarios en tiempo real
            $realtimeResponse = $analytics->properties->runRealtimeReport('properties/' . $propertyId, new \Google\Service\AnalyticsData\RunRealtimeReportRequest([
                'metrics' => [['name' => 'activeUsers']]
            ]));
            $data['usuariosEnLinea'] = $realtimeResponse->getRows() ? $realtimeResponse->getRows()[0]->getMetricValues()[0]->getValue() : 0;

            // Definimos los rangos de fechas que usaremos
            $range7dias = new \Google\Service\AnalyticsData\DateRange(['startDate' => '7daysAgo', 'endDate' => 'today']);
            $range30dias = new \Google\Service\AnalyticsData\DateRange(['startDate' => '30daysAgo', 'endDate' => 'today']);
            $range12meses = new \Google\Service\AnalyticsData\DateRange(['startDate' => '365daysAgo', 'endDate' => 'today']);
            $todayRange = new \Google\Service\AnalyticsData\DateRange(['startDate' => 'today', 'endDate' => 'today']);

            // 2. Gráfica de visitas por día (últimos 7 días)
            $requestVisitas = new \Google\Service\AnalyticsData\RunReportRequest(['property' => 'properties/' . $propertyId, 'dateRanges' => [$range7dias], 'dimensions' => [['name' => 'date']], 'metrics' => [['name' => 'sessions']], 'orderBys' => [['dimension' => ['dimensionName' => 'date']]]]);
            $responseVisitas = $analytics->properties->runReport('properties/' . $propertyId, $requestVisitas);
            $visitasPorDia = ['labels' => [], 'data' => []];
            if ($responseVisitas->getRows()) {
                foreach ($responseVisitas->getRows() as $row) {
                    $visitasPorDia['labels'][] = date("d/m", strtotime($row->getDimensionValues()[0]->getValue()));
                    $visitasPorDia['data'][] = (int)$row->getMetricValues()[0]->getValue();
                }
            }
            $data['visitasPorDia'] = $visitasPorDia;

            // 3. Gráfica de visitas por mes (últimos 12 meses)
            $requestMeses = new \Google\Service\AnalyticsData\RunReportRequest(['property' => 'properties/' . $propertyId, 'dateRanges' => [$range12meses], 'dimensions' => [['name' => 'yearMonth']], 'metrics' => [['name' => 'sessions']], 'orderBys' => [['dimension' => ['dimensionName' => 'yearMonth']]]]);
            $responseMeses = $analytics->properties->runReport('properties/' . $propertyId, $requestMeses);
            $visitasPorMes = ['labels' => [], 'data' => []];
            if ($responseMeses->getRows()) {
                $monthNames = ['01' => 'Ene', '02' => 'Feb', '03' => 'Mar', '04' => 'Abr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic'];
                foreach ($responseMeses->getRows() as $row) {
                    $yearMonth = $row->getDimensionValues()[0]->getValue();
                    $visitasPorMes['labels'][] = ($monthNames[substr($yearMonth, 4, 2)] ?? '') . " " . substr($yearMonth, 0, 4);
                    $visitasPorMes['data'][] = (int)$row->getMetricValues()[0]->getValue();
                }
            }
            $data['visitasPorMes'] = $visitasPorMes;

            // 4. Páginas más visitadas (Último Mes)
            $requestPaginasMes = new \Google\Service\AnalyticsData\RunReportRequest(['property' => 'properties/' . $propertyId, 'dateRanges' => [$range30dias], 'dimensions' => [['name' => 'pagePath']], 'metrics' => [['name' => 'screenPageViews']], 'limit' => 5, 'orderBys' => [['metric' => ['metricName' => 'screenPageViews'], 'desc' => true]]]);
            $responsePaginasMes = $analytics->properties->runReport('properties/' . $propertyId, $requestPaginasMes);
            $paginasMes = [];
            if ($responsePaginasMes->getRows()) {
                foreach ($responsePaginasMes->getRows() as $row) {
                    $paginasMes[] = ['url' => $row->getDimensionValues()[0]->getValue(), 'visitas' => (int)$row->getMetricValues()[0]->getValue()];
                }
            }
            $data['paginasMasVisitadas'] = $paginasMes;

            // 5. Páginas más visitadas (Último Año)
            $requestPaginasAnual = new \Google\Service\AnalyticsData\RunReportRequest(['property' => 'properties/' . $propertyId, 'dateRanges' => [$range12meses], 'dimensions' => [['name' => 'pagePath']], 'metrics' => [['name' => 'screenPageViews']], 'limit' => 10, 'orderBys' => [['metric' => ['metricName' => 'screenPageViews'], 'desc' => true]]]);
            $responsePaginasAnual = $analytics->properties->runReport('properties/' . $propertyId, $requestPaginasAnual);
            $paginasAnual = [];
            if ($responsePaginasAnual->getRows()) {
                foreach ($responsePaginasAnual->getRows() as $row) {
                    $paginasAnual[] = ['url' => $row->getDimensionValues()[0]->getValue(), 'visitas' => (int)$row->getMetricValues()[0]->getValue()];
                }
            }
            $data['paginasMasVisitadasAnual'] = $paginasAnual;

            // 6. Visitas de hoy
            $requestHoy = new \Google\Service\AnalyticsData\RunReportRequest(['property' => 'properties/' . $propertyId, 'dateRanges' => [$todayRange], 'metrics' => [['name' => 'sessions']]]);
            $responseHoy = $analytics->properties->runReport('properties/' . $propertyId, $requestHoy);
            $data['visitasHoy'] = $responseHoy->getRows() ? (int)$responseHoy->getRows()[0]->getMetricValues()[0]->getValue() : 0;
            
            // 7. Fuentes de Tráfico (Último Mes)
            $requestTrafico = new \Google\Service\AnalyticsData\RunReportRequest(['property' => 'properties/' . $propertyId, 'dateRanges' => [$range30dias], 'dimensions' => [['name' => 'sessionDefaultChannelGroup'], ['name' => 'landingPagePlusQueryString']], 'metrics' => [['name' => 'sessions']], 'limit' => 50, 'orderBys' => [['metric' => ['metricName' => 'sessions'], 'desc' => true]]]);
            $responseTrafico = $analytics->properties->runReport('properties/' . $propertyId, $requestTrafico);
            $trafficData = ['sources' => [], 'totalSessions' => 0];
            if ($responseTrafico->getRows()) {
                $totalSessions = 0; $rawRows = [];
                foreach ($responseTrafico->getRows() as $row) { $visits = (int)$row->getMetricValues()[0]->getValue(); $rawRows[] = ['source' => $row->getDimensionValues()[0]->getValue(), 'page' => $row->getDimensionValues()[1]->getValue(), 'visits' => $visits]; $totalSessions += $visits; }
                $trafficData['totalSessions'] = $totalSessions;
                $groupedSources = [];
                foreach ($rawRows as $row) { $sourceName = $row['source']; if (!isset($groupedSources[$sourceName])) { $groupedSources[$sourceName] = ['totalVisits' => 0, 'pages' => []]; } $groupedSources[$sourceName]['totalVisits'] += $row['visits']; $groupedSources[$sourceName]['pages'][] = ['url' => $row['page'], 'visits' => $row['visits']]; }
                uasort($groupedSources, function($a, $b) { return $b['totalVisits'] <=> $a['totalVisits']; });
                $trafficData['sources'] = $groupedSources;
            }
            $data['trafficSources'] = $trafficData;

            // 8. Visitas por Dispositivo (Último Mes)
            $requestDevice = new \Google\Service\AnalyticsData\RunReportRequest(['property' => 'properties/' . $propertyId, 'dateRanges' => [$range30dias], 'dimensions' => [['name' => 'deviceCategory']], 'metrics' => [['name' => 'sessions']]]);
            $responseDevice = $analytics->properties->runReport('properties/' . $propertyId, $requestDevice);
            $deviceData = ['labels' => [], 'data' => []];
            if ($responseDevice->getRows()) {
                foreach ($responseDevice->getRows() as $row) { $deviceData['labels'][] = ucfirst($row->getDimensionValues()[0]->getValue()); $deviceData['data'][] = (int)$row->getMetricValues()[0]->getValue(); }
            }
            $data['deviceData'] = $deviceData;

            // Guardar en caché y retornar
            file_put_contents($cacheFile, json_encode($data, JSON_PRETTY_PRINT));
            return $data;

        } catch (\Exception $e) {
            error_log('Error en la API de Google Analytics: ' . $e->getMessage());
            return [
                'usuariosEnLinea' => 0, 'visitasHoy' => 0, 'visitasPorDia' => [], 
                'visitasPorMes' => [], 'paginasMasVisitadas' => [], 'paginasMasVisitadasAnual' => [],
                'trafficSources' => [], 'deviceData' => []
            ];
        }
    }
}