<?php

return [
    'exceptions_formatters' => [
        Throwable::class => one2tek\larapi\ExceptionFormatter::class,
        SymfonyException\UnprocessableEntityHttpException::class => one2tek\larapi\UnprocessableEntityHttpExceptionFormatter::class
    ]
];
