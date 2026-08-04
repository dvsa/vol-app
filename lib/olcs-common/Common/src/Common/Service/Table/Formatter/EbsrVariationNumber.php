<?php

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Laminas\View\HelperPluginManager;

/**
 * EBSR variation number
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class EbsrVariationNumber implements FormatterPluginManagerInterface
{
    public const SN_TRANSLATION_KEY = 'ebsr-variation-short-notice';

    public function __construct(private HelperPluginManager $viewHelperManager, private TranslatorDelegator $translator)
    {
    }

    /**
     * Formats the ebsr variation number
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
         * far from ideal, but we sometimes get data in different formats as follows:
         *
         * 1. if it's from BusRegSearchView entity it's a flat array
         * 2. if it's from anywhere else, it's in the $data['busReg'] array key
         */
        if (isset($data['busReg'])) {
            $data = $data['busReg'];
        }

        //if no variation number return empty string
        if (!isset($data['variationNo'])) {
            return '';
        }

        $variationNo = Escape::html($data['variationNo']);

        //if the record is short notice, add a short notice status flag
        if (isset($data['isShortNotice']) && $data['isShortNotice'] === 'Y') {
            /**
            * @var \Common\View\Helper\Status $statusHelper
            */
            $statusHelper = $this->viewHelperManager->get('status');

            $status = [
                'colour' => 'orange',
                'value' => ucfirst(strtolower($this->translator->translate(self::SN_TRANSLATION_KEY)))
            ];

            return $variationNo . $statusHelper->__invoke($status);
        }

        //not short notice, so return the variation number by itself
        return $variationNo;
    }
}
