<?php

namespace one2tek\larapi\Exceptions;

class ApiException
{
    protected $request;
    protected $e;

    public function __construct($request, $e)
    {
        $this->request = $request;
        $this->e = $e;
    }

    public function generateExceptionResponse()
    {
        $formatters = config('larapi.exceptions_formatters');

        foreach ($formatters as $exceptionType => $formatter) {
            if (!($this->e instanceof $exceptionType)) {
                continue;
            }

            if (!class_exists($formatter)) {
                continue;
            }

            $formatterInstance = new $formatter();
            return $formatterInstance->format($this->request, $this->e);
        }
    }
}
