<?php

namespace Common\Data\Mapper\Lva\TransportManager\Sections;

class OtherEmployment extends AbstractSection implements TransportManagerSectionInterface
{
    use SectionSerializeTrait;

    private $employments;

    /**
     * @return static
     */
    #[\Override]
    public function populate(array $transportManagerApplication)
    {
        $employments = $transportManagerApplication['transportManager']['employments'];
        $employments = $this->sortByCreated($employments);

        $noOfPreviousRoles = count($employments);

        for ($x = 0; ($x < $noOfPreviousRoles) && ($x < 3); ++$x) {
            $template = 'markup-' . $this->getTranslationTemplate() . "answer-otherEmployments";
            $this->employments .= $this->populateTemplate($template, [$employments[$x]['employerName']]);
        }

        $this->formatSuffix($noOfPreviousRoles);

        $this->employments = $noOfPreviousRoles > 0 ? $this->employments : 'None Added';
        return $this;
    }

    private function formatSuffix(int $noOfPreviousRoles): void
    {
        $suffix = '';
        if ($noOfPreviousRoles > 3) {
            $template = 'markup-' . $this->getTranslationTemplate() . "answer-otherEmployments-more";
            $suffix = $this->populateTemplate($template, [$noOfPreviousRoles - 3]);
        }

        $this->employments .= $suffix;
    }
}
