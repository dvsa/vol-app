<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Permits\Bilateral\ApplicationCountryRemover;
use Dvsa\Olcs\Api\Service\Qa\QaContext;

class ClientReturnCodeHandler
{
    public const FRONTEND_DESTINATION_OVERVIEW = 'OVERVIEW';
    public const FRONTEND_DESTINATION_CANCEL = 'CANCEL';

    /**
     * Create service instance
     *
     *
     * @return ClientReturnCodeHandler
     */
    public function __construct(private readonly ApplicationCountryRemover $applicationCountryRemover)
    {
    }

    /**
     * Delete the country from the application if required, and return a code indicating the next action to be taken
     * by the frontend
     *
     *
     * @return string
     */
    public function handle(QaContext $qaContext)
    {
        $irhpPermitApplication = $qaContext->getQaEntity();

        $countries = $irhpPermitApplication->getIrhpApplication()->getCountrys();
        if (count($countries) > 1) {
            $this->applicationCountryRemover->remove($irhpPermitApplication);

            return self::FRONTEND_DESTINATION_OVERVIEW;
        }

        return self::FRONTEND_DESTINATION_CANCEL;
    }
}
