<?php

namespace Common\Service\Table\Formatter;

use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Laminas\View\HelperPluginManager;

class BusRegStatus implements FormatterPluginManagerInterface
{
    protected $viewHelperManager;

    public function __construct(protected TranslatorDelegator $translator, HelperPluginManager $viewHelperManager)
    {
        $this->viewHelperManager = $viewHelperManager;
    }

    /**
     * @param array $data   data array
     * @param array $column column info
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        //standardise the format of the data, so this can be used by multiple tables
        //we set the data even if the busReg key is blank
        if (array_key_exists('busReg', $data)) {
            $data = $data['busReg'];
        }

        /**
        * @var \Common\View\Helper\Status $statusHelper
        */
        $statusHelper = $this->viewHelperManager->get('status');

        //status field will be different, depending on whether the data has come from bus reg applications,
        //txc inbox or ebsr submission table
        if (isset($data['busRegStatus'])) {
            $statusId = $data['busRegStatus'];
            $statusDescription = $data['busRegStatusDesc'];
        } else {
            $statusId = $data['status']['id'];
            $statusDescription = $data['status']['description'];
        }

        $status = [
            'id' => $statusId,
            'description' => $this->translator->translate($statusDescription),
        ];

        return $statusHelper->__invoke($status);
    }
}
