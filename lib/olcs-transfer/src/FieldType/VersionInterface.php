<?php

namespace Dvsa\Olcs\Transfer\FieldType;

/**
 * Interface Version
 *
 * @package Dvsa\Olcs\Transfer\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
interface VersionInterface
{
    /**
     * @return int
     */
    public function getVersion();
}
