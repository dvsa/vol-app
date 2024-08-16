<?php

namespace Dvsa\Olcs\Api\Service\Publication\Formatter;

/**
 * Class FormatOrganisationName
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class OrganisationName
{
    public static function format(\Dvsa\Olcs\Api\Entity\Organisation\Organisation $organisation)
    {
        $text = $organisation->getName();

        $tradingNames = $organisation->getTradingNames();

        if (!$tradingNames->isEmpty()) {
            // Assume they are NOT already in the correct order, find the oldest trading name by lowset PK.
            /* @var $oldestTradingName \Dvsa\Olcs\Api\Entity\Organisation\TradingName */
            $oldestTradingName = null;
            foreach ($tradingNames as $tradingName) {
                if ($oldestTradingName === null || $tradingName->getId() < $oldestTradingName->getId()) {
                    $oldestTradingName = $tradingName;
                }
            }

            $text .= ' T/A ' . $oldestTradingName->getName();
        }
        return $text;
    }
}
