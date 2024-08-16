<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options;

use RuntimeException;

class OptionListGenerator
{
    /** @var array */
    private $sources = [];

    /**
     * Create service instance
     *
     *
     * @return OptionListGenerator
     */
    public function __construct(private readonly OptionListFactory $optionListFactory, private readonly OptionFactory $optionFactory)
    {
    }

    /**
     * Get the converted representation of the specified options to be returned by the API endpoint
     *
     * @return array
     *
     * @throws RuntimeException
     */
    public function generate(array $data)
    {
        $sourceName = $data['name'];

        if (!isset($this->sources[$sourceName])) {
            throw new RuntimeException('No source found for name ' . $sourceName);
        }

        $optionList = $this->optionListFactory->create($this->optionFactory);
        $this->sources[$sourceName]->populateOptionList($optionList, $data['options']);

        return $optionList;
    }

    /**
     * Registers an implementation of SourceInterface corresponding to the provided name
     *
     * @param string $name
     */
    public function registerSource($name, SourceInterface $source)
    {
        $this->sources[$name] = $source;
    }
}
