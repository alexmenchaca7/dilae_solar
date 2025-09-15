<?php

namespace Controllers;

use Model\Blog;
use MVC\Router;
use Classes\Email;
use Model\Usuario;
use Model\Suscripcion;
use Classes\Paginacion;

// Función auxiliar para calcular el consumo en kWh y el precio promedio a partir de un gasto bimestral en una tarifa escalonada
function calcularConsumoEscalonado(float $gastoBimestral, array $estructuraTarifaBimestre, float $iva): array {
    $gastoSinIva = $gastoBimestral / $iva;
    $gastoAcumulado = $gastoSinIva;
    $consumoTotalKwh = 0;

    foreach ($estructuraTarifaBimestre as $escalon) {
        $tieneLimite = isset($escalon['limite']);
        
        if ($tieneLimite) {
            $costoMaxEscalon = $escalon['limite'] * $escalon['precio'];

            if ($gastoAcumulado > $costoMaxEscalon) {
                // El consumo llena completamente este escalón
                $consumoTotalKwh += $escalon['limite'];
                $gastoAcumulado -= $costoMaxEscalon;
            } else {
                // El consumo termina en este escalón
                $consumoTotalKwh += $gastoAcumulado / $escalon['precio'];
                $gastoAcumulado = 0;
                break; // Terminamos el cálculo
            }
        } else {
            // Es el último escalón (excedente), no tiene límite
            if ($gastoAcumulado > 0) {
                $consumoTotalKwh += $gastoAcumulado / $escalon['precio'];
            }
            break; // Siempre es el último
        }
    }

    $precioPromedio = ($consumoTotalKwh > 0) ? ($gastoSinIva / $consumoTotalKwh) : 0;

    return [
        'consumoKwh' => $consumoTotalKwh,
        'precioPromedio' => $precioPromedio
    ];
}

class PaginasController {
    public static function index(Router $router) {
        $router->render('paginas/index', [
            'inicio' => true,
            'hero' => 'templates/hero-index',
            'lcp_image' => 'hero-index',
            'critical_css' => 'critical-index.css'
        ]); 
    }

    public static function privacy(Router $router) {
        $titulo = 'Politica de Privacidad';

        $router->render('paginas/privacy', [
            'titulo' => $titulo,
        ]);
    }

    public static function terminos(Router $router) {
        $titulo = 'Términos y Condiciones del Servicio';

        $router->render('paginas/terminos-servicio', [
            'titulo' => $titulo,
        ]);
    }

    public static function nosotros(Router $router) {
        $titulo = 'Sobre Nosotros';
        $meta_description = 'Expertos en energía solar en Guadalajara. En Dilae Solar combinamos ingeniería y las mejores marcas para ofrecerte proyectos solares precisos y rentables. ¡Conócenos!';

        $router->render('paginas/nosotros', [
            'titulo' => $titulo,
            'meta_description' => $meta_description,
            'hero' => 'templates/hero-nosotros',
            'lcp_image' => 'hero-nosotros',
            'critical_css' => 'critical-nosotros.css'
        ]); 
    }

    public static function soluciones(Router $router) {
        $titulo = 'Soluciones';
        $meta_description = 'Soluciones de energía solar residencial en Guadalajara. Ofrecemos paneles solares, sistemas de almacenamiento, calentadores solares y más. ¡Empieza tu proyecto hoy!';

        $router->render('paginas/soluciones', [
            'titulo' => $titulo,
            'meta_description' => $meta_description,
            'hero' => 'templates/hero-soluciones',
            'lcp_image' => 'hero-soluciones',
            'critical_css' => 'critical-soluciones.css'
        ]); 
    }

    public static function calculadora(Router $router) {
        $titulo = 'Calculadora';
        $meta_description = 'Calcula tu ahorro con paneles solares. Descubre cuántos paneles necesitas y el retorno de tu inversión en segundos. ¡Usa nuestra calculadora solar sin compromiso!';
        $datos = [];
        $resultados = [];
        $mostrarResultados = false;
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mostrarResultados = true;
    
            // --- 1. RECOLECTAR DATOS ---
            $datos['tipo_tarifa'] = filter_input(INPUT_POST, 'tipo_tarifa', FILTER_SANITIZE_SPECIAL_CHARS);
            $datos['montoPorBimestre'] = isset($_POST['montoPorBimestre']);
            $datos['gasto_bimestral'] = filter_input(INPUT_POST, 'gasto_bimestral', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $gastos_por_bimestre_input = $_POST['gasto_bimestre'] ?? [];
            $datos['gastos_bimestrales'] = $gastos_por_bimestre_input;
    
            // --- 2. PROCESAR GASTOS ---
            $gastoPromedio = 0;
            $gastos_bimestrales_validos = [];
            
            if ($datos['montoPorBimestre']) {
                foreach ($gastos_por_bimestre_input as $gasto) {
                    if (!empty($gasto) && is_numeric($gasto)) {
                        $gastos_bimestrales_validos[] = (float)$gasto;
                    }
                }
                if (count($gastos_bimestrales_validos) > 0) {
                    $gastoPromedio = array_sum($gastos_bimestrales_validos) / count($gastos_bimestrales_validos);
                    $datos['gasto_bimestral'] = number_format($gastoPromedio, 2, '.', '');
                }
            } else if (!empty($datos['gasto_bimestral'])) {
                $gastoPromedio = (float)$datos['gasto_bimestral'];
                $datos['gastos_bimestrales'] = [];
            }
    
            // --- 3. DEFINIR TARIFAS Y CONSTANTES ---
            define('IVA', 1.16);
            $tarifaSeleccionada = $datos['tipo_tarifa'] ?? '';
            $esTarifaEscalonada = false;
    
            // ESTRUCTURA DE TARIFAS PLANAS
            $tarifasFlat = [
                'Tarifa PDBT' => ['promedio' => 4.645, 'bimestral' => [4.499, 4.638, 4.674, 4.759, 4.687, 4.613]],
                'Tarifa GDBT' => ['promedio' => 1.611, 'bimestral' => [1.595, 1.597, 1.613, 1.653, 1.620, 1.585]],
                'Tarifa DAC'  => ['promedio' => 6.481, 'bimestral' => [6.712, 6.741, 6.615, 6.487, 6.096, 6.234]],
            ];
            
            // ESTRUCTURA DE TARIFAS ESCALONADAS
            $tarifasEscalonadas = [
                'Tarifa 1' => [
                    [['limite' => 150, 'precio' => 1.065], ['limite' => 130, 'precio' => 1.292], ['precio' => 3.784]], // Ene-Feb
                    [['limite' => 150, 'precio' => 1.073], ['limite' => 130, 'precio' => 1.302], ['precio' => 3.812]], // Mar-Abr
                    [['limite' => 150, 'precio' => 1.081], ['limite' => 130, 'precio' => 1.312], ['precio' => 3.840]], // May-Jun
                    [['limite' => 150, 'precio' => 1.089], ['limite' => 130, 'precio' => 1.322], ['precio' => 3.868]], // Jul-Ago
                    [['limite' => 150, 'precio' => 1.097], ['limite' => 130, 'precio' => 1.332], ['precio' => 3.896]], // Sep-Oct
                    [['limite' => 150, 'precio' => 1.105], ['limite' => 130, 'precio' => 1.342], ['precio' => 3.924]], // Nov-Dic
                ],
                'Tarifa 1A' => [
                    [['limite' => 150, 'precio' => 1.065], ['limite' => 150, 'precio' => 1.292], ['precio' => 3.784]], // Ene-Feb
                    [['limite' => 150, 'precio' => 1.073], ['limite' => 150, 'precio' => 1.302], ['precio' => 3.812]], // Mar-Abr
                    [['limite' => 200, 'precio' => 0.963], ['limite' => 100, 'precio' => 1.117], ['precio' => 3.840]], // May-Jun
                    [['limite' => 200, 'precio' => 0.971], ['limite' => 100, 'precio' => 1.125], ['precio' => 3.868]], // Jul-Ago
                    [['limite' => 200, 'precio' => 0.979], ['limite' => 100, 'precio' => 1.133], ['precio' => 3.896]], // Sep-Oct
                    [['limite' => 150, 'precio' => 1.105], ['limite' => 150, 'precio' => 1.342], ['precio' => 3.924]], // Nov-Dic
                ],
                'Tarifa 1B' => [
                    [['limite' => 150, 'precio' => 1.065], ['limite' => 200, 'precio' => 1.292], ['precio' => 3.784]], // Ene-Feb
                    [['limite' => 150, 'precio' => 1.073], ['limite' => 200, 'precio' => 1.302], ['precio' => 3.812]], // Mar-Abr
                    [['limite' => 250, 'precio' => 0.963], ['limite' => 200, 'precio' => 1.117], ['precio' => 3.840]], // May-Jun
                    [['limite' => 250, 'precio' => 0.971], ['limite' => 200, 'precio' => 1.125], ['precio' => 3.868]], // Jul-Ago
                    [['limite' => 250, 'precio' => 0.979], ['limite' => 200, 'precio' => 1.133], ['precio' => 3.896]], // Sep-Oct
                    [['limite' => 150, 'precio' => 1.105], ['limite' => 200, 'precio' => 1.342], ['precio' => 3.924]], // Nov-Dic
                ],
                'Tarifa 1C' => [ 
                    [['limite' => 150, 'precio' => 1.065], ['limite' => 200, 'precio' => 1.292], ['precio' => 3.784]], // Ene-Feb 
                    [['limite' => 150, 'precio' => 1.073], ['limite' => 200, 'precio' => 1.302], ['precio' => 3.812]], // Mar-Abr 
                    [['limite' => 300, 'precio' => 0.963], ['limite' => 300, 'precio' => 1.117], ['limite' => 300, 'precio' => 1.437], ['precio' => 3.840]], // May-Jun 
                    [['limite' => 300, 'precio' => 0.971], ['limite' => 300, 'precio' => 1.125], ['limite' => 300, 'precio' => 1.447], ['precio' => 3.868]], // Jul-Ago 
                    [['limite' => 300, 'precio' => 0.979], ['limite' => 300, 'precio' => 1.133], ['limite' => 300, 'precio' => 1.457], ['precio' => 3.896]], // Sep-Oct 
                    [['limite' => 150, 'precio' => 1.105], ['limite' => 200, 'precio' => 1.342], ['precio' => 3.924]], // Nov-Dic
                ],
                'Tarifa 1D' => [
                    [['limite' => 150, 'precio' => 1.065], ['limite' => 250, 'precio' => 1.292], ['precio' => 3.784]], // Ene-Feb
                    [['limite' => 150, 'precio' => 1.073], ['limite' => 250, 'precio' => 1.302], ['precio' => 3.812]], // Mar-Abr
                    [['limite' => 350, 'precio' => 0.963], ['limite' => 450, 'precio' => 1.117], ['limite' => 400, 'precio' => 1.437], ['precio' => 3.840]], // May-Jun
                    [['limite' => 350, 'precio' => 0.971], ['limite' => 450, 'precio' => 1.125], ['limite' => 400, 'precio' => 1.447], ['precio' => 3.868]], // Jul-Ago
                    [['limite' => 350, 'precio' => 0.979], ['limite' => 450, 'precio' => 1.133], ['limite' => 400, 'precio' => 1.457], ['precio' => 3.896]], // Sep-Oct
                    [['limite' => 150, 'precio' => 1.105], ['limite' => 250, 'precio' => 1.342], ['precio' => 3.924]], // Nov-Dic
                ],
                'Tarifa 1E' => [
                    [['limite' => 150, 'precio' => 1.065], ['limite' => 250, 'precio' => 1.292], ['precio' => 3.784]], // Ene-Feb
                    [['limite' => 150, 'precio' => 1.073], ['limite' => 250, 'precio' => 1.302], ['precio' => 3.812]], // Mar-Abr
                    [['limite' => 600, 'precio' => 0.804], ['limite' => 900, 'precio' => 0.998], ['limite' => 300, 'precio' => 1.295], ['precio' => 3.840]], // May-Jun
                    [['limite' => 600, 'precio' => 0.810], ['limite' => 900, 'precio' => 1.155], ['limite' => 300, 'precio' => 1.305], ['precio' => 3.868]], // Jul-Ago
                    [['limite' => 600, 'precio' => 0.816], ['limite' => 900, 'precio' => 1.014], ['limite' => 300, 'precio' => 1.315], ['precio' => 3.896]], // Sep-Oct
                    [['limite' => 150, 'precio' => 1.105], ['limite' => 250, 'precio' => 1.342], ['precio' => 3.924]], // Nov-Dic
                ],
                'Tarifa 1F' => [
                    [['limite' => 150, 'precio' => 1.065], ['limite' => 250, 'precio' => 1.292], ['precio' => 3.784]], // Ene-Feb
                    [['limite' => 150, 'precio' => 1.073], ['limite' => 250, 'precio' => 1.302], ['precio' => 3.812]], // Mar-Abr
                    [['limite' => 600, 'precio' => 0.804], ['limite' => 1800, 'precio' => 0.998], ['limite' => 2600, 'precio' => 2.427], ['precio' => 3.840]], // May-Jun
                    [['limite' => 600, 'precio' => 0.810], ['limite' => 1800, 'precio' => 1.006], ['limite' => 2600, 'precio' => 2.445], ['precio' => 3.868]], // Jul-Ago
                    [['limite' => 600, 'precio' => 0.816], ['limite' => 1800, 'precio' => 1.014], ['limite' => 2600, 'precio' => 2.463], ['precio' => 3.896]], // Sep-Oct
                    [['limite' => 150, 'precio' => 1.105], ['limite' => 250, 'precio' => 1.342], ['precio' => 3.924]], // Nov-Dic
                ],
            ];
    
            $costoKwhPromedio = 0; // Solo para tarifas flat promedio
    
            if (array_key_exists($tarifaSeleccionada, $tarifasFlat)) {
                if (!$datos['montoPorBimestre']) {
                    $costoKwhPromedio = $tarifasFlat[$tarifaSeleccionada]['promedio'];
                }
            } elseif (array_key_exists($tarifaSeleccionada, $tarifasEscalonadas)) {
                $esTarifaEscalonada = true;
            }
    
            define('POTENCIA_PANEL_W', 620);
            define('FACTOR_RENDIMIENTO', 0.8);
            define('VIDA_UTIL_PANELES', 25);
            define('FACTOR_CO2', 0.6538);
            define('CO2_POR_ARBOL_KG', 22);
    
            // --- 4. REALIZAR CÁLCULOS ---
            $consumoPromedioKwh = 0;
            $consumosBimestrales = []; // Guardará ['consumoKwh' => X, 'precioPromedio' => Y]
    
            if ($gastoPromedio > 0) {
                // --- CÁLCULO DE CONSUMO EN KWH ---
                if ($datos['montoPorBimestre'] && count($gastos_bimestrales_validos) > 0) {
                    if ($esTarifaEscalonada) {
                        $tarifasBimestrales = $tarifasEscalonadas[$tarifaSeleccionada];
                        for ($b = 0; $b < count($gastos_bimestrales_validos); $b++) {
                            $consumosBimestrales[] = calcularConsumoEscalonado($gastos_bimestrales_validos[$b], $tarifasBimestrales[$b], IVA);
                        }
                    } else { // Tarifas Flat
                        $tarifasBimestrales = $tarifasFlat[$tarifaSeleccionada]['bimestral'];
                        for ($b = 0; $b < count($gastos_bimestrales_validos); $b++) {
                            $consumoKwh = ($gastos_bimestrales_validos[$b] / IVA) / $tarifasBimestrales[$b];
                            $consumosBimestrales[] = ['consumoKwh' => $consumoKwh, 'precioPromedio' => $tarifasBimestrales[$b]];
                        }
                    }
                    if(!empty($consumosBimestrales)) {
                        $consumoPromedioKwh = array_sum(array_column($consumosBimestrales, 'consumoKwh')) / count($consumosBimestrales);
                    }
                } else if ($esTarifaEscalonada) { 
                    $tarifasBimestrales = $tarifasEscalonadas[$tarifaSeleccionada];
                    foreach($tarifasBimestrales as $estructuraBimestral) {
                        // Guardamos el resultado completo (consumo y precio) para usarlo después en el cálculo de ahorro
                        $consumosBimestrales[] = calcularConsumoEscalonado($gastoPromedio, $estructuraBimestral, IVA);
                    }
                    if(!empty($consumosBimestrales)) {
                        $consumoPromedioKwh = array_sum(array_column($consumosBimestrales, 'consumoKwh')) / count($consumosBimestrales);
                    }
                } else if (!$esTarifaEscalonada && $costoKwhPromedio > 0) {
                    $consumoPromedioKwh = ($gastoPromedio / IVA) / $costoKwhPromedio;
                }
    
                if ($consumoPromedioKwh > 0) {
                    // --- CÁLCULOS DE SISTEMA FOTOVOLTAICO ---
                    $produccionKwhPorPanelBimestral = (POTENCIA_PANEL_W * 5.8 * FACTOR_RENDIMIENTO * 60) / 1000;
                    $cantidadPaneles = ceil($consumoPromedioKwh / $produccionKwhPorPanelBimestral);

                    // LÓGICA PARA FACTOR DE COSTO VARIABLE
                    $factorCostoPorWatt = 0;
                    if ($cantidadPaneles >= 1 && $cantidadPaneles <= 3) {
                        $factorCostoPorWatt = 16.10;
                    } elseif ($cantidadPaneles >= 4 && $cantidadPaneles <= 5) {
                        $factorCostoPorWatt = 15.90;
                    } elseif ($cantidadPaneles >= 6 && $cantidadPaneles <= 7) {
                        $factorCostoPorWatt = 15.65;
                    } elseif ($cantidadPaneles >= 8 && $cantidadPaneles <= 9) {
                        $factorCostoPorWatt = 15.50;
                    } elseif ($cantidadPaneles >= 10 && $cantidadPaneles <= 11) {
                        $factorCostoPorWatt = 15.25;
                    } elseif ($cantidadPaneles >= 12 && $cantidadPaneles <= 19) {
                        $factorCostoPorWatt = 15.15;
                    } elseif ($cantidadPaneles >= 20 && $cantidadPaneles <= 29) {
                        $factorCostoPorWatt = 14.80;
                    } elseif ($cantidadPaneles >= 30 && $cantidadPaneles <= 39) {
                        $factorCostoPorWatt = 14.50;
                    } elseif ($cantidadPaneles >= 40 && $cantidadPaneles <= 49) {
                        $factorCostoPorWatt = 14.20;
                    } elseif ($cantidadPaneles >= 50) {
                        $factorCostoPorWatt = 14.00;
                    }

                    $potenciaNominal = POTENCIA_PANEL_W;
                    $potenciaTotalInstalada = ($potenciaNominal * $cantidadPaneles) / 1000;
                    
                    $diasPorMes = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
                    $horasSolarPicoPorMes = [4.81, 5.77, 6.86, 7.24, 7.15, 6.2, 5.66, 5.63, 5.21, 5.36, 5.17, 4.6];
                    $generacionMensualKwhArray = [];
                    $generacionAnualKwh = 0;
                    for ($i = 0; $i < 12; $i++) {
                        $potenciaTotalEnWatts = POTENCIA_PANEL_W * $cantidadPaneles;
                        $generacionMensual = ($potenciaTotalEnWatts * FACTOR_RENDIMIENTO * $horasSolarPicoPorMes[$i] * $diasPorMes[$i]) / 1000;
                        $generacionMensualKwhArray[] = $generacionMensual;
                        $generacionAnualKwh += $generacionMensual;
                    }
                    $generacionAnualMwh = $generacionAnualKwh / 1000;
    
                    // --- CÁLCULO DE AHORRO ANUAL ---
                    $ahorroAnualEstimado = 0;
                    if (!empty($consumosBimestrales) && ($datos['montoPorBimestre'] || $esTarifaEscalonada)) { // <-- CORRECCIÓN 2: Lógica unificada para ahorro bimestral
                        // Este bloque ahora funciona para el desglose real Y para el desglose estimado de tarifas escalonadas
                        for ($b = 0; $b < count($consumosBimestrales); $b++) {
                            $consumoEspecificoKwh = $consumosBimestrales[$b]['consumoKwh'];
                            $precioPromedioBimestral = $consumosBimestrales[$b]['precioPromedio'];
                            $generacionBimestralKwh = $generacionMensualKwhArray[$b * 2] + $generacionMensualKwhArray[($b * 2) + 1];
                            $ahorroBimestral = min($consumoEspecificoKwh, $generacionBimestralKwh) * $precioPromedioBimestral;
                            $ahorroAnualEstimado += $ahorroBimestral;
                        }
                    } else { // Cálculo por promedio solo para tarifas flat
                        for ($b = 0; $b < 6; $b++) {
                            $generacionBimestralKwh = $generacionMensualKwhArray[$b * 2] + $generacionMensualKwhArray[($b * 2) + 1];
                            $ahorroBimestral = min($consumoPromedioKwh, $generacionBimestralKwh) * $costoKwhPromedio;
                            $ahorroAnualEstimado += $ahorroBimestral;
                        }
                    }
    
                    // --- CÁLCULOS FINANCIEROS Y AMBIENTALES ---
                    $inversionTotal = 0;
                    $roiTexto = "N/A";
                    $roiDecimal = 0;
                    if ($ahorroAnualEstimado > 0) {
                        $costoBruto = (POTENCIA_PANEL_W * $cantidadPaneles) * $factorCostoPorWatt;
                        $inversionTotal = ceil($costoBruto / 1000) * 1000;
                        $roiDecimal = $inversionTotal / $ahorroAnualEstimado;
                        $años = floor($roiDecimal);
                        $meses = ceil(($roiDecimal - $años) * 12);
                        if ($meses >= 12) {
                            $años++;
                            $meses = 0;
                        }
                        $textoAños = ($años == 1) ? 'Año' : 'Años';
                        $textoMeses = ($meses == 1) ? 'Mes' : 'Meses';
                        if ($años > 0 && $meses > 0) {
                            $roiTexto = "$años $textoAños $meses $textoMeses";
                        } elseif ($años > 0) {
                            $roiTexto = "$años $textoAños";
                        } else {
                            $roiTexto = "$meses $textoMeses";
                        }
                    }
    
                    $ahorroTotalVidaUtil = $ahorroAnualEstimado * VIDA_UTIL_PANELES;
                    $gananciaNeta = $ahorroTotalVidaUtil - $inversionTotal;
                    $impactoAmbientalTonCO2 = $generacionAnualMwh * FACTOR_CO2;
                    $equivalenciaArboles = ($impactoAmbientalTonCO2 * 1000) / CO2_POR_ARBOL_KG;
    
                    // --- PREPARAR DATOS PARA GRÁFICAS ---
                    $generacionBimestralKwhArray = [];
                    for ($b = 0; $b < 6; $b++) {
                        $generacionBimestralKwhArray[] = round($generacionMensualKwhArray[$b * 2] + $generacionMensualKwhArray[($b * 2) + 1]);
                    }
                    $datosGrafica = [
                        'produccionBimestral' => $generacionBimestralKwhArray,
                        'consumoPromedio' => round($consumoPromedioKwh)
                    ];
                    $datosGraficaLinea = [
                        'inversionInicial' => -$inversionTotal,
                        'gananciaNetaFinal' => $gananciaNeta,
                        'aniosROI' => $roiDecimal ?? 0,
                        'roiTexto' => $roiTexto
                    ];
    
                    // --- GUARDAR RESULTADOS FINALES ---
                    $resultados = [
                        'datosGrafica' => $datosGrafica,
                        'datosGraficaLinea' => $datosGraficaLinea,
                        'impactoAmbiental' => '~' . number_format($impactoAmbientalTonCO2, 2) . ' Ton',
                        'equivalenciaArboles' => '~' . floor($equivalenciaArboles),
                        'gananciaNeta' => '$' . number_format(ceil($gananciaNeta)) . ' MXN',
                        'retornoInversion' => $roiTexto,
                        'ahorroAnual' => '$' . number_format(ceil($ahorroAnualEstimado)) . ' MXN',
                        'cantidadPaneles' => number_format($cantidadPaneles, 0) . ' Paneles',
                        'potenciaNominal' => number_format($potenciaNominal, 0) . ' Watts',
                        'potenciaTotalInstalada' => number_format($potenciaTotalInstalada, 2) . ' kWp',
                        'generacionAnual' => number_format($generacionAnualMwh, 2) . ' MWh'
                    ];
                }
            }
        }
    
        $router->render('paginas/calculadora', [
            'titulo' => $titulo,
            'meta_description' => $meta_description,
            'datos' => $datos,
            'resultados' => $resultados,
            'mostrarResultados' => $mostrarResultados,
            'critical_css' => 'critical-calculadora.css'
        ]);
    }

    public static function blogs(Router $router) {
        $titulo = 'Nuestro Blog';
        $meta_description = 'Blog Tu Mundo Solar por Dilae Solar. Encuentra artículos, guías y consejos sobre paneles solares, ahorro de energía y tecnologías limpias para tu hogar.';

        $condiciones = ["estado = 'publicado'"]; 

        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
        if($pagina_actual < 1) {
            header('Location: /blogs?page=1'); 
            exit();
        }

        $registros_por_pagina = 9;
        
        $total_blogs = Blog::totalCondiciones($condiciones);
        
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total_blogs);
        
        if ($paginacion->total_paginas() < $pagina_actual && $pagina_actual > 1) {
            header('Location: /blogs?page=' . $paginacion->total_paginas());
            exit();
        }

        $blogs = Blog::obtenerBlogsPublicadosPaginados($registros_por_pagina, $paginacion->offset());

        $router->render('paginas/blogs', [
            'titulo' => $titulo,
            'meta_description' => $meta_description,
            'hero' => 'templates/hero-blogs',
            'lcp_image' => 'hero-blogs',
            'critical_css' => 'critical-blogs.css',
            'blogs' => $blogs,
            'paginacion' => $paginacion->paginacion()
        ]);
    }

    public static function blog(Router $router, $slug) {
        if(!$slug) {
            header('Location: /blogs');
            exit;
        }

        // Usamos el slug directamente en lugar de $_GET
        $blog = Blog::findBySlugPublicado($slug);

        if(!$blog) {
            header('Location: /blogs'); 
            exit;
        }
        
        $autor = Usuario::find($blog->autor_id);
        $blog->autor_nombre = $autor ? ($autor->nombre . ' ' . $autor->apellido) : 'Anónimo';
        
        $blogs_relacionados = Blog::obtenerBlogsRelacionados($blog->id, 3);
        
        $router->render('paginas/blog-entrada', [
            'titulo' => $blog->titulo,
            'blog' => $blog,
            'blogs_relacionados' => $blogs_relacionados 
        ]);
    }

    public static function contacto(Router $router) {
        $titulo = 'Contacto';
        $meta_description = 'Contacta a Dilae Solar en Guadalajara. ¿Listo para empezar a ahorrar con energía solar? Llámanos o visita nuestras oficinas en Zapopan. ¡Cotiza tu proyecto hoy!';

        $alertas = [];
        $datos = [ // Para repoblar el formulario en caso de error o para limpiarlo
            'nombre' => '',
            'email' => '',
            'telefono' => '',
            'horario' => '',
            'codigo_postal' => '',
            'mensaje' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitizar y asignar datos POST
            $datos['nombre'] = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_SPECIAL_CHARS);
            $datos['email'] = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $datos['telefono'] = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_SPECIAL_CHARS);
            $datos['horario'] = filter_input(INPUT_POST, 'horario', FILTER_SANITIZE_SPECIAL_CHARS);
            $datos['codigo_postal'] = filter_input(INPUT_POST, 'codigo_postal', FILTER_SANITIZE_SPECIAL_CHARS);
            $datos['mensaje'] = filter_input(INPUT_POST, 'mensaje', FILTER_SANITIZE_SPECIAL_CHARS);
            
            // --- INICIO DE VALIDACIONES ---

            // Validar nombre
            if (empty($datos['nombre'])) {
                $alertas['error'][] = 'El nombre es obligatorio.';
            }

            // Validar email
            if (empty($datos['email'])) {
                $alertas['error'][] = 'El correo electrónico es obligatorio.';
            } elseif (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                $alertas['error'][] = 'El formato del correo electrónico no es válido.';
            }

            // Validar teléfono (10 dígitos)
            if (empty($datos['telefono'])) {
                $alertas['error'][] = 'El teléfono es obligatorio.';
            } elseif (!preg_match('/^[0-9]{10}$/', $datos['telefono'])) {
                $alertas['error'][] = 'El formato del teléfono no es válido (debe tener 10 dígitos).';
            }

            // Validar horario
            if (empty($datos['horario'])) {
                $alertas['error'][] = 'El horario es obligatorio.';
            }

            // Validar Código Postal
            if (empty($datos['codigo_postal'])) {
                $alertas['error'][] = 'El código postal es obligatorio.';
            } elseif (!preg_match('/^[0-9]{5}$/', $datos['codigo_postal']) || !self::validarCodigoPostalMx($datos['codigo_postal'])) {
                $alertas['error'][] = 'El código postal no es válido o no existe.';
            }

            // Validar mensaje
            if (empty($datos['mensaje'])) {
                $alertas['error'][] = 'El mensaje es obligatorio.';
            }

            // --- FIN DE VALIDACIONES ---

            if (empty($alertas['error'])) {
                // Todos los datos son válidos, proceder a enviar el email
                $email = new Email(); 
                $enviado = $email->enviarFormularioContacto($datos);

                if ($enviado) {
                    $alertas['exito'][] = '¡Mensaje enviado correctamente! Nos pondremos en contacto contigo a la brevedad.';
                    // Limpiar los datos del formulario después de un envío exitoso
                    $datos = [
                        'nombre' => '', 'email' => '', 'telefono' => '',
                        'horario' => '', 'codigo_postal' => '', 'mensaje' => ''
                    ];
                } else {
                    $alertas['error'][] = 'No se pudo enviar el mensaje. Por favor, inténtalo de nuevo más tarde o contáctanos por otro medio.';
                }
            }
        }

        $router->render('paginas/contacto', [
            'titulo' => $titulo,
            'meta_description' => $meta_description,
            'hero' => 'templates/hero-contacto',
            'lcp_image' => 'hero-contacto',
            'critical_css' => 'critical-contacto.css',
            'alertas' => $alertas,
            'datos' => $datos
        ]); 
    }

    // Valida un código postal mexicano usando la API de COPOMEX.
    private static function validarCodigoPostalMx(string $cp): bool {
        // Token de prueba de la API. Para producción, es recomendable registrarse y obtener uno propio.
        $token = 'pruebas';
        $url = "https://api.copomex.com/query/info_cp/{$cp}?token={$token}";

        // Usar cURL para hacer la petición a la API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // La API devuelve un array vacío y un código 200 si no encuentra el CP.
        // Si el CP existe, devuelve un array con datos.
        if ($http_code == 200) {
            $data = json_decode($response, true);
            // Si la respuesta no está vacía, el CP es válido.
            return !empty($data);
        }
        
        // Si hay algún error en la petición, asumimos que no es válido.
        return false;
    }

    public static function subscribe(Router $router) {
        // Establecer el tipo de contenido a JSON para la respuesta
        header('Content-Type: application/json');

        $email_address = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $response = [];

        if (!$email_address) {
            $response = ['status' => 'error', 'message' => 'Por favor, introduce un correo electrónico válido.'];
        } else {
            $suscripcion = Suscripcion::where('email', $email_address);

            if ($suscripcion && $suscripcion->confirmado == "1") {
                $response = ['status' => 'error', 'message' => 'Este correo electrónico ya está suscrito.'];
            } else {
                if (!$suscripcion) {
                    $suscripcion = new Suscripcion(['email' => $email_address]);
                }
                
                $suscripcion->crearToken();
                $suscripcion->confirmado = 0; // Asegurarse de que esté como no confirmado
                $resultado = $suscripcion->guardar();

                if ($resultado) {
                    $email = new Email($suscripcion->email, 'Nuevo Suscriptor', $suscripcion->token);
                    $email->enviarConfirmacionSuscripcion(); // Nuevo método de email
                    $response = ['status' => 'exito', 'message' => '¡Excelente! Revisa tu correo para confirmar tu suscripción.'];
                } else {
                    $response = ['status' => 'error', 'message' => 'No se pudo procesar tu solicitud en este momento.'];
                }
            }
        }

        // Devolver la respuesta en formato JSON y terminar la ejecución
        echo json_encode($response);
        exit;
    }


    public static function confirmarSuscripcion(Router $router) {
        $token = s($_GET['token'] ?? '');
        if (!$token) {
            header('Location: /');
            exit;
        }

        $suscripcion = Suscripcion::where('token', $token);

        if ($suscripcion) {
            $suscripcion->confirmado = 1;
            $suscripcion->token = null;
            $suscripcion->guardar();
            // Puedes crear una vista simple que diga "Suscripción confirmada"
            $router->render('paginas/suscripcion-confirmada', [
                'titulo' => 'Suscripción Confirmada'
            ]);
        } else {
            // O una vista de token inválido
            header('Location: /');
            exit;
        }
    }
}