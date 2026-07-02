<?php

namespace Common\Service\Table\Formatter;

use Common\RefData;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;

/**
 * IRHP Permit Range table - Type column formatter
 */
class IrhpPermitRangeType implements FormatterPluginManagerInterface
{
    public function __construct(private TranslatorDelegator $translator)
    {
    }

     /**
      * Format
      *
      * Returns a formatted column
      *
      * @param array $data
      * @param array $column
      *
      * @return                                        string
      * @SuppressWarnings(PHPMD.UnusedFormalParameter)
      */
    #[\Override]
    public function format($data, $column = [])
    {
        if (!$data['irhpPermitStock']['irhpPermitType']['isBilateral']) {
            return 'N/A';
        }

        if (!empty($data['irhpPermitStock']['permitCategory'])) {
            // use permit category if set
            $key = $data['irhpPermitStock']['permitCategory']['description'];
        } else {
            $key = sprintf(
                'permits.irhp.range.type.%s.%s',
                $data['cabotage'] ? 'cabotage' : 'standard',
                $data['journey']['id'] == RefData::JOURNEY_SINGLE ? 'single' : 'multiple'
            );
        }

        return $this->translator->translate($key);
    }
}
