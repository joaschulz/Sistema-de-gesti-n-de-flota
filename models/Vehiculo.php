<?php
class Vehiculo {
    public $patente;
    public $modelo;
    public $estado;
    public $kilometraje;

    public function __construct($patente, $modelo, $estado, $kilometraje) {
        $this->patente = $patente;
        $this->modelo = $modelo;
        $this->estado = $estado;
        $this->kilometraje = $kilometraje;
    }

    // Ejemplo de Regla de Negocio (Materia: Ingeniería de Software)
    public function puedeIngresarATaller() {
        return $this->estado === 'Operativo';
    }
}