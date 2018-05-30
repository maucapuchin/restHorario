<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}

//materias.php

// TODO: Corregir error al buscar materia que no existe
/**
 *
{
  "clave": "INF-2010",
  "carrera":"info",
  "creditos_total":8,
  "nombre": "Servicios Web",
  "dia":"lunes",
  "tipo_materia":"repe",
  "aula_materia":"b3"
  
  
}
 */
/**
 *
 Acceder al recurso materias
 * GET
 * http://localhost/restHorario/horarios/
 *
 Registro de materias
 * POST
 * http://localhost/restHorario/horarios/registro
 *
 Obtener materia por id
 * GET
 * http://localhost/restHorario/horarios/[id]
 Modificar materias
 * PUT
 * http://localhost/restHorario/horarios/[id]
 Eliminar materias
 * DELETE
 * http://localhost/restHorario/horarios/[id]
 */
/**
 *
 */
class horarios {
    
  const NOMBRE_TABLA = "horarios";
  const CLAVE = "clave";
  const ID_HORARIOS = "id_horarios";  
  const ID_ALUMNO = "id_alumno";
  const CARRERA = "carrera";
  const CREDITOS_TOTAL = "creditos_total";
  const FECHA = "fecha";
  const DIA = "dia";
  const TIPO_MATERIA = "tipo_materia";
  const AULA_MATERIA = "aula_materia";
  

  public static function get($solicitud)
  {
    $id_alumno = alumno::autorizar();
      
    if (empty($solicitud)) {
      return self::obtenerHorario($id_alumno);
    } else {
      return self::obtenerHorario($id_alumno, $solicitud[0]);
    }
  }
  public static function post()
  {
    $id_alumno = alumno::autorizar();

    $cuerpo = file_get_contents('php://input');
    $horario = json_decode($cuerpo);

    $claveHorario = self::crearHorario($id_alumno, $horario);

    http_response_code(201);

    return [
      "estado"=>"Registro exitoso",
      "mensaje"=>"Horario creada",
      "Clave"=>$claveHorario
    ];
  }
  public static function put($solicitud)
  {
    
    $id_alumno = alumno::autorizar();
      
    if (!empty($solicitud)) {
      $cuerpo = file_get_contents('php://input');
      $horario = json_decode($cuerpo);

      if (self::actualizarHorario($id_alumno, $horario, $solicitud[0]) > 0) { 
        http_response_code(200);
        return [
          "estado" => "OK",
          "mensaje" => "Registro actualizado correctamente"
        ];
      } else {
        throw new ExceptionApi("Materia no actualizada",
                "No se actualizo la materia solicitada",404);
      }
    } else {
      throw new ExceptionApi("Parametros incorrectos",
              "Faltan parametros para la consulta", 422);
    }
  }
  public static function delete($solicitud)
  {
    $id_alumno = alumno::autorizar();
    if (self::eliminarHorario($id_alumno, $solicitud[0])>0) {
      http_response_code(200);
      return[
        "estado"=>"Ok",
        "mensaje"=>"Horario eliminado correctamente"
      ];
    } else {
      throw new ExceptionApi("Error en solicitud",
              "Error al eliminar el horario: Clave no encontrada",404);
    }
  }
  private function obtenerHorario($id_alumno, $claveHorario = NULL)
  {
    try {
      if (!$claveHorario) {
        
        $sql = "SELECT * FROM " . self::NOMBRE_TABLA .
               " WHERE " . self::ID_ALUMNO . "=?";
        $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
        $query = $pdo->prepare($sql);
        $query->bindParam(1,$id_alumno,PDO::PARAM_INT);
      } else {
        $sql = "SELECT * FROM " . self::NOMBRE_TABLA .
               " WHERE " . self::ID_ALUMNO . "=? AND " .
               self::CLAVE . "=?";
        $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
        $query = $pdo->prepare($sql);
        $query->bindParam(1,$id_alumno,PDO::PARAM_INT);
        $query->bindParam(2,$claveHorario,PDO::PARAM_STR);
      }
      if ($query->execute()) {
        http_response_code(200);
        return [
          "estado" => "OK",
          "mensaje" => $query->fetchAll(PDO::FETCH_ASSOC)
        ];
      } else {
        throw new ExceptionApi("Error en consulta",
                "Se ha producido un error al ejecutar la consulta");
      }
    } catch (PDOException $e) {
      throw new ExceptionApi("Error de PDO",
              $e->getMessage());
    }

  }
  private function crearHorario($id_alumno, $horario){
    if ($horario) {
      try {
        $sql = "INSERT INTO " . self::NOMBRE_TABLA . " (" .
          self::ID_ALUMNO . "," .
          self::CLAVE . "," .
          self::CARRERA . "," .
          self::CREDITOS_TOTAL . "," .
          self::FECHA . "," .
          self::DIA . "," .
          self::TIPO_MATERIA . "," .
          self::AULA_MATERIA  . ")" .
          " VALUES(?,?,?,?,?,?,?,?)";
  

        $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
        $query = $pdo->prepare($sql);
        //echo "xxx".$horario->fecha."xxx;";
        $query->bindParam(1,$horario->clave);
        $query->bindParam(2,$id_alumno);
        $query->bindParam(3,$horario->carrera);
        $query->bindParam(4,$horario->creditos_total);
        $query->bindParam(5,$horario->fecha);
        $query->bindParam(6,$horario->dia);
        $query->bindParam(7,$horario->tipo_materia);
        $query->bindParam(8,$horario->aula_materia);
        

        $query->execute();

        return $horario->clave;

      } catch (PDOException $e) {
        throw new ExceptionApi("Error de BD",
                $e->getMessage());
      }

    } else {
      throw new ExceptionApi("Error de parametros",
              "Error al pasar la Materia");
    }
  }
  private function actualizarHorario($id_alumno, $horario, $claveHorario)
  {
    try {
      $sql = "UPDATE " . self::NOMBRE_TABLA .
        " SET " . 
          
        self::CARRERA . " = ?, ".
        self::CREDITOS_TOTAL . " = ?, ".
        self::DIA . " = ?, ".
        self::TIPO_MATERIA . " = ?, ".
        self::AULA_MATERIA . " = ? ".
        
        " WHERE " . self::CLAVE . " = ? AND "
        . self::ID_ALUMNO . "= ?";
      $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
      $query = $pdo->prepare($sql);

      $query->bindParam(1, $horario->carrera);
      $query->bindParam(2, $horario->creditos_total);
      $query->bindParam(3, $horario->dia);
      $query->bindParam(4, $horario->tipo_materia);
      $query->bindParam(5, $horario->aula_materia);
      $query->bindParam(6, $claveHorario);
      $query->bindParam(7, $id_alumno);

      $query->execute();
      return $query->rowCount();
    } catch (PDOException $e) {
      throw new ExceptionApi("Error en consulta",
              $e->getMessage());
    }

  }
  private function eliminarHorario($id_alumno, $claveHorario)
  {
    try {
      $sql = "DELETE FROM " . self::NOMBRE_TABLA .
        " WHERE " . self::ID_ALUMNO . "=? AND " .
        self::CLAVE . "=?";

      $query = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($sql);

      $query->bindParam(1, $id_alumno);
      $query->bindParam(2, $claveHorario);

      $query->execute();

      return $query->rowCount();

    } catch (PDOException $e) {
      throw new ExceptionApi("Error en base de datos",
              $e->getMessage(),400);
    }

  }
}
?>
