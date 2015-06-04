<?php

/**
 * Bus Details Quality Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Details;

use Dvsa\Olcs\Transfer\Command\Bus\UpdateQualitySchemes as UpdateQualitySchemesCommand;

/**
 * Bus Details Quality Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusDetailsQualityController extends BusDetailsController
{
    protected $item = 'quality';

    /* properties required by CrudAbstract */
    protected $formName = 'bus-reg-quality';

    public function processSave($data)
    {
        $command = new UpdateQualitySchemesCommand();
        $command->exchangeArray($data);
        return $this->handleCommand($command);
    }
}
