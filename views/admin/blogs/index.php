<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<?php
    if (isset($_GET['resultado'])) {
        $resultado = filter_var($_GET['resultado'], FILTER_VALIDATE_INT);
        $mensaje = '';
        $claseAlerta = 'exito'; // Todas son de éxito por ahora

        switch ($resultado) {
            case 1:
                $mensaje = "Blog Creado Correctamente";
                break;
            case 2:
                $mensaje = "Blog Actualizado Correctamente";
                break;
            case 3:
                $mensaje = "Blog Eliminado Correctamente";
                break;
        }

        if ($mensaje) {
            echo "<p class='alerta alerta__$claseAlerta'>" . htmlspecialchars($mensaje) . "</p>";
        }
    }
?>


<div class="dashboard__contenedor-boton">
    <form class="dashboard__busqueda" method="GET" action="/admin/blogs" id="search-form-ajax">
        <div class="campo-busqueda">
            <input 
                type="text" 
                name="busqueda" 
                class="input-busqueda" 
                placeholder="Buscar por título, contenido, autor..."
                value="<?php echo htmlspecialchars($_GET['busqueda'] ?? ''); ?>"
            >
            <button type="submit" class="boton-busqueda">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </form>

    <a class="dashboard__boton" href="/admin/blogs/crear">
        <i class="fa-solid fa-circle-plus"></i>
        Añadir Blog
    </a>
</div>


<div id="data-container">
    <div class="dashboard__contenedor" id="table-container">
        <?php include __DIR__ . '/_tabla.php'; ?>
    </div>
    <div id="pagination-container">
        <?php echo $paginacion; ?>
    </div>
</div>


<!-- Modal de Confirmación (ya lo tienes, solo asegúrate que el JS sea alcanzable) -->
<div id="deleteModal" class="modal">
    <div class="modal__content">
        <h3>Advertencia</h3>
        <p id="modalMessage">¡Al eliminar este blog se borrará permanentemente! ¿Estás seguro de que deseas continuar?</p>
        <div class="modal__acciones">
            <button id="cancelDelete" class="modal__cancel">Cancelar</button>
            <button id="confirmDelete" class="modal__confirm">Eliminar</button>
        </div>
    </div>
</div>

<script>
    let currentId = null;
    let currentForm = null;

    function openDeleteModal(event, id, type) {
        event.preventDefault();
        currentId = id;
        currentForm = event.target.closest('form');
        document.getElementById('deleteModal').style.display = 'block';
        document.body.style.overflow = 'hidden';

        const message = '¡Al eliminar este blog se borrará permanentemente! ¿Estás seguro de que deseas continuar?';
        document.getElementById('modalMessage').textContent = message;
    }

    document.getElementById('cancelDelete').addEventListener('click', () => {
        document.getElementById('deleteModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    });

    document.getElementById('deleteModal').addEventListener('click', (event) => {
        if (event.target === document.getElementById('deleteModal')) {
            document.getElementById('deleteModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });

    document.getElementById('confirmDelete').addEventListener('click', () => {
        if (currentForm) {
            currentForm.submit();
        }
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('search-form-ajax');
    if (!searchForm) return; // Si no hay formulario en la página, no hacer nada

    const searchInput = searchForm.querySelector('.input-busqueda');
    const tableContainer = document.getElementById('table-container');
    const paginationContainer = document.getElementById('pagination-container');
    let debounceTimer;

    // Función principal para obtener y renderizar los datos
    async function fetchData(url) {
        try {
            // Muestra un indicador de carga
            tableContainer.innerHTML = '<p class="t-align-center">Cargando resultados...</p>';
            paginationContainer.innerHTML = '';

            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) throw new Error('Error en la respuesta del servidor');

            const data = await response.json();

            // Actualiza el contenido con la respuesta del servidor
            tableContainer.innerHTML = data.tabla_html;
            paginationContainer.innerHTML = data.paginacion_html;
            
            // Actualiza la URL en el navegador sin recargar la página
            history.pushState({ path: url }, '', url);
        } catch (error) {
            console.error('Error al realizar la búsqueda:', error);
            tableContainer.innerHTML = '<p class="t-align-center alerta alerta__error">Error al cargar los datos. Intente de nuevo.</p>';
        }
    }

    // Evento para búsqueda en tiempo real mientras se escribe
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const baseUrl = searchForm.action;
            const url = `${baseUrl}?busqueda=${encodeURIComponent(searchInput.value)}`;
            fetchData(url);
        }, 400); // Un pequeño retraso para no saturar el servidor
    });

    // Previene el envío tradicional del formulario
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
    });

    // Maneja los clics en los enlaces de la paginación
    document.addEventListener('click', function(e) {
        // Se usa 'closest' para asegurar que el evento funcione incluso si se hace clic en un <i> o <span> dentro del <a>
        const paginationLink = e.target.closest('#pagination-container a');
        if (paginationLink) {
            e.preventDefault();
            fetchData(paginationLink.href);
        }
    });

    // Maneja los botones de "atrás" y "adelante" del navegador
    window.addEventListener('popstate', function() {
        fetchData(location.href);
    });
});
</script>