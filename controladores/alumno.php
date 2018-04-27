<?php

//docentes.php
/**
 *
 Acceder al recurso docentes
 * localhost/~instructor/restDocente/docentes/
 *
 Registro de docentes
 * POST
 * localhost/~instructor/restDocente/docentes/registro
 *
 Acceder al WS
 * POST
 * localhost/~instructor/restDocente/docentes/login
 */

 /**
  *
  */
 class alumno
 {

   //Datos de la tabla docentes.
   const NOMBRE_TABLA = "alumno";
   const ID_ALUMNO = "id_alumno";
   const NOMBRE = "nombre";
   const SEMESTRE = "semestre";
   const CORREO = "correo";
   const PASSWORD = "password";
   const CARRERA = "carrera";
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
     //{ "nombre":"Pedro","semestre":"3","correo":"pedro@mail.com","password":"1234","carrera":"teologia"}
      $cuerpo = file_get_contents('php://input');
      $alumno= json_decode($cuerpo);
      $resultado = self::crear($alumno);
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
            "Error al crear al alumno.");
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
      $alumno = json_decode($cuerpo);

      $correo = $alumno->correo;
      $password = $alumno->password;

      if (self::autenticar($correo, $password)) {
        $alumnoDatos = self::getAlumnoPorCorreo($correo);
        if ($alumnoDatos != NULL) {
          http_response_code(200);
          return ["estado"=>1, "alumno"=>$alumnoDatos];
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
   private function crear($datosAlumno){
     $nombre = $datosAlumno->nombre;

     $password = $datosAlumno->password;
     $passwordEnc = self::encriptarPassword($password);

     $correo = $datosAlumno->correo;

     $claveApi = self::generarClaveApi();

     try {
       $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

       $sql = "INSERT INTO " . self::NOMBRE_TABLA." (".
              self::NOMBRE . "," .
              self::SEMESTRE . "," .
              self::CORREO . "," .
              self::PASSWORD . "," .
              self::CARRERA . "," .
              self::CLAVE_API . ")" .
              " VALUES(?,?,?,?,?,?)";
      $query = $pdo->prepare($sql);
      $query->bindParam(1,$nombre);
      $query->bindParam(2,$datosAlumno->semestre);
      $query->bindParam(3,$correo);
      $query->bindParam(4,$passwordEnc);
      $query->bindParam(5,$datosAlumno->carrera);
      $query->bindParam(6,$claveApi);
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
         if (self::validarPassword($password,$resultado['password'])) {
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
             return alumno::getIdAlumno($claveApi);
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
   private function getAlumnoPorCorreo($correo){
     $sql = "SELECT " .
              self::NOMBRE . ", " .
              self::SEMESTRE . ", " .
              self::PASSWORD . ", " .
              self::CARRERA . ", " .
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
     # code...
   }
   private function getIdDocente($claveApi){
     # code...
   }
 }


?>
