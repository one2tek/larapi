# Api Consumer

`Api Consumer` is a small class for making internal API requests.

# Usage Example

```php
private $apiConsumer;

public function __construct()
{
    $this->apiConsumer = app()->make('apiconsumer');
}

public function index()
{
    return $this->apiConsumer->post('/oauth/token', []);
}
```