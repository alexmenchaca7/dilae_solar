<?php
  require_once __DIR__ . '/../includes/app.php';

  use Model\Blog;

  // Obtener la fecha actual en formato W3C
  $current_date = date('Y-m-d');

  // Iniciar la generación del XML
  header('Content-Type: application/xml; charset=utf-8');
  echo '<?xml version="1.0" encoding="UTF-8"?>';
  echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

  // URLs estáticas
  $static_pages = [
      '/' => '1.0',
      '/nosotros' => '0.8',
      '/soluciones' => '0.8',
      '/calculadora' => '0.8',
      '/blogs' => '0.9',
      '/contacto' => '0.7',
      '/terminos' => '0.5',
      '/privacy' => '0.5'
  ];

  foreach ($static_pages as $path => $priority) {
      echo '<url>';
      echo '<loc>' . $_ENV['HOST'] . $path . '</loc>';
      echo '<lastmod>' . $current_date . '</lastmod>';
      echo '<priority>' . $priority . '</priority>';
      echo '</url>';
  }

  // URLs dinámicas de los blogs
  $blogs = Blog::obtenerBlogsPublicadosPaginados(1000, 0); // Ajusta el límite si tienes más de 1000 blogs

  foreach ($blogs as $blog) {
      echo '<url>';
      echo '<loc>' . $_ENV['HOST'] . '/blog/' . htmlspecialchars($blog->slug) . '</loc>';
      // Usar la fecha de actualización del blog si está disponible, si no, la de creación
      $lastmod = !empty($blog->fecha_actualizacion) ? date('Y-m-d', strtotime($blog->fecha_actualizacion)) : date('Y-m-d', strtotime($blog->fecha_creacion));
      echo '<lastmod>' . $lastmod . '</lastmod>';
      echo '<priority>0.9</priority>'; // Alta prioridad para el contenido nuevo
      echo '</url>';
  }

  echo '</urlset>';
?>