<?php

/**
 * Abstract Validator
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\ValidationHelperTrait;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Abstract Validator
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractValidator implements FactoryInterface, ValidatorInterface
{
    use ValidationHelperTrait;
}
