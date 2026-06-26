<?php

namespace Common\Data\Mapper\Lva\TransportManager\Sections;

class ConvictionsPenalties extends AbstractSection implements TransportManagerSectionInterface
{
    use SectionSerializeTrait;

    private $convictions;

    /**
     * @return static
     */
    #[\Override]
    public function populate(array $transportManagerApplication)
    {
        $convictions = $transportManagerApplication['transportManager']['previousConvictions'];
        $template = 'markup-' . $this->getTranslationTemplate() . "answer-convictions";
        foreach ($convictions as $conviction) {
            $this->convictions .= $this->populateTemplate(
                $template,
                [
                    $conviction['categoryText'],
                    $conviction['convictionDate']
                ]
            );
        }

        if (empty($this->convictions)) {
            $this->convictions = "None Added";
        }
        return $this;
    }
}
