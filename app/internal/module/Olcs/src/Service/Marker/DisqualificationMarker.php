<?php

namespace Olcs\Service\Marker;

/**
 * DisqualificationMarker
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DisqualificationMarker extends AbstractMarker
{
    public function canRender()
    {
        $data = $this->getData();

        if (!isset($data['organisation']['disqualifications'][0])) {
            return false;
        }

        return true;
    }

    public function render()
    {
        $data = $this->getData();
        // its only possible to have one disqualification record
        $disqualification = $data['organisation']['disqualifications'][0];

        $startDateTime = new \DateTime($disqualification['startDate']);
        $endDateTime = ($disqualification['endDate']) ? new \DateTime($disqualification['endDate']) : null;

        return $this->renderPartial(
            'disqualification',
            [
                'startDateTime' => $startDateTime,
                'endDateTime' => $endDateTime,
                'active' => strtolower((string) $disqualification['status']) == 'active',
                'organisationId' => $data['organisation']['id'],
            ]
        );
    }
}
