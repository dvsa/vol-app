<?php

namespace Common\View\Helper;

use Doctrine\ORM\EntityNotFoundException;
use Laminas\View\Helper\AbstractHelper;

class SafeUser extends AbstractHelper
{
    public function __invoke($user, callable $resolve, string $fallback = 'Deleted user'): string
    {
        if ($user === null) {
            return $fallback;
        }

        try {
            return $resolve($user);
        } catch (EntityNotFoundException $e) {
            return $fallback;
        }
    }
}
