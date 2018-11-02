<?php

namespace Olcs\Controller\Lva\Traits;

use Common\Controller\Interfaces\MethodToggleAwareInterface;
use Common\Service\Cqrs\Query\QuerySender;
use Common\Controller\Plugin\FeaturesEnabledForMethod as FeaturesEnabledForMethodPlugin;
use Zend\Mvc\MvcEvent;

/**
 * @method FeaturesEnabledForMethodPlugin featuresEnabledForMethod(array $toggleConfig, $method)
 */
trait MethodToggleTrait
{
    public function togglableMethod($class, $method, ...$args)
    {
        if ($this->featuresEnabledForMethod($this->methodToggles, $method)){
            call_user_func_array([$class, $method], $args);
        }
    }
}
