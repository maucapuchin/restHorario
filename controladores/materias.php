<?php
//materias.php

const NOMBRE_TABLA="materias";
const CLAVE="clave";
const NOMBRE="nombre";
const HT="ht";
const HP="hp";
const ID_DOCENTE="idDocente";

public static function get($solicitud){
    $idDocente = docentes::autorizar();
}
public static function post(){
    
}
public static function put($solicitud){
    
}
public static function delete($solicitud){
    
}
private function obtenerMaterias($idDocente, $claveMateria =NULL){
    
}
private function crearMateria($idDocente, $materia){
    
}
private function actualizarMateria($idDocente, $materia, $claveMateria){
    
}
private function eliminarMateria($idDocente, $claveMateria){
    
}

?>
