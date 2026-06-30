<?php

namespace Common\Controller\Lva\Traits;

use Common\Controller\Plugin\FeaturesEnabledForMethod as FeaturesEnabledForMethodPlugin;

/**
 * @method FeaturesEnabledForMethodPlugin featuresEnabledForMethod(array $toggleConfig, $method)
 */
trait MethodToggleTrait
{
    public function togglableMethod($class, $method, ...$args): void
    {
        if ($this->featuresEnabledForMethod($this->methodToggles, $method)) {
            call_user_func_array([$class, $method], $args);
        }
    }
}
