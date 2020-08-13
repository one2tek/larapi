<?php

namespace Gentritabazi01\LarapiComponents\Core\Architect\ModeResolver;

interface ModeResolverInterface
{
    public function resolve($property, &$object, &$root, $fullPropertyPath);
}
