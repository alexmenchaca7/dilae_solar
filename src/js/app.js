document.addEventListener('DOMContentLoaded', function() {
    // Seleccionar todos los elementos necesarios
    const selectores = document.querySelectorAll('.garantia__selector');
    const contenidos = document.querySelectorAll('.garantia__contenido-item');
    const indicadores = document.querySelectorAll('.garantia__indicador');

    // Función para actualizar la pestaña activa
    function cambiarPestana(targetId) {
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

    // Añadir el evento de clic a cada selector
    selectores.forEach(selector => {
        selector.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            cambiarPestana(targetId);
        });
    });
});