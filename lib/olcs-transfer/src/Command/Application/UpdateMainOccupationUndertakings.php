<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractIdWithVersionCommand;

/**
 * @Transfer\RouteName("backend/application/single/main-occupation-undertakings")
 * @Transfer\Method("PUT")
 */
final class UpdateMainOccupationUndertakings extends AbstractIdWithVersionCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $psvOccupationRecordsConfirmation;

    public function getPsvOccupationRecordsConfirmation(): ?string
    {
        return $this->psvOccupationRecordsConfirmation;
    }

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $psvIncomeRecordsConfirmation;

    public function getPsvIncomeRecordsConfirmation(): ?string
    {
        return $this->psvIncomeRecordsConfirmation;
    }
}
