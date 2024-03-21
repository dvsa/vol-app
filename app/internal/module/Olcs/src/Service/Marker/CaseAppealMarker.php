<?php

namespace Olcs\Service\Marker;

/**
 * CaseAppealMarker
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CaseAppealMarker extends AbstractMarker
{
    public function canRender()
    {
        $data = $this->getData();

        return isset($data['cases']) && count($this->getCasesWithAppeals($data)) > 0;
    }

    public function render()
    {
        $data = $this->getData();

        $html = '';
        foreach ($this->getCasesWithAppeals($data) as $case) {
            $html .= $this->renderPartial(
                'case-appeal',
                [
                    'caseId' => $case['id'],
                    'appealDate' => new \DateTime($case['appeal']['appealDate']),
                    'hideCaseLink' => isset($data['configCase']['hideLink']),
                ]
            );
        }

        return $html;
    }

    private function getCasesWithAppeals($data)
    {
        $casesWithAppeals = [];
        foreach ($data['cases'] as $case) {
            if (empty($case['appeal']) || !empty($case['closedDate'])) {
                continue;
            }

            if (
                empty($case['appeal']['withdrawnDate']) &&
                (empty($case['appeal']['decisionDate']) || empty($case['appeal']['outcome']))
            ) {
                $casesWithAppeals[] = $case;
            }
        }

        return $casesWithAppeals;
    }
}
