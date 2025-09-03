<?php

namespace Model;

class Suscripcion extends ActiveRecord {
    
    protected static $tabla = 'suscripciones';
    protected static $columnasDB = ['id', 'email', 'fecha_suscripcion', 'token', 'confirmado'];

    public $id;
    public $email;
    public $fecha_suscripcion;
    public $token;
    public $confirmado;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->email = $args['email'] ?? '';
        $this->fecha_suscripcion = $args['fecha_suscripcion'] ?? date('Y-m-d');
        $this->token = $args['token'] ?? '';
        $this->confirmado = $args['confirmado'] ?? 0; // Por defecto, no confirmado
    }

    public function validar() {
        if (!$this->email || !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'El correo electrónico no es válido.';
        }
        return self::$alertas;
    }

    // Nueva función para crear el token
    public function crearToken() {
        $this->token = uniqid();
    }
}