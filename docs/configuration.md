## Configuration

> [Check how to use in real application.](https://github.com/gentritabazi01/Clean-Laravel-Api)

1. Extends `one2tek\larapi\Controllers\LaravelController` in your base controller. 

```php
<?php
use one2tek\larapi\Controllers\LaravelController; 

class Controller extends LaravelController {
    // ...
}
```

2. Extends `one2tek\larapi\Database\Repository` in your base repository.

```php
<?php
use one2tek\larapi\Database\Repository as BaseRepository; 

abstract class Repository extends BaseRepository {
    // ...
} 
```

3. Example Controller or Service for Users.

```php
<?php
use App\Http\Repositories\UserRepository; 

class UsersController extends Controller
{ 
    private $userRepository; 

    public function __construct(UserRepository $userRepository)
    {
        $this—>userRepository = $userRepository; 
    }

    public function getAll()
    {
        $resourceOptions = $this—>parseResourceOptions();

        $users = $this—>userRepository—>get($resourceOptions);
        
        return $this—>response($users);
    }
}
```

4. Example Repository for Users.

```php
<?php
class UserRepository extends Repository
{
    public function getModel()
    {
        return new User();
    }
}
```