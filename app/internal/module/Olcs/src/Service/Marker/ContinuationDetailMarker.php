<?php

namespace Olcs\Service\Marker;

/**
 * ContinuationDetailMarker
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ContinuationDetailMarker extends AbstractMarker
{
    public function canRender()
    {
        $data = $this->getData();

        return isset($data['continuationDetail']['continuation']) && isset($data['licence']['id']);
    }

    public function render()
    {
        $data = $this->getData();

        $continuation = $data['continuationDetail']['continuation'];

        return $this->renderPartial(
            'continuation',
            [
                'dateTime' => new \DateTime($continuation['year'] . '-' . $continuation['month'] . '-01'),
                'licenceId' => $data['licence']['id'],
            ]
        );
    }
}
