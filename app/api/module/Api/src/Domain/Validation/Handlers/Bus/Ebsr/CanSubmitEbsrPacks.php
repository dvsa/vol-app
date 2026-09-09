<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Can the current user submit EBSR packs
 *
 * Being an operator is not enough: the organisation must also hold a licence able to accept EBSR
 * submissions. Without this an operator with no PSV licence at all can queue packs, which are then
 * only rejected asynchronously once processing reaches
 * Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\Licence.
 */
class CanSubmitEbsrPacks extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * Validate DTO
     *
     * @param CommandInterface|QueryInterface $dto dto being validated
     *
     * @return bool
     */
    #[\Override]
    public function isValid($dto)
    {
        if (!$this->isOperator()) {
            return false;
        }

        $organisation = $this->getCurrentOrganisation();

        return $organisation instanceof Organisation && $organisation->hasEbsrEligibleLicence();
    }
}
