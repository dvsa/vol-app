<?php

namespace Olcs\Service\Marker\TransportManager;

/**
 * Class IsRemovedMarker
 *
 * @package Olcs\Service\Marker\TransportManager
 */
class IsRemovedMarker extends \Olcs\Service\Marker\AbstractMarker
{
    public function canRender()
    {
        $data = $this->getData();

        if (!isset($data['transportManager'])) {
            return false;
        }

        return !is_null($data['transportManager']['removedDate']);
    }

    public function render()
    {
        $data = $this->getData();

        return $this->renderPartial(
            'transport-manager/is-removed',
            [
                'date' => new \DateTime($data['transportManager']['removedDate'])
            ]
        );
    }
}
