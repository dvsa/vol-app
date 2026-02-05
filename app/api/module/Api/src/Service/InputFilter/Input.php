<?php

namespace Dvsa\Olcs\Api\Service\InputFilter;

use Laminas\InputFilter\Input as LaminasInput;

/**
 * Class Input
 * @package Dvsa\Olcs\Api\Service\InputFilter
 */
class Input extends LaminasInput
{
    /**
     * @var mixed
     */
    protected $filteredValue;
    /**
     * @var bool
     */
    protected $hasFiltered = false;

    /**
     * get value
     *
     * @return mixed
     */
    #[\Override]
    public function getValue()
    {
        if (!$this->hasFiltered) {
            $this->filteredValue = $this->getFilterChain()->filter($this->value);
            $this->hasFiltered = true;
        }
        return $this->filteredValue;
    }

    /**
     * set value (sets hasFiltered to false, then calls parent setValue() method)
     *
     * @param mixed $value value being set
     *
     * @return Input
     */
    #[\Override]
    public function setValue($value)
    {
        $this->hasFiltered = false;
        return parent::setValue($value);
    }
}
