<?php

namespace Common\Service\Table\Formatter;

use Common\RefData;
use Laminas\View\HelperPluginManager;

class EbsrDocumentStatus implements FormatterPluginManagerInterface
{
    public function __construct(private HelperPluginManager $viewHelperManager)
    {
    }

    /**
     * Formats the status of an EBSR document
     *
     * @param array $data   data array
     * @param array $column column info
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        /**
         * @todo
         *
         * Once the EBSR status data has been cleansed, this can be simplified and moved to the
         * Common\View\Helper\Status helper
         */
        $status = match ($data['ebsrSubmissionStatus']['id']) {
            RefData::EBSR_STATUS_PROCESSING, RefData::EBSR_STATUS_VALIDATING, RefData::EBSR_STATUS_SUBMITTED => [
            'colour' => 'orange',
            'value' => 'Processing'
            ],
            RefData::EBSR_STATUS_PROCESSED => [
            'colour' => 'green',
            'value' => 'Successful'
            ],
            default => [
            'colour' => 'red',
            'value' => 'Failed'
            ],
        };

        /**
        * @var \Common\View\Helper\Status $statusHelper
        */
        $statusHelper = $this->viewHelperManager->get('status');

        return $statusHelper->__invoke($status);
    }
}
