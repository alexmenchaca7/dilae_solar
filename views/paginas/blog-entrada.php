<main class="seccion">
    <div class="entrada-blog contenedor-blog" data-blog-id="<?php echo $blog->id; ?>"> 
        <div class="entrada-blog__header">
            <img src="/img/usuarios/<?php echo $blog->autor->imagen ?? 'default.png'; ?>" alt="Imagen del Autor del Articulo">
            <small><?php echo $blog->autor_nombre; ?>  -  <?php echo date('d M Y', strtotime($blog->fecha_creacion)); ?>  -  <?php echo $blog->lectura_estimada; ?> Min. de lectura</small>
        </div>

        <article class="entrada-blog__contenido">
            <h1><?php echo $blog->titulo; ?></h1>
            <picture>
                <source srcset="/img/blogs/<?php echo $blog->imagen; ?>.webp" type="image/webp">
                <source srcset="/img/blogs/<?php echo $blog->imagen; ?>.png" type="image/png">
                <img loading="lazy" src="/img/blogs/<?php echo $blog->imagen; ?>.png" alt="Imagen de Entrada de Blog">
            </picture>
            <?php echo $blog->contenido; ?>
        </article>

        <div class="entrada-blog__footer">
            <hr class="entrada-blog__division">
            <div class="social">
                <div class="logo-container">
                    <a rel="noopener noreferrer" target="_blank" href="https://www.instagram.com">
                        <i class="fa-brands fa-instagram"></i>
                    </a>
                </div>
                <div class="logo-container">
                    <a rel="noopener noreferrer" target="_blank" href="https://www.facebook.com">
                        <i class="fa-brands fa-facebook"></i>
                    </a>
                </div>
                <div class="logo-container">
                    <a rel="noopener noreferrer" target="_blank" href="https://x.com">
                        <i class="fa-brands fa-twitter"></i>
                    </a>
                </div>
            </div>
            <hr class="entrada-blog__division">
            <div class="entrada-blog__acciones">
                <small><?php echo $blog->views ?? 0; ?> visualizaciones</small>
                <div class="entrada-blog__btn-favorito">
                    <button class="btn-favorito" data-id="<?php echo $blog->id; ?>">
                        <?php
                            $liked_blogs_cookie = isset($_COOKIE['liked_blogs']) ? json_decode($_COOKIE['liked_blogs'], true) : [];
                            if (!is_array($liked_blogs_cookie)) $liked_blogs_cookie = [];
                            $es_likeado = in_array($blog->id, $liked_blogs_cookie);
                        ?>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="<?php echo $es_likeado ? 'liked' : ''; ?>">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                    </button>
                    <span class="likes-count"><?php echo $blog->likes ?? 0; ?></span>
                </div>
            </div>
        </div>  
    </div>

    <?php if (!empty($blogs_relacionados)) : ?>
        <section class="seccion blogs-relacionados contenedor">
            <div class="blogs-relacionados__header">
                <h2>Entradas recientes</h2>
                <a href="/blogs">Ver todo</a>
            </div>

            <div class="blogs__grid">
                <?php foreach ($blogs_relacionados as $blog_relacionado) : ?>
                    <div class="blog">
                        <a href="/blog/<?php echo htmlspecialchars($blog->slug); ?>" class="blog__imagen">
                            <picture>
                                <source srcset="/img/blogs/<?php echo $blog_relacionado->imagen; ?>.webp" type="image/webp">
                                <source srcset="/img/blogs/<?php echo $blog_relacionado->imagen; ?>.png" type="image/png">
                                <img loading="lazy" src="/img/blogs/<?php echo $blog_relacionado->imagen; ?>.png" alt="Imagen de Entrada de Blog">
                            </picture>
                        </a>
                        <div class="blog__contenido">
                            <small><?php echo date('d M Y', strtotime($blog->fecha_creacion)); ?>  -  <?php echo $blog->lectura_estimada; ?> Min. de lectura</small>
                            <a href="/blog/<?php echo htmlspecialchars($blog->slug); ?>">
                                <h2><?php echo $blog->titulo; ?></h2>
                                <p><?php echo substr(strip_tags($blog->contenido), 0, 100) . '...'; ?></p>
                            </a>
                        </div>
                        <hr class="blog__division">
                        <div class="blog__acciones">
                            <div class="blog__meta">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 13" fill="none">
                                        <path d="M8.98175 0.0442839C6.42933 0.366909 3.96677 1.73909 1.51237 4.19757C0.777271 4.93675 0.230034 5.56566 0.107518 5.8107C-0.0190813 6.06798 -0.0354167 6.43961 0.0666798 6.74998C0.152441 7.01135 0.450563 7.36665 1.35718 8.2896C3.74215 10.7236 6.04545 12.108 8.47943 12.5654C9.12468 12.6879 10.4601 12.7165 11.138 12.6267C13.5475 12.2918 15.8998 11.0013 18.2399 8.72249C18.979 8.00373 19.7795 7.11753 19.9061 6.87658C19.9837 6.72956 20 6.63972 20 6.33343C20 6.00672 19.9877 5.94138 19.8857 5.75761C19.6488 5.33288 18.0561 3.69526 17.1495 2.94382C15.2096 1.33887 13.3882 0.436335 11.4035 0.097374C10.7827 -0.0047226 9.58616 -0.033309 8.98175 0.0442839ZM10.9542 1.33478C11.7424 1.42463 12.8655 1.77584 13.7027 2.19648C15.1974 2.94382 16.7819 4.20982 18.2684 5.83928L18.7136 6.32118L18.5829 6.48862C18.3583 6.7704 17.1372 8.00373 16.7166 8.37536C14.9033 9.96398 13.2698 10.8624 11.4852 11.2463C11.0114 11.3443 10.9012 11.3525 10.015 11.3525C9.12876 11.3525 9.0185 11.3443 8.54477 11.2463C6.25373 10.7522 4.00352 9.29014 1.76148 6.82758L1.31634 6.34568L1.45111 6.17824C1.69206 5.86787 2.88046 4.67538 3.38686 4.23024C5.46146 2.40884 7.56056 1.40421 9.5249 1.29395C10.0721 1.26536 10.4193 1.27353 10.9542 1.33478Z" fill="black"/>
                                        <path d="M9.19859 3.13169C8.59417 3.29096 8.08369 3.58908 7.64672 4.05055C7.32409 4.38951 7.2465 4.49569 7.05864 4.86733C6.81361 5.36147 6.73193 5.72494 6.73193 6.33343C6.73193 6.76224 6.74827 6.92559 6.82178 7.18696C7.16074 8.37536 8.14903 9.31465 9.31702 9.56376C9.66415 9.63727 10.3666 9.63727 10.7137 9.56376C11.8817 9.31465 12.87 8.37536 13.2089 7.18696C13.3396 6.72548 13.3396 5.94138 13.2089 5.4799C12.8741 4.31192 11.9634 3.42164 10.7995 3.12352C10.3788 3.01734 9.62331 3.02142 9.19859 3.13169ZM10.8771 4.5447C12.1267 5.16545 12.4003 6.75407 11.4202 7.7342C11.2405 7.91388 11.0731 8.03232 10.8444 8.14258C10.5299 8.29368 10.5218 8.29368 10.0154 8.29368C9.51304 8.29368 9.50079 8.2896 9.18633 8.14258C8.61459 7.86896 8.18579 7.33398 8.05919 6.72957C7.88358 5.89237 8.30422 4.98984 9.05157 4.59371C9.44362 4.38543 9.58247 4.35276 10.0807 4.36501C10.5218 4.37318 10.5422 4.37726 10.8771 4.5447Z" fill="black"/>
                                    </svg>
                                    99
                                </span>
                            </div>
            
                            <div class="blog__btn-favorito">
                                <button class="btn-favorito" data-id="<?php echo $blog->id; ?>">
                                    <?php
                                        $liked_blogs_cookie = isset($_COOKIE['liked_blogs']) ? json_decode($_COOKIE['liked_blogs'], true) : [];
                                        if (!is_array($liked_blogs_cookie)) $liked_blogs_cookie = [];
                                        $es_likeado = in_array($blog->id, $liked_blogs_cookie);
                                    ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="<?php echo $es_likeado ? 'liked' : ''; ?>">
                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                    </svg>
                                </button>
                                <span class="likes-count"><?php echo $blog->likes ?? 0; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>