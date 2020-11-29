<?php

return [
    'exceptions_formatters' => [
        Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException::class => one2tek\larapi\ExceptionsFormatters\UnprocessableEntityHttpExceptionFormatter::class,
        Throwable::class => one2tek\larapi\ExceptionsFormatters\ExceptionFormatter::class
    ]
];
