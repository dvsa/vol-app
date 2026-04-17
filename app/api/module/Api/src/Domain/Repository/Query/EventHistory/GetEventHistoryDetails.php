<?php

/**
 * Get event history details
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository\Query\EventHistory;

use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;

/**
 * Get event history details
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GetEventHistoryDetails extends AbstractRawQuery
{
    protected $templateMap = null;

    protected $queryTemplate = 'SELECT * FROM {historyTable} WHERE id = :id AND version IN (:version) ' .
        'ORDER BY version DESC LIMIT 2';

    protected $historyTable = null;

    /**
     * Set history table
     *
     * @param string $historyTable
     */
    public function setHistoryTable($historyTable)
    {
        $this->historyTable = $historyTable;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    protected function getQueryTemplate()
    {
        return str_replace('{historyTable}', $this->historyTable, parent::getQueryTemplate());
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    protected function getParamTypes()
    {
        return [
            'version' => \Doctrine\DBAL\Connection::PARAM_INT_ARRAY
        ];
    }
}
