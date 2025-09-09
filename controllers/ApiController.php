<?php

namespace Controllers;

use Model\Blog;
use Model\BlogView;
use Model\ActiveRecord;

class ApiController {

    public static function update_likes() {
        header('Content-Type: application/json');
        
        $blog_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$blog_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID no válido']);
            return;
        }

        $blog = Blog::find($blog_id);
        if (!$blog) {
            echo json_encode(['status' => 'error', 'message' => 'Blog no encontrado']);
            return;
        }

        $liked_blogs = isset($_COOKIE['liked_blogs']) ? json_decode($_COOKIE['liked_blogs'], true) : [];

        if (!is_array($liked_blogs)) { // Verificación por si la cookie está corrupta
            $liked_blogs = [];
        }

        if (in_array($blog_id, $liked_blogs)) {
            // Quitar like
            $blog->likes = max(0, $blog->likes - 1);
            $index = array_search($blog_id, $liked_blogs);
            unset($liked_blogs[$index]);
            $liked = false;
        } else {
            // Añadir like
            $blog->likes++;
            $liked_blogs[] = $blog_id;
            $liked = true;
        }
        
        // 3. Guardamos el array actualizado de vuelta en la cookie
        // El array se convierte a string JSON para poder guardarlo
        // time() + 31536000 = 1 año de duración
        // "/" significa que la cookie estará disponible en todo el sitio web
        setcookie('liked_blogs', json_encode(array_values($liked_blogs)), time() + 31536000, "/");
        $blog->guardar();

        echo json_encode(['status' => 'success', 'likes' => $blog->likes, 'liked' => $liked]);
    }

    public static function add_view() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $blog_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $ip_address = $_SERVER['REMOTE_ADDR'];

        if (!$blog_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID no válido']);
            return;
        }
        
        $blog = Blog::find($blog_id);
        if (!$blog) {
            echo json_encode(['status' => 'error', 'message' => 'Blog no encontrado']);
            return;
        }
        
        if(isset($_SESSION['id']) && $blog->autor_id == $_SESSION['id']) {
            echo json_encode(['status' => 'is_author']);
            return;
        }

        $view_exists = BlogView::whereArray([
            'blog_id' => $blog_id,
            'ip_address' => $ip_address,
            'view_date' => date('Y-m-d')
        ]);

        if (empty($view_exists)) {
            $view = new BlogView([
                'blog_id' => $blog_id,
                'ip_address' => $ip_address,
                'view_date' => date('Y-m-d')
            ]);
            
            // --- LÍNEAS DE DEPURACIÓN ---
            error_log("Intentando guardar esta vista: " . print_r($view, true));
            $resultado_vista = $view->guardar();
            error_log("Resultado de guardar vista: " . print_r($resultado_vista, true));
            // --- FIN DE LÍNEAS DE DEPURACIÓN ---

            if ($resultado_vista) {
                $blog->views = ($blog->views ?? 0) + 1;

                // --- LÍNEAS DE DEPURACIÓN ---
                error_log("Intentando actualizar este blog: " . print_r($blog, true));
                $resultado_blog = $blog->guardar();
                error_log("Resultado de actualizar blog: " . print_r($resultado_blog, true));
                // --- FIN DE LÍNEAS DE DEPURACIÓN ---
            }
        }

        echo json_encode(['status' => 'success', 'views' => $blog->views]);
    }
}