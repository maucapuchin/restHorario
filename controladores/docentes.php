<?php
//https://github.com/ozwaldo/restDocentes
//docentes.php
/**
 *
 Acceder al recurso docentes
 * GET
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
 class docentes
 {

   //Datos de la tabla docentes.
   const NOMBRE_TABLA = "docentes";
   const ID_DOCENTE = "idDocente";
   const NOMBRE = "nombre";
   const A_PATERNO = "a_paterno";
   const A_MATERNO = "a_materno";
   const PASSWORD = "password";
   const CARRERA = "carrera";
   const CLAVE_API = "claveApi";
   const CORREO = "correo";

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
     //{ "nombre":"Pedro","a_paterno":"Perez","a_materno":"Lopez","password":"1234","carrera":"Informatica","correo":"pedro@mail.com"}
     //{ "password":"1234","correo":"pedro@mail.com"}
      $cuerpo = file_get_contents('php://input');
      $docente = json_decode($cuerpo);
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
              self::A_PATERNO . ",".
              self::A_MATERNO . "," .
              self::PASSWORD . "," .
              self::CARRERA . "," .
              self::CLAVE_API . "," .
              self::CORREO . ")" .
              " VALUES(?,?,?,?,?,?,?)";
      $query = $pdo->prepare($sql);
      $query->bindParam(1,$nombre);
      echo "<br>".$datosDocente->a_paterno."<br>";
      $query->bindParam(2,$datosDocente->a_paterno);
      $query->bindParam(3,$datosDocente->a_materno);
      $query->bindParam(4,$passwordEnc);
      $query->bindParam(5,$datosDocente->carrera);
      $query->bindParam(6,$claveApi);
      $query->bindParam(7,$correo);
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

   public static function autorizar(){
     $cabeceras = apache_request_headers();
     //echo "clave apli auth: " . $cabeceras["Authorization"];
     if (isset($cabeceras["Authorization"])) {
       $claveApi = $cabeceras["Authorization"];
       if (docentes::validadClaveApi($claveApi)) {
         return docentes::getIdDocente($claveApi);
       } else {
         throw new ExceptionApi(
           self::ESTADO_CLAVE_NO_AUTORIZADA,
           "Clave API no autorizada");
       }
     } else {
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
              self::A_PATERNO . ", " .
              self::A_MATERNO . ", " .
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
      return $resultado['idDocente'];
   }
 }


?>
