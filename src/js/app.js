document.addEventListener('DOMContentLoaded', function() {

    /** LOGICA PARA EL MENU HAMBURGUESA **/
    const hamburguesa = document.querySelector('#hamburguesa');
    const navegacion = document.querySelector('#navegacion');
    const cerrarNav = document.querySelector('#cerrar-nav');
    const body = document.body;

    if (hamburguesa && navegacion && cerrarNav) {
        hamburguesa.addEventListener('click', () => {
            navegacion.classList.add('visible');
            body.classList.add('no-scroll'); // Bloquea el scroll
        });

        cerrarNav.addEventListener('click', () => {
            navegacion.classList.remove('visible');
            body.classList.remove('no-scroll'); // Desbloquea el scroll
        });

        // Cierra el menú haciendo clic en el overlay
        navegacion.addEventListener('click', (e) => {
            // Si se hace clic en el contenedor principal (el overlay), se cierra
            if (e.target === navegacion) {
                navegacion.classList.remove('visible');
                body.classList.remove('no-scroll');
            }
        });
    }



    /** LOGICA PARA LOS SELECTORES DE LA SECCIÓN DE GARANTIA EN LA PAGINA DE INICIO **/
    const seccionGarantia = document.querySelector('.index-garantia');
    if (seccionGarantia) {
        // Seleccionar todos los elementos necesarios
        const selectores = document.querySelectorAll('.index-garantia__selector');
        const contenidos = document.querySelectorAll('.index-garantia__contenido-item');
        const indicadores = document.querySelectorAll('.index-garantia__indicador');

        let tabActual = 1;
        const totalTabs = selectores.length;
        let intervaloSlider;

        // Función para actualizar la pestaña activa
        function cambiarPestana(targetId) {
            // Actualiza el índice actual
            tabActual = parseInt(targetId);

            // Ocultar todos los contenidos y desactivar todos los selectores/indicadores
            contenidos.forEach(contenido => contenido.classList.remove('activo'));
            selectores.forEach(selector => selector.classList.remove('activo'));
            indicadores.forEach(indicador => indicador.classList.remove('activo'));

            // Mostrar el contenido y activar el selector/indicador correspondiente
            const contenidoActivo = document.querySelector(`.index-garantia__contenido-item[data-tab="${targetId}"]`);
            const selectorActivo = document.querySelector(`.index-garantia__selector[data-target="${targetId}"]`);
            const indicadorActivo = document.querySelector(`.index-garantia__indicador[data-indicador="${targetId}"]`);

            if (contenidoActivo) contenidoActivo.classList.add('activo');
            if (selectorActivo) selectorActivo.classList.add('activo');
            if (indicadorActivo) indicadorActivo.classList.add('activo');
        }

        // Función para avanzar a la siguiente pestaña
        function siguientePestana() {
            tabActual++;
            if (tabActual > totalTabs) {
                tabActual = 1; // Vuelve al inicio
            }
            cambiarPestana(tabActual);
        }

        // Inicia el intervalo
        function iniciarSlider() {
            clearInterval(intervaloSlider);
            intervaloSlider = setInterval(siguientePestana, 7500); // Cambia cada 7.5 segundos
        }

        // Detiene el intervalo
        function detenerSlider() {
            clearInterval(intervaloSlider);
        }

        // Reinicia el intervalo (para cuando el usuario interactúa)
        function reiniciarSlider() {
            detenerSlider();
            iniciarSlider();
        }

        // Añadir el evento de clic a cada selector
        selectores.forEach(selector => {
            selector.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                cambiarPestana(targetId);
                reiniciarSlider(); // Reinicia el temporizador al hacer clic
            });
        });

        // Añadir el evento de clic a cada indicador
        indicadores.forEach(indicador => {
            indicador.addEventListener('click', function() {
                const targetId = this.getAttribute('data-indicador');
                cambiarPestana(targetId);
                reiniciarSlider(); // Reinicia el temporizador al hacer clic
            });
        });

        // Pausar el slider cuando el cursor está sobre la sección
        seccionGarantia.addEventListener('mouseenter', detenerSlider);
        // Reanudar el slider cuando el cursor sale de la sección
        seccionGarantia.addEventListener('mouseleave', iniciarSlider);
        
        // Iniciar el slider por primera vez
        iniciarSlider();
    }


    /** LOGICA PARA EL CARRUSEL DE LA PAGINA DE NOSOTROS EN LA SECCION DE PROCESO */
    const carousel = document.querySelector('.nosotros-proceso__carousel');

    if (carousel) {
        const container = document.querySelector('.nosotros-proceso__items');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        const updateButtons = () => {
            const scrollLeft = container.scrollLeft;
            const scrollWidth = container.scrollWidth;
            const clientWidth = container.clientWidth;

            // Deshabilitar botón "anterior" si estamos al principio
            prevBtn.disabled = scrollLeft < 1;

            // Deshabilitar botón "siguiente" si estamos al final
            nextBtn.disabled = scrollLeft + clientWidth >= scrollWidth - 1;
        };

        // Evento para el botón "siguiente"
        nextBtn.addEventListener('click', () => {
            const itemWidth = container.querySelector('.nosotros-proceso__item').offsetWidth;
            const gap = parseInt(window.getComputedStyle(container).gap, 10);
            container.scrollLeft += itemWidth + gap;
        });

        // Evento para el botón "anterior"
        prevBtn.addEventListener('click', () => {
            const itemWidth = container.querySelector('.nosotros-proceso__item').offsetWidth;
            const gap = parseInt(window.getComputedStyle(container).gap, 10);
            container.scrollLeft -= itemWidth + gap;
        });

        // Actualizar los botones cuando el usuario haga scroll manualmente
        container.addEventListener('scroll', updateButtons);

        // Actualizar los botones al cargar la página
        updateButtons();
    }



    /** CONTADORES ANIMADOS EN LA PAGINA DE INDEX Y NOSOTROS */
    const itemsConContador = document.querySelectorAll(".index-resultados__item, .nosotros-contador__item");

    if (itemsConContador.length > 0) {
        const duracion = 750;

        const iniciarContador = (contador) => {
            const objetivo = +contador.dataset.target;
            let inicio = 0;
            const incremento = objetivo / (duracion / 16.67);

            const actualizarContador = () => {
                inicio += incremento;
                if (inicio < objetivo) {
                    contador.innerText = `${Math.floor(inicio)}`;
                    requestAnimationFrame(actualizarContador);
                } else {
                    contador.innerText = `${objetivo}`;
                }
            };
            actualizarContador();
        };

        const observarItems = new IntersectionObserver((entradas, observador) => {
            entradas.forEach((entrada) => {
                if (entrada.isIntersecting) {
                    // 2. Cuando el CONTENEDOR es visible, buscamos el contador DENTRO de él.
                    const contador = entrada.target.querySelector('.contador');
                    if (contador) {
                        iniciarContador(contador);
                    }
                    // 3. Dejamos de observar el CONTENEDOR una vez animado.
                    observador.unobserve(entrada.target);
                }
            });
        }, {
            threshold: 0.5 // Un umbral de 50% suele ser muy confiable
        });

        // 4. Le decimos al observador que vigile cada CONTENEDOR.
        itemsConContador.forEach(item => {
            observarItems.observe(item);
        });
    }



    /** LOGICA PARA EL GRID INTERACTIVO DEL FORMULARIO DE LA PAGINA DE CALCULADORA  */
    const selectTarifa = document.getElementById('tipo_tarifa');
    const checkbox = document.getElementById('montoPorBimestre');
    const gastoPromedioContainer = document.getElementById('gastoPromedioContainer');
    const bimestresContainer = document.getElementById('bimestresContainer');
    const infoConsumoHistorico = document.getElementById('infoConsumoHistorico');
    const opcionGridContainer = document.querySelector('.formulario__opcion-bimestre-grid');

    if (!selectTarifa || !checkbox || !opcionGridContainer) {
        console.error('Faltan elementos críticos del formulario.');
        return; // Detiene la ejecución si falta algo
    }

    const inputPromedio = document.getElementById('gasto_bimestral');
    const inputsBimestre = Array.from(bimestresContainer.querySelectorAll('input'));
    const form = document.querySelector('.formulario');
    const allCurrencyInputs = [inputPromedio, ...inputsBimestre];

    function formatCurrency(value) {
        if (value === '' || value === null) return '';
        const number = parseFloat(value);
        if (isNaN(number)) return '';
        
        return '$' + number.toLocaleString('es-MX', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function getNumericValue(formattedValue) {
        if (!formattedValue) return '';
        // Permite solo números y un punto decimal
        return formattedValue.replace(/[^0-9.]/g, '');
    }

    function toggleBimestreFields() {
        if (checkbox.checked) {
            opcionGridContainer.classList.add('grid-activo');
            gastoPromedioContainer.classList.add('hidden');
            if (inputPromedio) inputPromedio.required = false;

            bimestresContainer.classList.add('visible');
            infoConsumoHistorico.classList.remove('hidden');
            inputsBimestre.forEach(input => input.required = true);
        } else {
            opcionGridContainer.classList.remove('grid-activo');
            gastoPromedioContainer.classList.remove('hidden');
            if (inputPromedio) inputPromedio.required = true;

            bimestresContainer.classList.remove('visible');
            infoConsumoHistorico.classList.add('hidden');
            inputsBimestre.forEach(input => input.required = false);
        }
    }

    // --- Listeners y Ejecución Inicial ---

    allCurrencyInputs.forEach(input => {
        if (!input) return;

        // Al cargar la página, formatea los valores que PHP haya repoblado.
        if(input.value) {
            input.value = formatCurrency(getNumericValue(input.value));
        }

        // CADA VEZ que el usuario escribe (input), aplicamos el formato.
        input.addEventListener('input', (e) => {
            // <-- INICIA LA SECCIÓN CORREGIDA
            const input = e.target;
            
            // 1. Obtenemos solo los dígitos, ignorando todo lo demás.
            let numericValue = input.value.replace(/[^0-9]/g, '');

            // Si no hay números, dejamos el campo vacío.
            if (numericValue === '') {
                input.value = '';
                return;
            }

            // 2. Convertimos a número y formateamos a moneda local (esto añade las comas).
            const number = parseInt(numericValue, 10);
            const formattedValue = number.toLocaleString('es-MX');

            // 3. Asignamos el valor con el signo de pesos.
            // Al hacer esto, el cursor se va al final por defecto, lo cual es correcto.
            input.value = '$' + formattedValue;
            // <-- TERMINA LA SECCIÓN CORREGIDA. No hay más lógica de cursor.
        });

        // Cuando el usuario SALE del campo (blur), aseguramos que tenga 2 decimales.
        input.addEventListener('blur', () => {
            const numericValue = getNumericValue(input.value);
            input.value = formatCurrency(numericValue);
        });
    });

    // Limpiar los campos antes de enviar el formulario ---
    if (form) {
        form.addEventListener('submit', () => {
            if(checkbox.disabled) {
                checkbox.disabled = false;
            }
            allCurrencyInputs.forEach(input => {
                if (input) {
                    input.value = getNumericValue(input.value);
                }
            });
        });
    }

    // Listeners para los eventos de cambio
    checkbox.addEventListener('change', toggleBimestreFields);

    // Ejecutar al cargar la página para establecer el estado inicial correcto
    toggleBimestreFields();





    /** TUTORIAL PARA ENCONTRAR EL TIPO DE TARIFA DE CFE Y EL CONSUMO HISTORICO */
    function inicializarModal(triggerId, modalId) {
        const trigger = document.getElementById(triggerId);
        const modal = document.getElementById(modalId);

        if (trigger && modal) {
            const cerrarBtn = modal.querySelector('.modal-cerrar');

            // Abrir el modal
            trigger.addEventListener('click', () => {
                modal.classList.add('visible');
            });

            // Cerrar con el botón 'X'
            cerrarBtn.addEventListener('click', () => {
                modal.classList.remove('visible');
            });

            // Cerrar haciendo clic en el fondo
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('visible');
                }
            });
        }
    }

    // Inicializar cada modal
    inicializarModal('info-tarifa-trigger', 'recibo-modal');
    inicializarModal('infoConsumoHistorico', 'consumo-modal');




    /** GRAFICA DE PRODUCCION MENSUAL VS CONSUMO DE LA CALCULADORA */
    const ctx = document.getElementById('myBarChart');

    // Verificamos si el canvas y los datos de la gráfica existen antes de dibujarla
    if (ctx && typeof datosGrafica !== 'undefined') {

        // Encontrar el valor más alto entre todos los datos para ajustar la escala
        const maximoValor = Math.max(...datosGrafica.produccionBimestral, datosGrafica.consumoPromedio);
        const limiteSugerido = maximoValor * 1.25;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Ene - Feb', 'Mar - Abr', 'May - Jun', 'Jul - Ago', 'Sep - Oct', 'Nov - Dic'],
                datasets: [
                    {
                        label: 'Producción Bimestral Estimada',
                        data: datosGrafica.produccionBimestral, 
                        backgroundColor: '#001F3F', 
                        borderColor: '#001F3F',
                        borderWidth: 1,
                        categoryPercentage: 0.8, 
                        barPercentage: 0.8, 
                        yAxisID: 'y'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false 
                    },
                    tooltip: {
                        enabled: true 
                    },
                    annotation: {
                        annotations: {
                            consumoPromedio: { 
                                type: 'line',
                                yMin: datosGrafica.consumoPromedio,
                                yMax: datosGrafica.consumoPromedio,
                                borderColor: '#C7922A',
                                borderWidth: 3,
                                borderDash: [7, 7],
                                label: {
                                    display: false
                                }
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false 
                        },
                        ticks: {
                            color: '#B1BAC4', 
                            font: {
                                size: 12,
                                family: "'Inter', sans-serif", 
                                weight: '500'
                            }
                        },
                    },
                    y: {
                        beginAtZero: true,
                        suggestedMax: limiteSugerido, 
                        border: {
                            display: false 
                        },

                        ticks: {
                            maxTicksLimit: 6,
                            color: '#B1BAC4', 
                            font: {
                                size: 12,
                                family: "'Inter', sans-serif",
                                weight: '500'
                            },
                            callback: function(value, index, values) {
                                return value; 
                            }
                        },
                        grid: {
                            color: '#B1BAC4',
                            lineWidth: 2,
                            drawBorder: false, 
                            drawTicks: false
                        }
                    }
                }
            }
        });
    }



    /** GRAFICA DE RENDIMIENTO SOLAR A 25 AÑOS */
    const ctxLinea = document.getElementById('calculadoraChart');

    if (ctxLinea && typeof datosGraficaLinea !== 'undefined') {

        const { inversionInicial, gananciaNetaFinal, aniosROI, roiTexto } = datosGraficaLinea;

        const COLOR_GANANCIA = '#C7922A'; 
        const COLOR_INVERSION = '#001F3F'; 
        const COLOR_ROI = '#dc3545';      

        const ahorroAnual = (gananciaNetaFinal - inversionInicial) / 25;
        const labels = Array.from({ length: 26 }, (_, i) => i);
        const datosAcumulados = labels.map(i => inversionInicial + (ahorroAnual * i));
        const roiIndex = Math.round(aniosROI)

        const inversionData = [inversionInicial, ...Array(25).fill(null)];
        const roiData = [...Array(roiIndex).fill(null), 0, ...Array(25 - roiIndex).fill(null)];
        const gananciaData = [...Array(25).fill(null), gananciaNetaFinal];

        new Chart(ctxLinea, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Ganancia Acumulada',
                        data: datosAcumulados,
                        order: 1,
                        borderWidth: 0,
                        fill: true,
                        tension: 0,
                        pointRadius: 0,
                        backgroundColor: function(context) {
                            const chart = context.chart;
                            const { ctx, chartArea } = chart;
                            if (!chartArea) { return null; }
                            const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            const yAxis = chart.scales.y;
                            const zeroPixel = yAxis.getPixelForValue(0);
                            if (yAxis.min >= 0) return COLOR_GANANCIA;
                            if (yAxis.max <= 0) return COLOR_INVERSION;
                            const zeroPosition = (zeroPixel - chartArea.top) / chartArea.height;
                            gradient.addColorStop(0, COLOR_GANANCIA);
                            gradient.addColorStop(zeroPosition, COLOR_GANANCIA);
                            gradient.addColorStop(zeroPosition, COLOR_INVERSION);
                            gradient.addColorStop(1, COLOR_INVERSION);
                            return gradient;
                        }
                    },
                    {
                        label: `Inversión Inicial: $${new Intl.NumberFormat('es-MX').format(Math.abs(inversionInicial))} MXN`,
                        data: inversionData,
                        order: 2,
                        pointBackgroundColor: COLOR_INVERSION,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 8, 
                        pointHoverRadius: 10,
                        pointStyle: 'circle'
                    },
                    {
                        label: `Retorno de Inversión: ${roiTexto}`,
                        data: roiData,
                        order: 2,
                        pointBackgroundColor: COLOR_ROI,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 8, 
                        pointHoverRadius: 10,
                        pointStyle: 'circle'
                    },
                    {
                        label: `Ganancia Neta: $${new Intl.NumberFormat('es-MX').format(gananciaNetaFinal)} MXN`,
                        data: gananciaData,
                        order: 2,
                        pointBackgroundColor: COLOR_GANANCIA,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 8, 
                        pointHoverRadius: 10,
                        pointStyle: 'circle'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'center',
                        labels: {
                            boxWidth: 15,
                            padding: 20,
                            usePointStyle: true,
                            filter: (item) => {
                                // Oculta la leyenda para el primer dataset ('Ganancia Acumulada')
                                return item.datasetIndex !== 0;
                            },
                            font: {
                                size: 12,
                                family: "'Inter', sans-serif"
                            }
                        }
                    },
                    tooltip: {
                        intersect: false,
                        mode: 'nearest',
                        callbacks: {
                            title: function(tooltipItems) {
                                const item = tooltipItems[0];
                                if (item.datasetIndex > 0) {
                                    return item.dataset.label;
                                }
                                return `Año ${item.label}`;
                            },
                            label: function(context) {
                                // Muestra el valor solo para la línea de área.
                                if (context.datasetIndex === 0) {
                                    let value = context.parsed.y;
                                    let label = value >= 0 ? 'Ganancia Acumulada: ' : 'Inversión: ';
                                    return label + '$' + new Intl.NumberFormat('es-MX').format(value);
                                }
                                return ''; 
                            }
                        }
                    }
                },
                annotation: {
                    drawTime: 'beforeDatasetsDraw',
                    annotations: {
                        line1: {
                            type: 'line',
                            xMin: aniosROI,
                            xMax: aniosROI,
                            borderColor: COLOR_ROI,
                            borderWidth: 2,
                            borderDash: [6, 6], 
                            label: {
                                content: `ROI: ${roiTexto}`,
                                enabled: true,
                                position: 'start',
                                backgroundColor: '#dc3545',
                                font: {
                                    size: 10,
                                    weight: 'bold'
                                },
                                yAdjust: -15
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) {
                                return '$' + new Intl.NumberFormat('es-MX').format(value);
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Años',
                            font: { size: 12, family: "'Inter', sans-serif" }
                        },
                        ticks: {
                            callback: function(value, index) {
                                return labels[index] % 5 === 0 ? labels[index] : '';
                            },
                            maxRotation: 0,
                            minRotation: 0
                        }
                    }
                }
            }
        });
    }
});