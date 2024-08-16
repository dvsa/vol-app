<?php

namespace Olcs\Service\Marker;

/**
 * CaseStayMarker
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CaseStayMarker extends AbstractMarker
{
    public function canRender()
    {
        $data = $this->getData();

        if (!isset($data['cases'])) {
            return false;
        }

        return count($this->getCasesWithStays($data)) > 0;
    }

    public function render()
    {
        $data = $this->getData();

        $html = '';
        foreach ($this->getCasesWithStays($data) as $case) {
            foreach ($case['stays'] as $stay) {
                if (empty($stay['withdrawnDate'])) {
                    $html .= $this->renderPartial(
                        'case-stay',
                        [
                            'caseId' => $case['id'],
                            'stay' => $stay,
                            'hideCaseLink' => isset($data['configCase']['hideLink']),
                        ]
                    );
                }
            }
        }

        return $html;
    }

    private function getCasesWithStays($data)
    {
        $casesWithStays = [];

        foreach ($data['cases'] as $case) {
            if (
                empty($case['appeal']) ||
                (!empty($case['appeal']['decisionDate']) && !empty($case['appeal']['outcome'])) ||
                !empty($case['appeal']['withdrawnDate'])
            ) {
                continue;
            }
            if (empty($case['stays'])) {
                continue;
            }

            foreach ($case['stays'] as $stay) {
                if (empty($stay['withdrawnDate'])) {
                    $casesWithStays[] = $case;
                    continue 2;
                }
            }
        }

        return $casesWithStays;
    }
}
