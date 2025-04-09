<?php
namespace App\Utils;

use App\Models\Usuario\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;


class Utils{

    /**
     * !FUNCIONES: DEBUGGER
     */
    public static function debuggerQuery($query){

            // Obtén la consulta generada por Laravel sin ejecutarla
            $sqlQuery = $query->toSql();

            // Obtiene los valores enlazados
            $bindings = $query->getBindings();

            // Combina la consulta y los valores enlazados para obtener la consulta final
            $queryWithBindings = vsprintf(str_replace(['?'], ['\'%s\''], $sqlQuery), $bindings);

            // Imprime la consulta SQL con los valores enlazados
            echo $queryWithBindings;
            die;

    }

     /**
      * !Funcion para obtener un archivo mediante adentro de la ruta app/files/
      * @param mixed $ruta ejemplo nombreCarpeta/ejemplo.pdf
      */
      public static function getArchivoGeneral($ruta){
        $archivoPath = storage_path('app/files/' . $ruta);

            $archivo = File::get($archivoPath);
            $tipoMime = File::mimeType($archivoPath);

            return response($archivo)->header('Content-Type', $tipoMime);

      }

}

