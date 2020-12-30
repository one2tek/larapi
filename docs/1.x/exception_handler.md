# Exception Handler

`Larapi` included a Laravel exception handler build specifically for APIs.
When building APIs there are specific formatting do's and dont's on how to send errors back to the user. Frameworks like Laravel are not build specifically for API builders. `Larapi` bridges that gap. For instance, specifications like [JSON API](https://jsonapi.org/) have [guidelines for how errors should be formatted](https://jsonapi.org/format/#error-objects).

# Configure

Open `Handler.php` and modify function `render` like below:

```php
use one2tek\larapi\Exceptions\ApiException;

public function render($request, Throwable $e)
{
    $apiException = new ApiException($request, $e);
    
    return $apiException->generateExceptionResponse();
}
```

# Formatters

`Larapi` already comes with sensible formatters out of the box. In `config/larapi.php` is a section where the formatter priority is defined.

```php
'exceptions_formatters' => [
    Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException::class => one2tek\larapi\ExceptionsFormatters\UnprocessableEntityHttpExceptionFormatter::class,
    Throwable::class => one2tek\larapi\ExceptionsFormatters\ExceptionFormatter::class
]
```

You can write custom formatters easily:

```php
<?php

namespace Infrastructure\ExceptionsFormatters;

class NotFoundHttpExceptionFormatter
{
    const STATUS_CODE = 404;
    const MESSAGE = 'Page Not Found.';

    public function format($request, $e)
    {
        $data = [
            'success' => false,
            'status' => self::STATUS_CODE,
            'message' => self::MESSAGE
        ];
        
        return response()->json($data, self::STATUS_CODE);
    }
}
```

Now you just need to add it to `config/larapi.php` and all `NotFoundHttpExceptions` will be formatted using our custom formatter.