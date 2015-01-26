<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class TransportManagers
 * @package Olcs\Filter\SubmissionSection
 */
class TransportManagers extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for transport-managers section
     * @param array $data
     * @return array
     */
    public function filter($data = array())
    {
        var_dump($data);exit;
        $dataToReturnArray = array();
        if (!empty($data['prohibitions'])) {
            foreach ($data['prohibitions'] as $prohibition) {
                $thisRow = array();
                $thisRow['id'] = $prohibition['id'];
                $thisRow['version'] = $prohibition['version'];
                $thisRow['prohibitionDate'] = $prohibition['prohibitionDate'];
                $thisRow['clearedDate'] = $prohibition['clearedDate'];
                $thisRow['vehicle'] = $prohibition['vrm'];
                $thisRow['trailer'] = $prohibition['isTrailer'];
                $thisRow['imposedAt'] = $prohibition['imposedAt'];
                $thisRow['prohibitionType'] = $prohibition['prohibitionType']['description'];
                $dataToReturnArray['tables']['prohibition-history'][] = $thisRow;
            }
        }
        $dataToReturnArray['text'] = isset($data['prohibitionNote']) ? $data['prohibitionNote'] : '';
        return $dataToReturnArray;
    }
}
