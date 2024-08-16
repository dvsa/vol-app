<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Is Anonymous User
 */
class IsAnonymousUser extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * Tests whether the user is an anonymous user
     *
     * @param CommandInterface|QueryInterface $dto DTO being validated
     *
     * @return bool
     */
    public function isValid($dto)
    {
        return $this->isAnonymousUser();
    }
}
