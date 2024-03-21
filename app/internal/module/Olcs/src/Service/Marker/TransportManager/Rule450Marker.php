<?php

namespace Olcs\Service\Marker\TransportManager;

/**
 * Rule50Marker
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Rule450Marker extends \Olcs\Service\Marker\AbstractMarker
{
    public function canRender()
    {
        $data = $this->getData();

        return count($this->getTransportManagerRequiringMarkers($data)) > 0;
    }

    public function render()
    {
        $data = $this->getData();
        $tms = $this->getTransportManagerRequiringMarkers($data);

        $html = '';
        foreach ($tms as $tm) {
            $html .= $this->renderPartial(
                'transport-manager/rule450',
                [
                    'person' => $tm['homeCd']['person'],
                    'associatedOrganisationCount' => $tm['associatedOrganisationCount'],
                    'associatedTotalAuthVehicles' => $tm['associatedTotalAuthVehicles'],
                    // if both are set then must be on transportManager section
                    'hideName' => isset($data['transportManagerLicences']) &&
                        isset($data['transportManagerApplications'])
                ]
            );
        }

        return $html;
    }

    protected function getTransportManagerRequiringMarkers($data)
    {
        $mergedTmasTmls = $this->getMergeTmaAndTml($data);

        $tms = [];
        foreach ($mergedTmasTmls as $tmaOrTml) {
            //  they are a TM type of 'External' or 'Both'
            if (
                $tmaOrTml['transportManager']['tmType']['id'] !== \Common\RefData::TRANSPORT_MANAGER_TYPE_BOTH &&
                $tmaOrTml['transportManager']['tmType']['id'] !== \Common\RefData::TRANSPORT_MANAGER_TYPE_EXTERNAL
            ) {
                continue;
            }

            if (
                $tmaOrTml['transportManager']['associatedOrganisationCount'] > 4 ||
                $tmaOrTml['transportManager']['associatedTotalAuthVehicles'] > 50
            ) {
                // add and eliminate duplicates
                $tms[$tmaOrTml['transportManager']['id']] = $tmaOrTml['transportManager'];
            }
        }

        return $tms;
    }


    /**
     * Merge together the TMAs and TMLs
     *
     * @param array $data
     *
     * @return array
     */
    protected function getMergeTmaAndTml($data)
    {
        // get all the TMAs that need a marker creating
        $tmas = (isset($data['transportManagerApplications'])) ?
            $tmas = $data['transportManagerApplications'] :
            [];

        // get all the TMLs that need a marker creating
        $tmls = (isset($data['transportManagerLicences'])) ?
            $tmls = $data['transportManagerLicences'] :
            [];

        $merged = array_merge($tmas, $tmls);

        return $merged;
    }
}
