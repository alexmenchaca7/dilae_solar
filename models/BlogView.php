<?php

namespace Model;

class BlogView extends ActiveRecord {
    protected static $tabla = 'blog_views';
    protected static $columnasDB = ['id', 'blog_id', 'ip_address', 'view_date'];

    public $id;
    public $blog_id;
    public $ip_address;
    public $view_date;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->blog_id = $args['blog_id'] ?? null;
        $this->ip_address = $args['ip_address'] ?? '';
        $this->view_date = $args['view_date'] ?? date('Y-m-d');
    }
}