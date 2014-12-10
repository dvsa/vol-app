<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class ProhibitionHistory
 * @package Olcs\Filter\SubmissionSection
 */
class ProhibitionHistory extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for prohibition-history section
     * @param array $data
     * @return array
     */
    public function filter($data = array())
    {
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
