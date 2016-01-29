<?php

/**
 * Stub Licence Controller
 */
namespace OlcsTest\Controller\Traits\Stub;

use Olcs\Controller\Traits\LicenceControllerTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Stub Licence Controller
 */
class StubLicenceController implements ServiceLocatorAwareInterface
{
    use LicenceControllerTrait,
        ServiceLocatorAwareTrait;
}
