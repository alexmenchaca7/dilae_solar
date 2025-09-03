<fieldset class="formulario__fieldset">
    <legend class="formulario__legend">Información del Blog</legend>

    <div class="formulario__campo">
        <label for="titulo" class="formulario__label">Título:</label>
        <input 
            type="text"
            class="formulario__input"
            id="titulo"
            name="titulo" <?php /* Cambiado de blog[titulo] a solo titulo, como en tu form de productos */ ?>
            placeholder="Título de la Entrada del Blog"
            value="<?php echo s($blog->titulo ?? ''); ?>"
        >
    </div>

    <div class="formulario__campo">
        <label for="slug" class="formulario__label">Slug (URL Amigable):</label>
        <input 
            type="text"
            class="formulario__input"
            id="slug"
            name="slug"
            placeholder="ej: mi-nuevo-post (se generará si se deja vacío)"
            value="<?php echo s($blog->slug ?? ''); ?>"
        >
        <p class="descripcion-campo" style="font-size: 1.2rem; color: #555; margin-top: 0.5rem;">Si dejas el slug vacío al crear, se generará uno a partir del título. Puedes editarlo manualmente.</p>
    </div>

    <div class="formulario__campo">
        <label for="contenido" class="formulario__label">Contenido:</label>
        <textarea 
            class="formulario__input" <?php /* Para el editor WYSIWYG, puede que necesites otra clase o el ID */ ?>
            id="contenido_blog" <?php /* ID Único para el editor */ ?>
            name="contenido" 
            rows="15"
            placeholder="Escribe aquí el contenido de tu blog..."
        ><?php echo s($blog->contenido ?? ''); ?></textarea>
        <!-- Aquí se inicializará el editor WYSIWYG -->
    </div>

    <div class="formulario__campo">
        <label for="estado" class="formulario__label">Estado:</label>
        <select class="formulario__input" id="estado" name="estado">
            <option value="borrador" <?php echo (($blog->estado ?? 'borrador') === 'borrador') ? 'selected' : ''; ?>>Borrador</option>
            <option value="publicado" <?php echo (($blog->estado ?? '') === 'publicado') ? 'selected' : ''; ?>>Publicado</option>
            <option value="archivado" <?php echo (($blog->estado ?? '') === 'archivado') ? 'selected' : ''; ?>>Archivado</option>
        </select>
    </div>

    <?php /* Opcional: Si quieres permitir seleccionar el autor desde el admin (necesitarás cargar $usuarios en el controlador)
    if(isset($usuarios) && !empty($usuarios)): ?>
    <div class="formulario__campo">
        <label for="autor_id" class="formulario__label">Autor:</label>
        <select class="formulario__input" id="autor_id" name="autor_id">
            <option value="">-- Seleccionar Autor (Opcional) --</option>
            <?php foreach($usuarios as $usuario_autor): ?>
                <option value="<?php echo $usuario_autor->id; ?>" <?php echo ($blog->autor_id == $usuario_autor->id) ? 'selected' : ''; ?>>
                    <?php echo s($usuario_autor->nombre . " " . $usuario_autor->apellido); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; */?>

</fieldset>

<fieldset class="formulario__fieldset">
    <legend class="formulario__legend">SEO</legend>
    <p style="font-size: 1.4rem; color: #555; margin-top: -1rem; margin-bottom: 2rem;">Optimiza cómo se muestra esta entrada de blog en los buscadores. Si dejas los campos vacíos, se usará el título del blog y un extracto del contenido.</p>

    <div class="formulario__campo">
        <label for="meta_title" class="formulario__label">Meta Título</label>
        <input 
            type="text"
            class="formulario__input"
            id="meta_title"
            name="meta_title"
            placeholder="Título para buscadores (recomendado 55-60 caracteres)"
            value="<?php echo htmlspecialchars($producto->meta_title ?? ''); ?>"
        >
        <div class="char-counter">
            <span id="meta_title_counter">0/60</span>
            <div class="char-indicator" id="meta_title_indicator">
                <div class="indicator-bar-fill"></div>
            </div>
        </div>
    </div>

    <div class="formulario__campo">
        <label for="meta_description" class="formulario__label">Meta Descripción</label>
        <textarea 
            class="formulario__input"
            name="meta_description" 
            id="meta_description" 
            rows="3"
            placeholder="Descripción para buscadores (recomendado 150-160 caracteres)"
        ><?php echo htmlspecialchars($producto->meta_description ?? ''); ?></textarea>
        <div class="char-counter">
            <span id="meta_description_counter">0/155</span>
            <div class="char-indicator" id="meta_description_indicator">
                <div class="indicator-bar-fill"></div>
            </div>
        </div>
    </div>
</fieldset>

<fieldset class="formulario__fieldset">
    <legend class="formulario__legend">Imagen Destacada</legend>

    <!-- Contenedor general del campo, similar a como envuelves cada imagen en productos -->
    <div class="formulario__campo contenedor-imagen" id="slot-imagen-destacada-blog"> 
        <label for="imagen_input_blog" class="formulario__label">
            <?php echo (isset($blog->imagen) && $blog->imagen) ? 'Cambiar Imagen:' : 'Seleccionar Imagen:'; ?>
        </label>

        <!-- Este div actúa como el .contenedor-imagen-preview de tus productos,
             pero sin el botón de eliminar individual, ya que es una sola imagen. -->
        <div class="contenedor-imagen-preview"> 
            <!-- Esta es el área clickeable y donde se muestra la imagen/placeholder.
                 Le damos la clase .imagen-preview que ya tienes estilada. -->
            <div class="imagen-preview" id="preview-area-clickable-blog" 
                 data-hover-text="<?php echo (isset($blog->imagen) && $blog->imagen) ? 'Cambiar Imagen' : 'Seleccionar Imagen'; ?>">
                
                <img src="<?php echo (isset($blog->imagen) && $blog->imagen) ? '/img/blogs/' . s($blog->imagen) . '.png' : '#'; ?>" 
                     alt="Imagen destacada" 
                     class="imagen-cargada <?php echo (isset($blog->imagen) && $blog->imagen) ? '' : 'hidden'; ?>"
                     id="img-element-blog">

                <span class="imagen-placeholder <?php echo (isset($blog->imagen) && $blog->imagen) ? 'hidden' : ''; ?>" id="placeholder-blog">
                    <i class="fas fa-plus"></i> <!-- Asumiendo que usas FontAwesome para el "+" -->
                </span>
                
                <!-- El input file real. El JS lo activará.
                     Lo mantenemos con style="display: none;" como en tu script de productos.
                     La clase "imagen-input" es la que usas en productos para el JS. -->
                <input 
                    type="file" 
                    class="imagen-input" 
                    id="imagen_input_blog" 
                    name="imagen" 
                    accept="image/jpeg, image/png, image/webp"
                    style="display: none;" 
                >
            </div>
        </div>
    </div>

    <?php if (isset($blog->imagen) && $blog->imagen): ?>
        <div class="formulario__campo formulario__campo--checkbox" style="margin-top: 1rem;"> <!-- Ajuste de margen -->
            <input type="checkbox" class="formulario__checkbox-input" id="eliminar_imagen_actual" name="eliminar_imagen_actual" value="1">
            <label for="eliminar_imagen_actual" class="formulario__label--checkbox">Eliminar imagen actual</label>
        </div>
    <?php endif; ?>
</fieldset>


<!-- Script para el editor WYSIWYG (TinyMCE como ejemplo) -->
<script src="https://cdn.tiny.cloud/1/awcmz8f1zy7oj3v3vcwg7nrgcioryqwa9lulqsz7wfqf47af/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.querySelector('textarea#contenido_blog')) {
            tinymce.init({
                selector: 'textarea#contenido_blog',
                plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons accordion', // Sin 'template'
                menubar: 'file edit view insert format tools table help',
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat | code fullscreen preview',
                height: 500,
                image_advtab: true,
                image_title: true,
                automatic_uploads: true,
                relative_urls: false, 
                remove_script_host: true, 
                // document_base_url: '/', // Podría ser necesario si lo anterior no funciona por sí solo
                images_upload_url: '/admin/blogs/upload-editor-image', 
                images_upload_base_path: '/img/blogs/contenido', 
                images_reuse_filename: false, // (Opcional, pero bueno para evitar colisiones) Genera nombres únicos
                file_picker_types: 'image media',
                file_picker_callback: function (cb, value, meta) {
                    var input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    input.setAttribute('accept', meta.filetype === 'image' ? 'image/*' : (meta.filetype === 'media' ? 'video/*,audio/*' : '*/*'));

                    input.onchange = function () {
                        var file = this.files[0];

                        // Crear FormData para enviar el archivo
                        var formData = new FormData();
                        formData.append('file', file, file.name); // El backend esperará un campo 'file'

                        // Enviar al mismo endpoint que images_upload_url
                        fetch('/admin/blogs/upload-editor-image', { // Asegúrate que la URL es correcta
                            method: 'POST',
                            body: formData
                            // No necesitas 'Content-Type' aquí, el navegador lo setea para FormData
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.location) {
                                cb(result.location, { title: file.name, alt: file.name });
                            } else {
                                // Manejar error de subida desde el servidor
                                tinymce.activeEditor.notificationManager.open({
                                    text: 'Error subiendo imagen: ' + (result.error || 'Error desconocido'),
                                    type: 'error'
                                });
                                console.error("Error subiendo imagen desde file_picker:", result);
                            }
                        })
                        .catch(error => {
                            tinymce.activeEditor.notificationManager.open({
                                text: 'Error de red o servidor al subir imagen.',
                                type: 'error'
                            });
                            console.error("Fetch error en file_picker:", error);
                        });
                    };
                    input.click();
                },
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
            });
        }

        const tituloInput = document.getElementById('titulo');
        const slugInput = document.getElementById('slug');
        if (tituloInput && slugInput) {
            tituloInput.addEventListener('keyup', function() {
                let slugWasAutogenerated = slugInput.dataset.autogenerated === 'true';
                if (slugInput.value === '' || slugWasAutogenerated) {
                    let slugValue = this.value.toLowerCase().trim().replace(/\s+/g, '-').replace(/[^\w-]+/g, '').replace(/--+/g, '-');
                    slugInput.value = slugValue;
                    slugInput.dataset.autogenerated = 'true';
                }
            });
            slugInput.addEventListener('input', function() {
                slugInput.dataset.autogenerated = 'false';
            });
        }

        // --- Lógica para la IMAGEN DESTACADA del BLOG (estilo productos) ---
        const areaPreviewBlog = document.getElementById('preview-area-clickable-blog'); // El div .imagen-preview
        const inputFileBlog = document.getElementById('imagen_input_blog');       // El <input type="file"> dentro de .imagen-preview
        const imgElementBlog = document.getElementById('img-element-blog');           // El <img>
        const placeholderBlog = document.getElementById('placeholder-blog');        // El <span> +
        const labelPrincipalBlog = document.querySelector('label[for="imagen_input_blog"]'); // El label principal del campo

        if (areaPreviewBlog && inputFileBlog && imgElementBlog && placeholderBlog) {

            function actualizarAparienciaVisual(hayImagen) {
                const textoLabel = hayImagen ? 'Cambiar Imagen:' : 'Seleccionar Imagen:'; // Texto para el label principal
                const textoHover = hayImagen ? 'Cambiar Imagen' : 'Seleccionar Imagen'; // Texto para el data-attribute del hover

                if (labelPrincipalBlog) {
                    labelPrincipalBlog.textContent = textoLabel;
                }
                areaPreviewBlog.setAttribute('data-hover-text', textoHover);

                if (hayImagen) {
                    imgElementBlog.classList.remove('hidden');
                    placeholderBlog.classList.add('hidden');
                } else {
                    imgElementBlog.src = '#'; // Para evitar mostrar una imagen rota si no hay src
                    imgElementBlog.classList.add('hidden');
                    placeholderBlog.classList.remove('hidden');
                }
            }

            // Estado inicial
            const tieneImagenInicial = imgElementBlog.getAttribute('src') && imgElementBlog.getAttribute('src') !== '#';
            actualizarAparienciaVisual(tieneImagenInicial);
            if (tieneImagenInicial) {
                imgElementBlog.dataset.imagenOriginal = imgElementBlog.getAttribute('src'); // Guardar src original si existe
            } else {
                imgElementBlog.dataset.imagenOriginal = '';
            }


            areaPreviewBlog.addEventListener('click', function() {
                inputFileBlog.click(); 
            });

            inputFileBlog.addEventListener('change', function(event) {
                if (this.files && this.files[0]) {
                    const archivoSeleccionado = this.files[0];
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        imgElementBlog.src = e.target.result; 
                        actualizarAparienciaVisual(true);   
                    }
                    reader.readAsDataURL(archivoSeleccionado);
                } else {
                    // Usuario canceló la selección.
                    // Si no había una imagen original del servidor O la imagen mostrada es la de placeholder '#',
                    // entonces volvemos al estado placeholder.
                    const imagenOriginalSrc = imgElementBlog.dataset.imagenOriginal;
                    if (!imagenOriginalSrc || imagenOriginalSrc === '') {
                        actualizarAparienciaVisual(false);
                    } else {
                        // Si había una imagen original del servidor, la restauramos en el preview
                        // y actualizamos los textos para reflejar que se puede "Cambiar Imagen".
                        imgElementBlog.src = imagenOriginalSrc;
                        actualizarAparienciaVisual(true);
                    }
                    // Importante: el input file (this) ahora está vacío, por lo que si el usuario
                    // guarda sin seleccionar otra imagen, no se subirá nada nuevo.
                    // Si había una imagen en el servidor, esta se mantendrá a menos que se marque "eliminar".
                }
            });
        } else {
            console.error("Error en Blog JS: No se encontraron todos los elementos para el preview de la imagen destacada. Verifica IDs: preview-area-clickable-blog, imagen_input_blog, img-element-blog, placeholder-blog.");
        }
    });
</script>