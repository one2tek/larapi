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
            default:
                $title = $e->getMessage();
                break;
        }

        switch ($status) {
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
                    'message' => (app()->environment('production') && $status == 500) ? 'Whoops, looks like something went wrong.' : $title,
                    'exception' => (app()->environment('production')) ? '' : (string) $e,
                    'line' => (app()->environment('production')) ? '' : $e->getLine(),
                    'file' => (app()->environment('production')) ? '' : $e->getFile()
                ];
        }

        return response()->json($json, $status);
    }
}
