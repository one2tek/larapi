<?php

namespace Gentritabazi01\LarapiComponents\Core\Architect\ModeResolver;

use Gentritabazi01\LarapiComponents\Core\Architect\ModeResolver\ModeResolverInterface;

class EmbedModeResolver implements ModeResolverInterface
{
    /**
     * Simply returns the object since embedded is the default
     * transformation
     * @param  string  $property
     * @param  object  $object
     * @param  array  $root
     * @param  string  $fullPropertyPath
     * @return object
     */
    public function resolve($property, &$object, &$root, $fullPropertyPath)
    {
        return $object;
    }
}
