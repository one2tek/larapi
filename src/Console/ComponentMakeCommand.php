<?php

namespace one2tek\larapi\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ComponentMakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'component:make {parent} {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * All filetypes we care about per namespace
     *
     * @var array
     */
    protected $fileTypes = [
        'Models' => [
            'model'
        ],
        'Controllers' => [
            'controller'
        ],
        'Services' => [
            'service'
        ],
        'Repositories' => [
            'repository'
        ],
        'Events' => [
            'WasCreated',
            'WasUpdated',
            'WasDeleted'
        ],
        'Exceptions' => [
            'NotFoundException'
        ]
    ];

    /**
     * Create a new command instance.
     *
     * @param Filesystem $files
     * @param Composer $composer
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->makeDirectory(base_path(). '/api/'. $this->argument('parent'));

        foreach ($this->fileTypes as $key => $fileType) {
            $this->makeSubDirectories(base_path(). '/api/'. $this->argument('parent'), $key);
           
            foreach ($fileType as $file) {
                $this->makeFile($key, $file);
            }
        }
    }

    /**
    * Make file.
    */
    protected function makeFile($dir, $fileName)
    {
        $stubFile = strtolower(rtrim($dir. '/'. $fileName, 's'));
        $name = $this->argument('name');
        $myFileName = $fileName;
        if ($myFileName == 'model') {
            $myFileName = '';
        }
        $filePath = base_path(). '/api/'. $this->argument('parent'). '/' . $dir. '/'. $name. ucfirst($myFileName). '.php';

        if (!$this->files->exists($filePath)) {
            $class = $this->buildFile($name, $stubFile, $dir. '/'. $fileName, $dir, $fileName);
            $this->files->put($filePath, $class);
        }
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeSubDirectories($path, $fileType)
    {
        if ($this->files->isDirectory($path)) {
            $this->makeDirectory($path.'/'.$fileType.'/');
        }
    }

    /**
     * Build file.
     */
    protected function buildFile($name, $type, $fileName, $dir, $shortFileName)
    {
        $stub = $this->files->get($this->getStub($type));
        return $this->replaceNamespace($stub, $fileName, $dir)->replaceClass($stub, $name, $fileName, $shortFileName);
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name, $fileName, $shortFileName)
    {
        $class = str_replace($this->getNamespace($name). '\\', '', $name);

        $stub = str_replace('DummyVariable', $class, $stub);
        $stub = str_replace('dummyVariable', lcfirst($class), $stub);
        $stub = str_replace('dummyvariable', strtolower($class), $stub);
        $stub = str_replace('DummyName', ucfirst($name), $stub);

        return str_replace('DummyClass', $this->argument('name'). ucfirst($shortFileName), $stub);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub($type)
    {
        return __DIR__. '/Stubs/'. $type. '.stub';
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceNamespace(&$stub, $fileName, $dir)
    {
        $stub = str_replace('DummyNamespace', 'Api\\'. $this->argument('parent'). '\\'. $dir, $stub);
        $stub = str_replace('DummyPath', 'Api\\'.$this->argument('parent'), $stub);

        return $this;
    }

    /**
     * Get the full namespace for a given class, without the class name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getNamespace($name)
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->fire();

        $this->info(
            "Routes: ". PHP_EOL.
            "$". "router->get('/". strtolower($this->argument('name')). "', '". ucfirst($this->argument('name')). "Controller@getAll');". PHP_EOL.
            "$". "router->get('/". strtolower($this->argument('name')). "/{id}', '". ucfirst($this->argument('name')). "Controller@getById');". PHP_EOL.
            "$". "router->post('/". strtolower($this->argument('name')). "', '". ucfirst($this->argument('name')). "Controller@create');". PHP_EOL.
            "$". "router->put('/". strtolower($this->argument('name')). "/{id}', '". ucfirst($this->argument('name')). "Controller@update');". PHP_EOL.
            "$". "router->delete('/". strtolower($this->argument('name')). "/{id}', '". ucfirst($this->argument('name')). "Controller@delete');". PHP_EOL
        );
    }
}
