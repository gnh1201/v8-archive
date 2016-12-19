<?php

namespace Directus\View;

use Directus\Permissions\Exception\AclException;
use Directus\Database\Exception\CustomUiValidationError;
use Directus\Database\Exception\DuplicateEntryException;
use Directus\Database\Exception\RelationshipMetadataException;

class ExceptionView
{

    public static function exceptionHandler($app, $exception)
    {

        $response = $app->response();
        $response->header('Content-type', 'application/json');

        $httpCode = 500;
        $data = [];

        /**
         * Directus\Permissions\Exception\AclException & subclasses
         */
        if ($exception instanceof AclException || is_subclass_of($exception, 'Directus\Permissions\Exception\AclException')) {
            $httpCode = 403;
            $data = ['message' => $exception->getMessage()];
        } /**
         * Directus\Database\Exception\RelationshipMetadataException
         */
        elseif ($exception instanceof RelationshipMetadataException) {
            $httpCode = 424;
            $data = ['message' => $exception->getMessage()];
        }

        /**
         * Directus\Database\Exception\SuppliedArrayAsColumnValue
         */
        // elseif($exception instanceof SuppliedArrayAsColumnValue) {
        //     $httpCode = 422;
        //     $data = array('message' => $exception->getMessage());
        // }

        /**
         * Directus\Database\Exception\CustomUiValidationError
         */
        elseif ($exception instanceof CustomUiValidationError) {
            $httpCode = 422;
            $data = ['message' => $exception->getMessage()];
        } /**
         * Directus\Database\Exception\DuplicateEntryException
         */
        elseif ($exception instanceof DuplicateEntryException) {
            $httpCode = 409;
            $data = ['message' => $exception->getMessage()];
        } // @todo log error nonetheless
        else {
            $data = ['message' => 'Internal Server Error'];
            if ('production' !== DIRECTUS_ENV) {
                $data = [
                    'code' => $exception->getCode(),
                    'class' => get_class($exception),
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTrace(),
                    'traceAsString' => $exception->getTraceAsString(),
                ];
            }
        }

        $data = @json_encode($data);
        if ('production' !== DIRECTUS_ENV) {
            $data = JsonView::format_json($data);
        }

        http_response_code($httpCode);
        header('Content-type: application/json');
        echo $data;
        exit;
    }

}
