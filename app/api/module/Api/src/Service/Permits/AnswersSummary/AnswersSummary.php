<?php

namespace Dvsa\Olcs\Api\Service\Permits\AnswersSummary;

class AnswersSummary
{
    /** @var array */
    private $rows = [];

    /**
     * Get the representation of this class to be returned by the API endpoint
     *
     * @return array
     */
    public function getRepresentation()
    {
        $rowsRepresentation = [];
        foreach ($this->rows as $row) {
            $rowsRepresentation[] = $row->getRepresentation();
        }

        return ['rows' => $rowsRepresentation];
    }

    /**
     * Add a AnswersSummaryRow instance representing a row in the answers summary
     */
    public function addRow(AnswersSummaryRow $row)
    {
        $this->rows[] = $row;
    }
}
