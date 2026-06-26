<?php

namespace Common\Data\Mapper\Lva\TransportManager\Sections;

/**
 * Class OtherLicences
 *
 * @package Common\Data\Mapper\Lva\TransportManager\Sections
 */
class OtherLicences extends AbstractSection implements TransportManagerSectionInterface
{
    use SectionSerializeTrait;

    private $licences;

    /**
     * @return static
     */
    #[\Override]
    public function populate(array $transportManagerApplication)
    {
        $licences = $transportManagerApplication['otherLicences'];
        if (empty($licences)) {
            $this->licences = 'None Added';
            return $this;
        }

        $licences = $this->sortByCreated($licences);

        foreach ($licences as $licence) {
            $template = 'markup-' . $this->getTranslationTemplate() . "answer-otherLicences";
            $this->licences .= $this->populateTemplate($template, [$licence['licNo']]);
        }

        return $this;
    }
}
