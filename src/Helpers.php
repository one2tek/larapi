<?php

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

if (!function_exists('renderException')) {
    function renderException($request, $e)
    {
        $status = 500;

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
        }

        switch ($status) {
            case 401:
                $title = strlen($e->getMessage()) ? $e->getMessage() : 'Unauthorized.';
                break;
            case 403:
                $title = strlen($e->getMessage()) ? $e->getMessage() : 'Forbidden.';
                break;
            case 404:
                $title = strlen($e->getMessage()) ? $e->getMessage() : 'Not Found.';
                break;
            case 405:
                $title = strlen($e->getMessage()) ? $e->getMessage() : 'Method Not Allowed.';
                break;
            case 500:
                $title = (app()->environment('production')) ? 'Whoops, looks like something went wrong.' : $e->getMessage();
                break;
            case 503:
                $title = 'The server is currently unable to handle the request due to a temporary overloading or maintenance of the server.';
                break;
            default:
                $title = $e->getMessage();
                break;
        }

        switch ($status) {
            case 404:
                $json = [
                    'status' => $status,
                    'errors' => [
                        [
                            'title' => 404,
                            'detail' => $title,
                        ]
                    ]
                ];
                break;
                
            case 422:
                $decoded = json_decode($e->getMessage(), true);
        
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $decoded = [[$e->getMessage()]];
                }

                $data = array_reduce($decoded, function ($carry, $item) use ($e) {
                    return array_merge($carry, array_map(function ($current) use ($e) {
                        return ['title' => 'Validation error.', 'detail' => $current];
                    }, $item));
                }, []);

                $json = [
                    'status' => $status,
                    'errors' => $data
                ];
                break;

            default:
                $json = [
                    'status' => $status,
                    'message' => $title,
                    'exception' => (app()->environment('production')) ? '' : (string) $e,
                    'line' => (app()->environment('production')) ? '' : $e->getLine(),
                    'file' => (app()->environment('production')) ? '' : $e->getFile()
                ];
        }

        return response()->json($json, $status);
    }
}
