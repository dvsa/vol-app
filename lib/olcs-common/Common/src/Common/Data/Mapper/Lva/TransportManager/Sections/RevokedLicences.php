<?php

namespace Common\Data\Mapper\Lva\TransportManager\Sections;

class RevokedLicences extends AbstractSection implements TransportManagerSectionInterface
{
    use SectionSerializeTrait;

    private $revokedLicences;

    /**
     * @return static
     */
    #[\Override]
    public function populate(array $transportManagerApplication)
    {

        $revokedLicences = $transportManagerApplication['transportManager']['otherLicences'];

        if (empty($revokedLicences)) {
            $this->revokedLicences = 'None Added';
            return $this;
        }

        $licences = $this->sortByCreated($revokedLicences);

        foreach ($licences as $licence) {
            $template = 'markup-' . $this->getTranslationTemplate() . "answer-revokedLicences";
            $this->revokedLicences .= $this->populateTemplate($template, [$licence['licNo']]);
        }

        return $this;
    }
}
