<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

class FilteredTranslateableText
{
    /**
     * Create instance
     *
     * @param string $filter
     *
     * @return FilteredTranslateableText
     */
    public function __construct(private $filter, private readonly TranslateableText $translateableText)
    {
    }

    /**
     * Get the representation of this class to be returned by the API endpoint
     *
     * @return array
     */
    public function getRepresentation()
    {
        return [
            'filter' => $this->filter,
            'translateableText' => $this->translateableText->getRepresentation()
        ];
    }

    /**
     * Get the embedded TranslateableText instance
     *
     * @return TranslateableText
     */
    public function getTranslateableText()
    {
        return $this->translateableText;
    }
}
