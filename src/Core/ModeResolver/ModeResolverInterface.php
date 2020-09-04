<?php

namespace Gentritabazi01\LarapiComponents\Core\ModeResolver;

interface ModeResolverInterface
{
    public function resolve($property, &$object, &$root, $fullPropertyPath);
}
