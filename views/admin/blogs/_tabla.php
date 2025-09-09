<?php if(!empty($blogs)): ?>
    <table class="table">
        <thead class="table__thead">
            <tr>
                <th scope="col" class="table__th">Imagen</th>
                <th scope="col" class="table__th">Título</th>
                <th scope="col" class="table__th">Autor</th>
                <th scope="col" class="table__th">Estado</th>
                <th scope="col" class="table__th">Fecha Creación</th>
                <th scope="col" class="table__th"></th> <!-- Para acciones -->
            </tr>
        </thead>

        <tbody class="table__tbody">
            <?php foreach($blogs as $blog): ?>
                <tr class="table__tr">
                    <td class="table__td table__td--imagen">
                        <?php if($blog->imagen): ?>
                            <img loading="lazy" src="/img/blogs/<?php echo htmlspecialchars($blog->imagen); ?>" alt="Imagen Blog: <?php echo htmlspecialchars($blog->titulo); ?>" width="100" height="auto">
                        <?php else: ?>
                            <span class="table__td--placeholder">Sin Imagen</span>
                        <?php endif; ?>
                    </td>
                    <td class="table__td"><?php echo htmlspecialchars($blog->titulo); ?></td>
                    <td class="table__td"><?php echo htmlspecialchars($blog->nombre_autor ?? 'N/A'); ?></td>
                    <td class="table__td"><?php echo htmlspecialchars(ucfirst($blog->estado)); ?></td>
                    <td class="table__td"><?php echo date('d/m/Y', strtotime($blog->fecha_creacion)); ?></td>
                    <td class="table__td--acciones">
                        <a class="table__accion table__accion--editar" href="/admin/blogs/editar?id=<?php echo $blog->id; ?>">
                            <i class="fa-solid fa-pencil"></i> Editar
                        </a>

                        <form method="POST" action="/admin/blogs/eliminar" class="table__accion-form">
                            <input type="hidden" name="id" value="<?php echo $blog->id; ?>">
                            <input type="hidden" name="tipo" value="blog"> 
                            <button 
                                class="table__accion table__accion--eliminar" 
                                type="submit" 
                                onclick="openDeleteModal(event, <?php echo $blog->id; ?>, 'blog')"
                            >
                                <i class="fa-solid fa-trash"></i> Eliminar
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="t-align-center">No Hay Entradas de Blog Aún</p>
<?php endif; ?>