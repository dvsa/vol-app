<?php

namespace Olcs\Mvc\Controller\ParameterProvider;

/**
 * Class PreviousPiHearingData
 * @package Olcs\Mvc\Controller\ParameterProvider
 */
class PreviousPiHearingData extends AbstractParameterProvider
{
    private $pi;

    public function __construct($pi)
    {
        $this->pi = (array) $pi;
    }

    public function provideParameters()
    {
        $params = ['pi' => $this->pi['id']];

        if (!empty($this->pi['piHearings'])) {
            $lastHearing = end($this->pi['piHearings']);

            //if the venue other field is filled in, override venue id
            if ($lastHearing['venueOther'] != '') {
                $lastHearing['venue']['id'] = 'other';
            }

            $populateFields = [
                'venue' => $lastHearing['venue']['id'],
                'venueOther' => $lastHearing['venueOther'],
                'presidingTc' => $lastHearing['presidingTc']['id'],
                'presidedByRole' => $lastHearing['presidedByRole']['id'],
                'witnesses' => $lastHearing['witnesses'],
                'details' => $lastHearing['details']
            ];

            $params = array_merge($populateFields, $params);
        }

        return $params;
    }
}
