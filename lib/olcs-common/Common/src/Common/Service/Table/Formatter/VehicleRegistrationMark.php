<?php

namespace Common\Service\Table\Formatter;

use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;

/**
 * Vehicle Registration Mark Formatter which displays Vehicle Registration mark with
 * an indicator if the licence is an interim licence
 *
 * @author Richard Ward <richard.ward@bjss.com>
 */
class VehicleRegistrationMark implements FormatterPluginManagerInterface
{
    public function __construct(private TranslatorDelegator $translator)
    {
    }

    /**
     * Format a Vehicle Registration Mark with an 'interim' indicator where relevant.
     *
     * @param array $data   The row data.
     * @param array $column The column data.
     *
     * @return string The formatted Vehicle Registration Mark
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $vrm = $data['vehicle']['vrm'];
        return empty($data['interimApplication'])
            ? $vrm
            : self::formatInterimValue($vrm);
    }

    /**
     * Format a Vehicle Registration Mark when an 'interim' indicator is required
     *
     * @param string $vrm The vehicle registration mark
     *
     * @return string The formatted Vehicle Registration Mark
     */
    private function formatInterimValue($vrm)
    {
        return sprintf(
            '%s (%s)',
            $vrm,
            $this->translator->translate(
                'application_vehicle-safety_vehicle.table.vrm.interim-marker'
            )
        );
    }
}
