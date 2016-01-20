<?php

namespace Olcs\Service\Marker;

/**
 * BusRegShortNoticeRefused
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class BusRegShortNoticeRefused extends AbstractMarker
{
    public function canRender()
    {
        $data = $this->getData();

        return isset($data['busReg']) && $data['busReg']['shortNoticeRefused'] === 'Y';
    }

    public function render()
    {
        return $this->renderPartial(
            'busreg-notice-refused',
            []
        );
    }
}
