<?php

namespace one2tek\larapi\ExceptionsFormatters;

class UnprocessableEntityHttpExceptionFormatter
{
    const STATUS_CODE = 422;
    const MESSAGE = 'Validation failed.';

    public function format($request, $e)
    {
        $data = [
            'success' => false,
            'status' => self::STATUS_CODE,
            'message' => self::MESSAGE
        ];

        // Laravel validation errors will return JSON string
        $decoded = json_decode($e->getMessage(), true);

        // Message was not valid JSON
        // This occurs when we throw UnprocessableEntityHttpExceptions
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Mimick the structure of Laravel validation errors
            $decoded = [[$e->getMessage()]];
        }

        // Laravel errors are formatted as {"field": [/*errors as strings*/]}
        $data['errors'] = array_reduce($decoded, function ($carry, $item) use ($e) {
            return array_merge($carry, array_map(function ($current) use ($e) {
                return [
                    'title' => 'Validation error.',
                    'detail' => $current
                ];
            }, $item));
        }, []);

        return response()->json($data, self::STATUS_CODE);
    }
}
