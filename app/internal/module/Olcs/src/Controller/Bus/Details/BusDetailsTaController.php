<?php

/**
 * Bus Details Ta Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Details;

use Dvsa\Olcs\Transfer\Command\Bus\UpdateTaAuthority as UpdateTaAuthorityCommand;

/**
 * Bus Details Ta Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusDetailsTaController extends BusDetailsController
{
    protected $item = 'ta';

    /* properties required by CrudAbstract */
    protected $formName = 'bus-reg-ta';

    protected $inlineScripts = ['forms/bus-details-ta'];

    public function processSave($data)
    {
        $command = new UpdateTaAuthorityCommand();
        $command->exchangeArray($data);
        return $this->handleCommand($command);
    }
}
