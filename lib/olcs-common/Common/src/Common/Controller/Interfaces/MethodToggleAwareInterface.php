<?php

namespace Common\Controller\Interfaces;

interface MethodToggleAwareInterface
{
    public function togglableMethod($class, $method, ...$args);
}
