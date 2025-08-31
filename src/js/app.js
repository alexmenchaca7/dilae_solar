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
    const checkbox = document.getElementById('montoPorBimestre');
    const gastoPromedioContainer = document.getElementById('gastoPromedioContainer');
    const bimestresContainer = document.getElementById('bimestresContainer');
    const infoConsumoHistorico = document.getElementById('infoConsumoHistorico');

    // Obtenemos el contenedor del checkbox
    const opcionGridContainer = document.querySelector('.formulario__opcion-bimestre-grid');

    if (!checkbox || !opcionGridContainer) {
        console.error('Faltan elementos del checkbox.');
        return; // Detiene la ejecución si falta algo
    }

    const inputPromedio = document.getElementById('gasto_bimestral');
    const inputsBimestre = bimestresContainer.querySelectorAll('input');

    function toggleBimestreFields() {
        if (checkbox.checked) {
            // Escenario 1: Checkbox ACTIVADO
                
            opcionGridContainer.classList.add('grid-activo');
            
            // Ocultar el campo de promedio
            gastoPromedioContainer.classList.add('hidden');
            if (inputPromedio) inputPromedio.required = false;

            // Mostrar los campos bimestrales
            bimestresContainer.classList.add('visible');
            infoConsumoHistorico.classList.remove('hidden');
            inputsBimestre.forEach(input => input.required = true);

        } else {
            // Escenario 2: Checkbox DESACTIVADO

            opcionGridContainer.classList.remove('grid-activo');

            // Mostrar el campo de promedio
            gastoPromedioContainer.classList.remove('hidden');
            if (inputPromedio) inputPromedio.required = true;

            // Ocultar los campos bimestrales
            bimestresContainer.classList.remove('visible');
            infoConsumoHistorico.classList.add('hidden');
            inputsBimestre.forEach(input => input.required = false);
        }
    }

    // Añadir el listener al checkbox
    checkbox.addEventListener('change', toggleBimestreFields);

    // Ejecutar la función una vez al cargar la página para establecer el estado inicial correcto
    toggleBimestreFields();




    /** GRAFICA DE PRODUCCION MENSUAL VS CONSUMO DE LA CALCULADORA */
    const ctx = document.getElementById('myBarChart');

    if (ctx) { // Asegúrate de que el canvas existe
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Ene - Feb', 'Mar - Abr', 'May - Jun', 'Jul - Ago', 'Sep - Oct', 'Nov - Dic'],
                datasets: [
                    {
                        label: 'Producción Bimestral Estimada',
                        data: [2800, 3700, 3500, 3120, 2800, 2160], // Datos de producción de las barras
                        backgroundColor: '#001F3F', // v.$primario
                        borderColor: '#001F3F',
                        borderWidth: 1,
                        categoryPercentage: 0.8, // Ancho de la barra
                        barPercentage: 0.8, // Espacio entre barras
                        yAxisID: 'y'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Permite que la altura del contenedor controle el canvas
                plugins: {
                    legend: {
                        display: false // Deshabilita la leyenda por defecto de Chart.js, usaremos nuestra leyenda personalizada
                    },
                    tooltip: {
                        enabled: true // Habilita los tooltips al pasar el ratón
                    },
                    annotation: {
                        annotations: {
                            consumoPromedio: { // Un nombre único para tu anotación
                                type: 'line',
                                yMin: 2500, // El valor en el eje Y donde se dibujará la línea
                                yMax: 2500, // Debe ser el mismo que yMin para una línea horizontal
                                borderColor: '#C7922A',
                                borderWidth: 3,
                                borderDash: [7, 7],
                                // La siguiente línea hace que la etiqueta de la anotación no se muestre
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
                            display: false // Oculta las líneas de la cuadrícula vertical
                        },
                        ticks: {
                            color: '#B1BAC4', // Color de las etiquetas del eje X
                            font: {
                                size: 12,
                                family: "'Inter', sans-serif", // Asegúrate de que esta fuente esté cargada
                                weight: '500'
                            }
                        },
                    },
                    y: {
                        beginAtZero: true,
                        max: 4000, // Máximo del eje Y
                        border: {
                            display: false // <-- ¡ESTA ES LA LÍNEA MÁGICA! Desactiva el borde del eje.
                        },

                        ticks: {
                            stepSize: 2000, // Pasos de 2000
                            color: '#B1BAC4', // Color de las etiquetas del eje Y
                            font: {
                                size: 12,
                                family: "'Inter', sans-serif",
                                weight: '500'
                            },
                            callback: function(value, index, values) {
                                return value; // Muestra el valor tal cual
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
});