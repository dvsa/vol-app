<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

/**
 * Abstract ECMT short term email handler
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
abstract class AbstractEcmtShortTermEmailHandler extends AbstractEmailHandler
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    protected function getTranslateToWelsh($recordObject)
    {
        return $recordObject->getLicence()->getTranslateToWelsh();
    }

    /**
     * Format a fee as currency
     *
     * param float $amount
     *
     * @return string
     */
    protected function formatCurrency($amount)
    {
         return str_replace('.00', '', $amount);
    }
}
