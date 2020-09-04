<?php

namespace one2tek\larapi\Core\ModeResolver;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Collection;
use one2tek\larapi\Core\ModeResolver\IdsModeResolver;
use one2tek\larapi\Core\ModeResolver\ModeResolverInterface;
use one2tek\larapi\Core\Utility;

class SideloadModeResolver implements ModeResolverInterface
{
    private $idsResolver;

    public function __construct()
    {
        $this->idsResolver = new IdsModeResolver;
    }

    /**
     * Move all relational resources to the root element and
     * use idsResolver to replace them with a collection of identifiers
     * @param  string $property The property to resolve
     * @param  object $object The object which has the property
     * @param  array $root The root array which will contain the object
     * @param  string $fullPropertyPath The full dotnotation path to this property
     * @return mixed
     */
    public function resolve($property, &$object, &$root, $fullPropertyPath)
    {
        $this->addCollectionToRoot($root, $object, $fullPropertyPath);

        return $this->idsResolver->resolve($property, $object, $root, $fullPropertyPath);
    }

    /**
     * Add the collection to the root array
     * @param array $root
     * @param object $object
     * @param string $fullPropertyPath
     * @return void
     */
    private function addCollectionToRoot(&$root, &$object, $fullPropertyPath)
    {
        // First determine if the $object is a resource or a
        // collection of resources
        $isResource = false;
        if (is_array($object)) {
            $copy = $object;
            $values = array_values($copy);
            $firstPropertyOrResource = array_shift($values);

            if (Utility::isPrimitive($firstPropertyOrResource)) {
                $isResource = true;
            }
        } elseif ($object instanceof EloquentModel) {
            $isResource = true;
        }

        $newCollection = $isResource ? [$object] : $object;

        // Does existing collections use arrays or Collections
        $copy = $root;
        $values = array_values($copy);
        $existingRootCollection = array_shift($values);

        $newCollection = $existingRootCollection instanceof Collection ?
                                new Collection($newCollection) : $newCollection;

        if (!array_key_exists($fullPropertyPath, $root)) {
            $root[$fullPropertyPath] = $newCollection;
        } else {
            $this->mergeRootCollection($root[$fullPropertyPath], $newCollection);
        }
    }

    /**
     * If a collection for this resource has already begun (i.e. multiple
     * resources share this type of resource), then merge with the existing collection
     * @param  mixed $collection
     * @param  object $object
     * @return void
     */
    private function mergeRootCollection(&$collection, $object)
    {
        if (is_array($object)) {
            foreach ($object as $resource) {
                $this->addResourceToRootCollectionIfNonExistant($collection, $resource);
            }
        } elseif ($object instanceof Collection) {
            $object->each(function ($resource) use (&$collection) {
                $this->addResourceToRootCollectionIfNonExistant($collection, $resource);
            });
        }
    }

    /**
     * Check if the resource already exists in the root collection by id
     * TODO: https://github.com/esbenp/laravel-controller/issues/2
     * @param mixed $collection
     * @param mixed $resource
     */
    private function addResourceToRootCollectionIfNonExistant(&$collection, $resource)
    {
        $identifier = Utility::getProperty($resource, 'id');
        $exists = false;

        $copy = $collection instanceof Collection ? $collection->toArray() : $collection;

        foreach ($copy as $rootResource) {
            if ((int) Utility::getProperty($rootResource, 'id') === (int) $identifier) {
                $exists = true;
                break;
            }
        }

        if ($exists === false) {
            if (is_array($collection)) {
                $collection[] = $resource;
            } elseif ($collection instanceof Collection) {
                $collection->push($resource);
            }
        }
    }
}
