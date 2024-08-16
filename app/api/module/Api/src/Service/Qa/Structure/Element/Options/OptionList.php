<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options;

class OptionList
{
    /** @var array */
    private $options = [];

    /**
     * Create instance
     *
     *
     * @return OptionList
     */
    public function __construct(private readonly OptionFactory $optionFactory)
    {
    }

    /**
     * Add an option to the list
     *
     * @param string $value
     * @param string $label
     * @param string|null $hint
     */
    public function add($value, $label, $hint = null)
    {
        $this->options[] = $this->optionFactory->create($value, $label, $hint);
    }

    /**
     * Get the representation of this element to be returned by the API endpoint
     *
     * @return array
     */
    public function getRepresentation()
    {
        $representation = [];

        foreach ($this->options as $option) {
            $representation[] = $option->getRepresentation();
        }

        return $representation;
    }

    /**
     * Get an array of the embedded Option instances
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
