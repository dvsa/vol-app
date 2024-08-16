<?php

namespace Dvsa\Olcs\Api\Domain\Command\SystemParameter;

use Dvsa\Olcs\Api\Domain\Command\AbstractIdOnlyCommand;

/**
 * Update a SystemParameter
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Update extends AbstractIdOnlyCommand
{
    protected $paramValue;

    /**
     * @return mixed
     */
    public function getParamValue()
    {
        return $this->paramValue;
    }
}
