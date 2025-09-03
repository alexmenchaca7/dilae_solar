<?php

namespace Model;

class Usuario extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'nombre', 'apellido', 'email', 'pass', 'token', 'confirmado'];
    protected static $tabla = 'usuarios'; 
    
    // Propiedad con las columnas a buscar
    protected static $buscarColumnasDirectas = ['nombre', 'apellido', 'email'];


    public $id;
    public $nombre;
    public $apellido;
    public $email;
    public $pass;
    public $pass2;
    public $token;
    public $confirmado;

    public $password_actual;
    public $password_nuevo; 


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->apellido = $args['apellido'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->pass = $args['pass'] ?? '';
        $this->pass2 = $args['pass2'] ?? '';
        $this->token = $args['token'] ?? '';
        $this->confirmado = $args['confirmado'] ?? null;
    }


    // Validar el Registro de Usuarios
    public function validarRegistro() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre es obligatorio';
        }
    
        if(!$this->apellido) {
            self::$alertas['error'][] = 'El apellido es obligatorio';
        }
    
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        } else if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'Email no válido';
        } else {
            // Validar que el email no esté registrado
            $existeUsuario = $this::where('email', $this->email);
            
            if ($existeUsuario && $existeUsuario->id !== $this->id) {
                self::$alertas['error'][] = 'El correo ya está registrado';
            }
        }
    
        return self::$alertas;
    }


    // Validar el Login de Usuarios
    public function validarLogin() {
        if(!$this->email) {
            self::$alertas['error'][] = 'El email del usuario es obligatorio';
        } else if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'Email no válido';
        }
        if(!$this->pass) {
            self::$alertas['error'][] = 'El password no puede ir vacio';
        }
        return self::$alertas;
    }


    // Valida un email
    public function validarEmail() {
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        } else if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'Email no válido';
        }
        return self::$alertas;
    }


    // Valida el password 
    public function validarPassword() {
        if(!$this->pass) {
            self::$alertas['error'][] = 'El password no puede ir vacío';
        } else if(strlen($this->pass) < 6) {
            self::$alertas['error'][] = 'El password debe contener al menos 6 caracteres';
        }

        if($this->pass !== $this->pass2) {
            self::$alertas['error'][] = 'Los passwords no coinciden';
        }

        return self::$alertas;
    }

    public function nuevo_password() : array {
        if(!$this->password_actual) {
            self::$alertas['error'][] = 'El password actual no puede ir vacio';
        }
        if(!$this->password_nuevo) {
            self::$alertas['error'][] = 'El password nuevo no puede ir vacio';
        }
        if(strlen($this->password_nuevo) < 6) {
            self::$alertas['error'][] = 'El password debe contener al menos 6 caracteres';
        }
        return self::$alertas;
    }


    // Comprobar el password
    public function comprobar_password() : bool {
        return password_verify($this->password_actual, $this->pass );
    }


    // Hashea el password
    public function hashPassword() : void {
        $this->pass = password_hash($this->pass, PASSWORD_BCRYPT);
    }


    // Generar un Token
    public function crearToken() : void {
        $this->token = uniqid();
    }

    public static function buscar($termino) {
        $condicionesGenerales = [];
        $terminoGeneral = trim($termino);

        if (empty($terminoGeneral)) {
            return $condicionesGenerales; // No hay término, no hay condiciones.
        }

        $palabrasBusqueda = explode(' ', $terminoGeneral);
        $palabrasBusqueda = array_filter($palabrasBusqueda); // Eliminar elementos vacíos

        if (empty($palabrasBusqueda)) {
            return $condicionesGenerales;
        }

        foreach ($palabrasBusqueda as $palabra) {
            $palabraEscapada = self::$conexion->escape_string($palabra);
            $palabraLower = mb_strtolower($palabraEscapada, 'UTF-8');

            $condicionesParaEstaPalabra = [];

            if (!empty(static::$buscarColumnasDirectas)) {
                foreach (static::$buscarColumnasDirectas as $columna) {
                    $condicionesParaEstaPalabra[] = "LOWER(usuarios.{$columna}) LIKE '%" . $palabraLower . "%'";
                }
            }
            
            if (!empty($condicionesParaEstaPalabra)) {
                $condicionesGenerales[] = "(" . implode(' OR ', $condicionesParaEstaPalabra) . ")";
            }
        }
        
        return $condicionesGenerales;
    }
}