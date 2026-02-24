<?php

namespace Olcs\Service\Marker;

/**
 * BusRegShortNoticeRefused
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class BusRegShortNoticeRefused extends AbstractMarker
{
    #[\Override]
    public function canRender()
    {
        $data = $this->getData();

        return isset($data['busReg']) && $data['busReg']['shortNoticeRefused'] === 'Y';
    }

    #[\Override]
    public function render()
    {
        return $this->renderPartial(
            'busreg-notice-refused',
            []
        );
    }
}
