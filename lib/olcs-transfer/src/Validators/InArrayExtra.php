<?php

namespace Dvsa\Olcs\Transfer\Validators;

/**
 * @author Dmitry Golubev <d.e.golubev@gmail.com>
 */
abstract class InArrayExtra extends \Laminas\Validator\InArray
{
    /** @var  array */
    protected $extraHaystack;

    /**
     * Returns the haystack option
     *
     * @return array
     */
    #[\Override]
    public function getHaystack()
    {
        return array_unique(
            array_merge(parent::getHaystack(), $this->getExtraHaystack())
        );
    }

    /**
     * Set additional items to haystack
     *
     * @param array $items Extra items to haystack
     *
     * @return $this
     */
    public function setExtraHaystack(array $items)
    {
        $this->extraHaystack = $items;
        return $this;
    }

    /**
     * Get Extra Haystack
     *
     * @return array
     */
    public function getExtraHaystack()
    {
        return (array)$this->extraHaystack;
    }
}
