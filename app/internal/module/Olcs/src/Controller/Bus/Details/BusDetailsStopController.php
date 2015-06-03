<?php

/**
 * Bus Details Stop Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Details;

use Dvsa\Olcs\Transfer\Command\Bus\UpdateStops as UpdateStopsCommand;

/**
 * Bus Details Stop Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusDetailsStopController extends BusDetailsController
{
    protected $item = 'stop';

    /* properties required by CrudAbstract */
    protected $formName = 'bus-reg-stop';

    public function processSave($data)
    {
        $command = new UpdateStopsCommand();
        $command->exchangeArray($data);
        return $this->handleCommand($command);
    }
}
