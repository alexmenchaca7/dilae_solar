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
    const seccionGarantia = document.querySelector('.garantia');
    if (seccionGarantia) {
        // Seleccionar todos los elementos necesarios
        const selectores = document.querySelectorAll('.garantia__selector');
        const contenidos = document.querySelectorAll('.garantia__contenido-item');
        const indicadores = document.querySelectorAll('.garantia__indicador');

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
            const contenidoActivo = document.querySelector(`.garantia__contenido-item[data-tab="${targetId}"]`);
            const selectorActivo = document.querySelector(`.garantia__selector[data-target="${targetId}"]`);
            const indicadorActivo = document.querySelector(`.garantia__indicador[data-indicador="${targetId}"]`);

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



    /** FRAME CHATBOT ODOO **/
    const iframe = document.getElementById('dilae-chat-iframe');
  
    // al inicio solo la burbuja
    iframe.style.width = "1000px";
    iframe.style.height = "1000px";

    // si quieres que al hacer click se expanda 
    iframe.addEventListener("load", () => {
        iframe.contentWindow.document.addEventListener("click", () => {
        iframe.style.width = "2000px";
        iframe.style.height = "2000px";
        });
    });
});