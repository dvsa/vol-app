<?php

namespace Dvsa\Olcs\Cli\Domain\Query\Util;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class GetDbValue
 *
 * @package Dvsa\Olcs\Cli\Domain\Query\Util
 */
class GetDbValue extends AbstractQuery
{
    /**
     * @var string
     */
    protected $propertyName;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var string
     */
    protected $filterProperty;

    /**
     * @var mixed
     */
    protected $filterValue;

    /**
     * @return string
     */
    public function getPropertyName(): ?string
    {
        return $this->propertyName;
    }

    /**
     * @return string
     */
    public function getEntityName(): ?string
    {
        return $this->entityName;
    }

    /**
     * @return string
     */
    public function getFilterProperty(): ?string
    {
        return $this->filterProperty;
    }

    /**
     * @return mixed
     */
    public function getFilterValue()
    {
        return $this->filterValue;
    }
}
