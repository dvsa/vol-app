<?php

namespace Olcs\Service\Marker;

/**
 * SoleTraderDisqualificationMarker
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class SoleTraderDisqualificationMarker extends AbstractMarker
{
    public function canRender()
    {
        $data = $this->getData();

        if (
            !isset($data['organisation']['type']) ||
            $data['organisation']['type']['id'] !== \Common\RefData::ORG_TYPE_SOLE_TRADER
        ) {
            return false;
        }
        if (!isset($data['organisation']['organisationPersons'][0]['person'])) {
            return false;
        }
        $person = $data['organisation']['organisationPersons'][0]['person'];
        if (!isset($person['disqualifications'])) {
            return false;
        }
        if (count($person['disqualifications']) === 0) {
            return false;
        }

        return true;
    }

    public function render()
    {
        $data = $this->getData();

        $person = $data['organisation']['organisationPersons'][0]['person'];

        // its only possible to have one disqualification record
        $disqualification = $person['disqualifications'][0];

        $startDateTime = new \DateTime($disqualification['startDate']);
        $endDateTime = ($disqualification['endDate']) ? new \DateTime($disqualification['endDate']) : null;

        return $this->renderPartial(
            'soletrader-disqualification',
            [
                'startDateTime' => $startDateTime,
                'endDateTime' => $endDateTime,
                'active' => strtolower($disqualification['status']) == 'active',
                'organisationId' => $data['organisation']['id'],
                'personId' => $person['id'],
            ]
        );
    }
}
