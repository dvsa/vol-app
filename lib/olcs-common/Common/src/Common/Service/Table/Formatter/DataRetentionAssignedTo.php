<?php

namespace Common\Service\Table\Formatter;

use Laminas\View\HelperPluginManager;

/**
 * Data Retention Assigned To
 */
class DataRetentionAssignedTo implements FormatterPluginManagerInterface
{
    public function __construct(private HelperPluginManager $viewHelperManager)
    {
    }

    /**
     * Format column value
     *
     * @param array $data   Row data
     * @param array $column Column Parameters
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if (isset($data['assignedTo']['contactDetails']['person'])) {
            /**
             * @var \Common\View\Helper\PersonName $personName
             */
            $personName = $this->viewHelperManager->get('personName');

            return $personName->__invoke(
                $data['assignedTo']['contactDetails']['person'],
                [
                    'forename',
                    'familyName'
                ]
            );
        }

        return '';
    }
}
