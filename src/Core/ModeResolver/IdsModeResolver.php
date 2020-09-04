<?php

namespace one2tek\larapi\Core\ModeResolver;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use one2tek\larapi\Core\ModeResolver\ModeResolverInterface;
use one2tek\larapi\Core\Utility;

class IdsModeResolver implements ModeResolverInterface
{
    /**
     * Map through the collection and convert it to a collection
     * of ids
     * @param  string $property
     * @param  object $object
     * @param  array $root
     * @param  string $fullPropertyPath
     * @return mixed
     */
    public function resolve($property, &$object, &$root, $fullPropertyPath)
    {
        if (is_array($object)) {
            // We need to determine if this is a singular relationship or
            // a collection of models
            $arrayCopy = $object;
            $firstElement = array_shift($arrayCopy);

            // The object was not a collection, and was rather a single
            // model, because the first item returned was a property
            // We therefore just return the single ID
            if (Utility::isPrimitive($firstElement)) {
                return (int) Utility::getProperty($object, 'id');
            }

            return array_map(function ($entry) {
                return (int) Utility::getProperty($entry, 'id');
            }, $object);
        } elseif ($object instanceof Collection) {
            return $object->map(function ($entry) {
                return (int) Utility::getProperty($entry, 'id');
            });
        // The relation is not a collection, but rather
        // a singular relation
        } elseif ($object instanceof Model) {
            return $object->id;
        }
    }
}
