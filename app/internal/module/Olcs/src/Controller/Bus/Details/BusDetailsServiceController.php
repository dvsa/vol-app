<?php

/**
 * Bus Details Service Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Details;

use Dvsa\Olcs\Transfer\Command\Bus\UpdateServiceDetails as UpdateServiceDetailsCommand;

/**
 * Bus Details Service Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusDetailsServiceController extends BusDetailsController
{
    protected $item = 'service';

    /* properties required by CrudAbstract */
    protected $formName = 'bus-service-number-and-type';

    protected $inlineScripts = ['bus-servicenumbers'];

    public function processSave($data)
    {
        $command = new UpdateServiceDetailsCommand();
        $command->exchangeArray($data);
        $response = $this->handleCommand($command);

        return $response;
    }
}
