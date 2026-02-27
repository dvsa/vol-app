<?php

namespace Olcs\Service\Marker;

/**
 * BusRegEbsrMarker
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class BusRegEbsrMarker extends AbstractMarker
{
    #[\Override]
    public function canRender()
    {
        $data = $this->getData();

        return isset($data['busReg']) && $data['busReg']['isTxcApp'] === 'Y';
    }

    #[\Override]
    public function render()
    {
        $data = $this->getData();

        return $this->renderPartial(
            'busreg-ebsr',
            [
                'busReg' => $data['busReg']
            ]
        );
    }
}
