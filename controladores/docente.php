<?php

//docente.php
/**
 *
 Acceder al recurso docente
 * localhost/restHorario/docente/
 *
 Registro de docentes
 * POST
 * localhost/restHorario/docente/registro
 *
 Acceder al WS
 * POST
 * localhost/restHorario/docentes/login
 */

 /**
  *
  */
 class docente
 {

   //Datos de la tabla docente.
   const NOMBRE_TABLA = "docente";
   const ID_DOCENTE = "id_docente";
   const NOMBRE = "nombre";
   const CORREO = "correo";
   const PASSWORD = "password";
   const CORDINACION = "cordinacion";
   const CLAVE_API = "claveApi";

   const ESTADO_CREACION_OK = 200;
   const ESTADO_CREACION_ERROR = 403;
   const ESTADO_ERROR_DB = 500;
   const ESTADO_NO_CLAVE_API = 406;
   const ESTADO_CLAVE_NO_AUTORIZADA = 401;
   const ESTADO_URL_INCORRECTA = 404;
   const ESTADO_FALLA_DESCONOCIDA = 504;
   const ESTADO_DATOS_INCORRECTOS = 422;

   public static function post($solicitud)
   {
     if (isset($solicitud)) {
       if ($solicitud[0]  == "registro") {
         return self::registrar();
       } else if ($solicitud[0] == "login") {
         return self::ingresar();
       } else {
         throw new
         ExceptionApi(self::ESTADO_URL_INCORRECTA, "URL Incorrecta",400);
       }
    } else{
      ExceptionApi(self::ESTADO_DATOS_INCORRECTOS, "Solicitud incorrecta",400);
    }
   }

   private function registrar(){
     //{ "nombre":"Pedro","correo":"pedro@mail.com","password":"1234","cordinacion":"informatica"}
      $cuerpo = file_get_contents('php://input');
      $docente= json_decode($cuerpo);
      $resultado = self::crear($docente);
      switch ($resultado) {
        case self::ESTADO_CREACION_OK:
          http_response_code( 200);
          return [
              "estado"=>self::ESTADO_CREACION_OK,
              "mensaje"=>utf8_encode("¡Registro Exitoso!")
            ];
          break;
        case self::ESTADO_CREACION_ERROR:
          throw new ExceptionApi(
            self::ESTADO_CREACION_ERROR,
            "Error al crear al docente.");
          break;
        default:
          throw new ExceptionApi(
          self::ESTADO_FALLA_DESCONOCIDA,
          "Error desconocido.");
      }
   }
   private function ingresar(){
      $respuesta = array();

      $cuerpo = file_get_contents('php://input');
      $docente = json_decode($cuerpo);

      $correo = $docente->correo;
      $password = $docente->password;

      if (self::autenticar($correo, $password)) {
        $docenteDatos = self::getDocentePorCorreo($correo);
        if ($docenteDatos != NULL) {
          http_response_code(200);
          return ["estado"=>1, "docente"=>$docenteDatos];
        } else {
          throw new ExceptionApi(
            self::ESTADO_FALLA_DESCONOCIDA,
            "Ocurrio un error desconocido.");

        }
      } else {
        throw new ExceptionApi(
          self::ESTADO_DATOS_INCORRECTOS,
          "Correo o password incorrectos");

      }

   }
   private function crear($datosDocente){
     $nombre = $datosDocente->nombre;

     $password = $datosDocente->password;
     $passwordEnc = self::encriptarPassword($password);

     $correo = $datosDocente->correo;

     $claveApi = self::generarClaveApi();

     try {
       $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

       $sql = "INSERT INTO " . self::NOMBRE_TABLA." (".
              self::NOMBRE . "," .
              self::CORREO . "," .
              self::PASSWORD . "," .
              self::CORDINACION . "," .
              self::CLAVE_API . ")" .
              " VALUES(?,?,?,?,?)";
      $query = $pdo->prepare($sql);
      $query->bindParam(1,$nombre);
      $query->bindParam(2,$correo);
      $query->bindParam(3,$passwordEnc);
      $query->bindParam(4,$cordinacion);
      $query->bindParam(5,$claveApi);
      $resultado = $query->execute();
      if ($resultado) {
        return self::ESTADO_CREACION_OK;
      } else {return self::ESTADO_CREACION_ERROR;}
     } catch (PDOException $pdoe) {
        throw new ExceptionApi(self::ESTADO_ERROR_DB,
                $pdoe->getMessage());
     }
   }
   private function autenticar($correo, $password){
   
       $sql = "SELECT "
              . self::PASSWORD .
            " FROM "
              . self::NOMBRE_TABLA .
            " WHERE "
              . self::CORREO . " = ?";

     try {

       $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
       $query = $pdo->prepare($sql);
       $query->bindParam(1,$correo);
       $resultado = $query->execute();

       if ($query) {
           
         $resultado = $query->fetch();
         if(self::validarPassword($password,$resultado['password'])) {
           return true;
         } else {
           return false;
         }
       } else {
         return false;
       }

     } catch (PDOException $pdoe) {
        throw new ExceptionApi(self::ESTADO_ERROR_DB,
                $pdoe->getMessage());
     }


   }
       private function autorizar(){
     $cabeceras = apache_request_headers();
     if(isset($cabeceras["authorization"])){
         $claveApi = $cabeceras["authorization"];
         if(alumno::validadClaveApi($claveApi)){
             return alumno::getIdDocente($claveApi);
         }else{
             throw new ExceptionApi(
                 self::ESTADO_CLAVE_NO_AUTORIZADA,
             "Clave Api no autorizada");
         }
     }else{
         throw new ExceptionApi(
         self::ESTADO_NO_CLAVE_API,
         "Se requiere una clave API para autorizar");
     }
   }
    
   private function encriptarPassword($password){
     if ($password) {
       return password_hash($password, PASSWORD_DEFAULT);
     } else {
       return null;
     }
   }
   private function generarClaveApi() {
     $microt = microtime().rand();
     // echo "Microtime: " . $microt . "<br>";
     return md5($microt);
   }
   private function validarPassword($passwordClaro,
    $passwordEncrip){
     return password_verify($passwordClaro, $passwordEncrip);
   }
   
   private function getDocentePorId($id){
     # code...
   }
   private function getDocentePorCorreo($correo){
     $sql = "SELECT " .
              self::NOMBRE . ", " .
              self::CORREO . ", " .
              self::PASSWORD . ", " .
              self::CORDINACION . ", " .
              self::CLAVE_API  .
            " FROM " . self::NOMBRE_TABLA .
            " WHERE " . self::CORREO . " = ?";

    $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
    $query = $pdo->prepare($sql);
    $query->bindParam(1,$correo);

    if ($query->execute()) {
      return $query->fetch(PDO::FETCH_ASSOC);
    } else {
      return null;
    }

   }
   private function validadClaveApi($claveApi){
     //echo "Clave Api: " . $claveApi;
     $sql = "SELECT COUNT(" . self::ID_DOCENTE . ")" .
            " FROM " . self::NOMBRE_TABLA .
            " WHERE " . self::CLAVE_API . "= $claveApi";

    $pdo = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($sql);
    $pdo->execute();

    return $pdo->fetchColumn(0) > 0;
   }
   private function getIdDocente($claveApi){
     $sql = "SELECT " . self::ID_DOCENTE  .
             " FROM "  . self::NOMBRE_TABLA .
             " WHERE " . self::CLAVE_API . "= $claveApi";

      $pdo = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($sql);

      $pdo->execute();

      $resultado = $pdo->fetch();
      return $resultado['id_docente'];
   }
 }


?>
