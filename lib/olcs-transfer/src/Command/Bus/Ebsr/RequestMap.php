<?php

namespace Dvsa\Olcs\Transfer\Command\Bus\Ebsr;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * RequestMap
 *
 * @Transfer\RouteName("backend/bus/single/request-map")
 * @Transfer\Method("POST")
 */
class RequestMap extends AbstractCommand
{
    use Identity;

    /**
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"small","large","auto"}})
     */
    protected ?string $scale = null;

    public function getScale(): string
    {
        return $this->scale;
    }
}
