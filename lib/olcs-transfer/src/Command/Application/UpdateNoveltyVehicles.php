<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\AbstractIdWithVersionCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/application/single/novelty-vehicles")
 * @Transfer\Method("PUT")
 */
final class UpdateNoveltyVehicles extends AbstractIdWithVersionCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $psvLimousines;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     * @Transfer\Optional
     */
    protected $psvNoLimousineConfirmation;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     * @Transfer\Optional
     */
    protected $psvOnlyLimousinesConfirmation;

    public function getPsvLimousines(): ?string
    {
        return $this->psvLimousines;
    }

    public function getPsvNoLimousineConfirmation(): ?string
    {
        return $this->psvNoLimousineConfirmation;
    }

    public function getPsvOnlyLimousinesConfirmation(): ?string
    {
        return $this->psvOnlyLimousinesConfirmation;
    }
}
