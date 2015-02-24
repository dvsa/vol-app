<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class Oppositions
 * @package Olcs\Filter\SubmissionSection
 */
class Oppositions extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for oppositions section
     * @param array $data
     * @return array $dataToReturnArray
     */
    public function filter($data = array())
    {
        $dataToReturnArray = array();
        if (isset($data['oppositions']) && is_array($data['oppositions'])) {

            usort(
                $data['oppositions'],
                function ($a, $b) {
                    return strnatcmp($b['oppositionType']['description'], $a['oppositionType']['description']);
                }
            );
            usort(
                $data['oppositions'],
                function ($a, $b) {
                    return strtotime($b['raisedDate']) - strtotime($a['raisedDate']);
                }
            );

            foreach ($data['oppositions'] as $opposition) {
                $thisOpposition = array();
                $thisOpposition['id'] = $opposition['id'];
                $thisOpposition['version'] = $opposition['version'];
                $thisOpposition['dateReceived'] = $opposition['raisedDate'];
                $thisOpposition['oppositionType'] = $opposition['oppositionType']['description'];
                $thisOpposition['contactName']['forename'] =
                    $opposition['opposer']['contactDetails']['person']['forename'];
                $thisOpposition['contactName']['familyName'] =
                    $opposition['opposer']['contactDetails']['person']['familyName'];

                if (isset($opposition['grounds'])) {
                    foreach ($opposition['grounds'] as $ground) {
                        $thisOpposition['grounds'][] = $ground['description'];
                    }
                }

                $thisOpposition['isValid'] = $opposition['isValid'];
                $thisOpposition['isCopied'] = $opposition['isCopied'];
                $thisOpposition['isInTime'] = $opposition['isInTime'];
                $thisOpposition['isPublicInquiry'] = $opposition['isPublicInquiry'];
                $thisOpposition['isWithdrawn'] = $opposition['isWithdrawn'];

                $dataToReturnArray['tables']['oppositions'][] = $thisOpposition;
            }
        }

        return $dataToReturnArray;
    }
}
