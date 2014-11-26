<?php

namespace Olcs\Filter\SubmissionSection;

use Common\Exception\ResourceNotFoundException;
use Zend\Filter\AbstractFilter;

/**
 * Class Oppositions
 * @package Olcs\Filter\SubmissionSection
 */
class Oppositions extends AbstractFilter
{
    /**
     * @param \Zend\Stdlib\ArrayObject $data
     * @return \Zend\Stdlib\ArrayObject
     * @throws ResourceNotFoundException
     */
    public function filter($data = array())
    {
        $dataToReturnArray = array();
        if (isset($data['application']['oppositions']) && is_array($data['application']['oppositions'])) {

            usort(
                $data['application']['oppositions'],
                function ($a, $b) {
                    return strnatcmp($b['oppositionType']['description'], $a['oppositionType']['description']);
                }
            );
            usort(
                $data['application']['oppositions'],
                function ($a, $b) {
                    return strtotime($b['raisedDate']) - strtotime($a['raisedDate']);
                }
            );

            foreach ($data['application']['oppositions'] as $opposition) {
                $thisOpposition = array();
                $thisOpposition['id'] = $opposition['id'];
                $thisOpposition['version'] = $opposition['version'];
                $thisOpposition['dateReceived'] = $opposition['raisedDate'];
                $thisOpposition['oppositionType'] = $opposition['oppositionType']['description'];
                $thisOpposition['contactName']['forename'] =
                    $opposition['opposer']['contactDetails']['person']['forename'];
                $thisOpposition['contactName']['familyName'] =
                    $opposition['opposer']['contactDetails']['person']['familyName'];

                foreach ($opposition['grounds'] as $ground) {
                    $thisOpposition['grounds'][] = $ground['grounds']['description'];
                }

                $thisOpposition['isValid'] = $opposition['isValid'];
                $thisOpposition['isCopied'] = $opposition['isCopied'];
                $thisOpposition['isInTime'] = $opposition['isInTime'];
                $thisOpposition['isPublicInquiry'] = $opposition['isPublicInquiry'];
                $thisOpposition['isWithdrawn'] = $opposition['isWithdrawn'];

                $dataToReturnArray[] = $thisOpposition;
            }
        }

        return $dataToReturnArray;
    }
}
