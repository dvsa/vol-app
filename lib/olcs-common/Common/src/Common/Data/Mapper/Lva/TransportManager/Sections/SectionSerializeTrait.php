<?php

namespace Common\Data\Mapper\Lva\TransportManager\Sections;

trait SectionSerializeTrait
{
    /**
     * sectionSerialize
     * Method to prefix property values so these become translation strings in labels
     * @return array
     */
    public function sectionSerialize()
    {
        $templatePrefix = $this->getTranslationTemplate();
        return array_combine(
            array_map(
                static fn($k) => $templatePrefix . $k,
                array_keys(get_object_vars($this))
            ),
            get_object_vars($this)
        );
    }
}
