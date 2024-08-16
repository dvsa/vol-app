<?php

namespace Dvsa\Olcs\Api\Service\Publication\Formatter;

/**
 * Return a formatted list of transport managers
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TransportManagers
{
    public static function format(array $transportManagers)
    {
        if (empty($transportManagers)) {
            return null;
        }

        $tmNames = [];
        foreach ($transportManagers as $transportManager) {
            /* @var $transportManager \Dvsa\Olcs\Api\Entity\Tm\TransportManager */
            $tmNames[] = $transportManager->getHomeCd()->getPerson()->getFullName();
        }

        return 'Transport Manager(s): ' . implode(', ', $tmNames);
    }
}
