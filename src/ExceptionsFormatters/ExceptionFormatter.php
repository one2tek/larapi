<?php

namespace one2tek\larapi\ExceptionsFormatters;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionFormatter
{
    const DEFAULT_401_MESSAGE = 'Unauthorized.';
    const DEFAULT_403_MESSAGE = 'Forbidden.';
    const DEFAULT_404_MESSAGE = 'Page Not Found.';
    const DEFAULT_405_MESSAGE = 'Method Not Allowed.';
    const DEFAULT_500_MESSAGE = 'Whoops, looks like something went wrong.';
    const DEFAULT_503_MESSAGE = 'The server is currently unable to handle the request due to a temporary overloading or maintenance of the server.';

    public function format($request, $e)
    {
        $statusCode = 500;

        if ($e instanceof HttpExceptionInterface) {
            $statusCode = $e->getStatusCode();
        }

        $message = $this->getMessage($statusCode, $e);

        $data = [
            'success' => false,
            'status' => $statusCode,
            'message' => $message
        ];

        if (config('app.debug')) {
            $data['exception'] = (string) $e;
            $data['line'] = $e->getLine();
            $data['file'] = $e->getFile();
        }

        return response()->json($data, $statusCode);
    }

    public function getMessage($statusCode, $e)
    {
        switch ($statusCode) {
            case 401:
                $message = strlen($e->getMessage()) ? $e->getMessage() : self::DEFAULT_401_MESSAGE;
                break;
            case 403:
                $message = strlen($e->getMessage()) ? $e->getMessage() : self::DEFAULT_403_MESSAGE;
                break;
            case 404:
                $message = strlen($e->getMessage()) ? $e->getMessage() : self::DEFAULT_404_MESSAGE;
                break;
            case 405:
                $message = strlen($e->getMessage()) ? $e->getMessage() : self::DEFAULT_405_MESSAGE;
                break;
            case 500:
                $message = (app()->environment('production')) ? self::DEFAULT_500_MESSAGE : $e->getMessage();
                break;
            case 503:
                $message = self::DEFAULT_503_MESSAGE;
                break;
            default:
                $message = $e->getMessage();
                break;
        }

        return $message;
    }
}
