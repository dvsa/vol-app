<?php

namespace Olcs\Service\Marker\TransportManager;

/**
 * SiQualificationMarker
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class SiQualificationMarker extends \Olcs\Service\Marker\AbstractMarker
{
    public function canRender()
    {
        $data = $this->getData();

        if (!empty($this->getTransportManagerRequiringGbMarkers($data))) {
            return true;
        }

        if (!empty($this->getTransportManagerRequiringNiMarkers($data))) {
            return true;
        }

        return false;
    }

    public function render()
    {
        $data = $this->getData();
        $html = '';

        $gbTms = $this->getTransportManagerRequiringGbMarkers($data);
        foreach ($gbTms as $tm) {
            $html .= $this->renderPartial(
                'transport-manager/si-gb-qualification',
                [
                    'person' => $tm['homeCd']['person'],
                    'niFlag' => false,
                    // if both are set then must be on transportManager section
                    'hideName' => ($data['page'] === 'transportManager')
                ]
            );
        }

        $niTms = $this->getTransportManagerRequiringNiMarkers($data);
        foreach ($niTms as $tm) {
            $html .= $this->renderPartial(
                'transport-manager/si-gb-qualification',
                [
                    'person' => $tm['homeCd']['person'],
                    'niFlag' => true,
                    // if both are set then must be on transportManager section
                    'hideName' => ($data['page'] === 'transportManager')
                ]
            );
        }
        return $html;
    }

    /**
     * Get a list of TMs that require the GB SI Qualification and dont have it
     *
     * @param array $data
     *
     * @return array
     */
    protected function getTransportManagerRequiringGbMarkers($data)
    {
        $mergedTmasTmls = $this->getMergeTmaAndTml($data);

        $tms = [];
        foreach ($mergedTmasTmls as $tmaOrTml) {
            if (
                $tmaOrTml['transportManager']['requireSiGbQualification'] &&
                !$tmaOrTml['transportManager']['hasValidSiGbQualification']
            ) {
                // add and eliminate duplicates
                $tms[$tmaOrTml['transportManager']['id']] = $tmaOrTml['transportManager'];
            }
        }
        if (isset($data['transportManagersFromLicence'])) {
            foreach ($data['transportManagersFromLicence'] as $tml) {
                if (
                    $tml['transportManager']['requireSiGbQualificationOnVariation'] &&
                    !$tml['transportManager']['hasValidSiGbQualification']
                ) {
                    // add and eliminate duplicates
                    $tms[$tml['transportManager']['id']] = $tml['transportManager'];
                }
            }
        }

        return $tms;
    }

    /**
     * Get a list of TMs that require the NI SI Qualification and dont have it
     *
     * @param array $data
     *
     * @return array
     */
    protected function getTransportManagerRequiringNiMarkers($data)
    {
        $mergedTmasTmls = $this->getMergeTmaAndTml($data);

        $tms = [];
        foreach ($mergedTmasTmls as $tmaOrTml) {
            if (
                $tmaOrTml['transportManager']['requireSiNiQualification'] &&
                !$tmaOrTml['transportManager']['hasValidSiNiQualification']
            ) {
                // add and eliminate duplicates
                $tms[$tmaOrTml['transportManager']['id']] = $tmaOrTml['transportManager'];
            }
        }
        if (isset($data['transportManagersFromLicence'])) {
            foreach ($data['transportManagersFromLicence'] as $tml) {
                if (
                    $tml['transportManager']['requireSiNiQualificationOnVariation'] &&
                    !$tml['transportManager']['hasValidSiNiQualification']
                ) {
                    // add and eliminate duplicates
                    $tms[$tml['transportManager']['id']] = $tml['transportManager'];
                }
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
