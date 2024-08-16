<?php

/**
 * Abstract Handler
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers;

use Dvsa\Olcs\Api\Domain\Validation\ValidationHelperTrait;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Abstract Handler
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractHandler implements HandlerInterface, FactoryInterface
{
    use ValidationHelperTrait;

    /**
     * @inheritdoc
     */
    abstract public function isValid($dto);
}
