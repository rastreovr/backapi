<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait GenericControllerTrait
{
    /**
     * UFunción abstracta que establece el modelo de base de datos a partir
     * del cual se trabajará.
     *
     * @return string Debe regresar la clase del modelo a usar para el controlador
     */
    abstract protected function model();
    // Posible mejora: permitir sobreescribir el nombre del modelo

    /**
     * Si esta función regresa true, entonces se crearán bitácoras en la tabla de bitacoras_v3
     * para las acciones de Create, Update y Delete de las funciones genéricas
     * @return bool
     */
    protected function logToBitacora(): bool
    {
        return false;
    }

    /**
     * Obtiene el nombre de la clase a partir de la clase del modelo establecida
     *
     * @return string
     */
    protected function modelName()
    {
        $modelClass = explode("\\", $this->model());
        return end($modelClass);
    }

    /**
     * Muestra un listado de los registros de la base de datos.
     *
     * @param Request $request
     * @return JsonResponse respuesta de API. data contiene un listado de los objetos
     */
    protected function index(Request $request): JsonResponse
    {
        $allFound = $this->model()::all();

        return sendApiSuccess(
            $allFound,
            "Obtenidos los datos de '{$this->modelName()}' exitosamente"
        );
    }

    /**
     * Muestra un registro de la base de datos con el id especificado.
     *
     * @param Request $request
     * @param integer $id
     * @return JsonResponse respuesta de API, data contiene el objeto especifico buscado
     */
    protected function show(Request $request, int $id): JsonResponse
    {
        $found = $this->model()
            ::where("id", $id)
            ->first();
        if ($found) {
            return sendApiSuccess(
                $found,
                "Obtenido registro de '{$this->modelName()}' exitosamente"
            );
        }
        return sendApiSuccess(
            $found,
            "Registro de '{$this->modelName()}' no encontrado"
        );
    }

    /**
     * Crea un nuevo registro de la base de datos.
     *
     * @param Request $request
     * @return JsonResponse respuesta de API, data contiene el objeto creado
     */
    protected function store(Request $request, $extras = []): JsonResponse
    {
        $model = $this->model();
        $body =
            count($extras) >= 1
                ? array_merge($request->all(), $extras)
                : $request->all();
        $createObject = new $model($body);

        if (!$createObject->save()) {
            return sendApiFailure(
                (object) [],
                "Fallo al guardar registro de '{$this->modelName()}'"
            );
        }
        $createObject->refresh();

        $result = sendApiSuccess(
            $createObject,
            "Guardado registro de '{$this->modelName()}' exitosamente",
            201
        );

        if ($this->logToBitacora()) {
            $request
                ->user()
                ->tryLogToBitacora($result, $this->modelName(), "CREATE");
        }

        return $result;
    }

    /**
     * Actualiza los datos de un registro de la base de datos. También puede recuperar
     * un registro borrado de forma lógica si se usa el query param "restore":
     *
     * [HTTP PUT] http://api/v1/roles/1?restore
     *
     * Nota: No se permite hacer restore y actualizar el mismo registro en la misma petición
     *
     * @param Request $request
     * @param integer $id
     * @return JsonResponse respuesta de API, data contiene el registro actualizado.
     */
    protected function update(Request $request, int $id): JsonResponse
    {
        if ($request->has("restore")) {
            $found = $this->model()
                ::withTrashed()
                ->where("id", $id)
                ->first();

            $found->deleted_at = null;
            $found->save();
            return sendApiSuccess(
                (object) [],
                "Se restauró el registro de '{$this->modelName()}' exitosamente"
            );
        } else {
            $found = $this->model()
                ::where("id", $id)
                ->first();
        }

        if (is_null($found)) {
            return sendApiFailure(
                (object) [],
                "No se encontró registro de '{$this->modelName()}' para actualizar"
            );
        }

        if ($found->updateOrFail($request->all()) === false) {
            return sendApiFailure(
                $found,
                "Fallo al actualizar registro de '{$this->modelName()}'"
            );
        } else {
            $result = sendApiSuccess(
                $found,
                "Registro de '{$this->modelName()}' actualizado exitosamente"
            );

            if ($this->logToBitacora()) {
                $request
                    ->user()
                    ->tryLogToBitacora($result, $this->modelName(), "UPDATE");
            }
            return $result;
        }
    }

    /**
     * Borra un registro de la base de datos, es soft por default, si se quiere
     * borar de forma física, se debe pasar el query param "force":
     * [HTTP DELETE] http://api/v1/roles/1?force
     *
     * @param Request $request
     * @param integer $id
     * @return JsonResponse respuesta de API, data debería contener un objeto vacío
     */
    protected function destroy(Request $request, int $id): JsonResponse
    {
        $found = $this->model()
            ::where("id", $id)
            ->first();

        if (is_null($found)) {
            return sendApiFailure(
                (object) [],
                "No se encontró registro de '{$this->modelName()}' para eliminar"
            );
        }

        /** cualquier tabla que tenga la columna isDeletable es sujeta a esto */
        if (isset($found->isDeletable)) {
            if (!$found->isDeletable) {
                return sendApiFailure(
                    (object) [],
                    "Intento de eliminar un registro que no es borrable"
                );
            }
        }

        $delete = $request->has("force")
            ? $found->forceDelete()
            : $found->delete();

        if ($delete) {
            $resultMock = sendApiSuccess([
                "id" => $id,
            ]);

            if ($this->logToBitacora()) {
                $request
                    ->user()
                    ->tryLogToBitacora(
                        $resultMock,
                        $this->modelName(),
                        "DELETE"
                    );
            }
            return sendApiSuccess(
                (object) [],
                "Registro de '{$this->modelName()}' eliminado exitosamente"
            );
        } else {
            return sendApiFailure(
                (object) [],
                "Fallo al eliminar registro de '{$this->modelName()}'"
            );
        }
    }
}
