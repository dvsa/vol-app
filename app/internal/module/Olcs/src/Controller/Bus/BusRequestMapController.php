<?php

namespace Olcs\Controller\Bus;

use Olcs\Controller\AbstractInternalController;
use Dvsa\Olcs\Transfer\Command\Bus\Ebsr\RequestMap as RequestMapCmd;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\BusRequestMap as Mapper;
use Olcs\Form\Model\Form\BusRequestMap as BusRequestMapForm;

/**
 * Class BusRequestMapController
 * @package Olcs\Controller\Bus
 */
class BusRequestMapController extends AbstractInternalController implements
    BusRegControllerInterface,
    LeftViewProvider
{
    /**
     * @var string
     */
    protected $section = 'processing';
    /**
     * @var string
     */
    protected $subNavRoute = 'licence_bus_processing';

    protected $redirectConfig = [
        'add' => [
            'route' => 'licence/bus-docs',
            'action' => 'documents'
        ]
    ];

    protected $formClass = BusRequestMapForm::class;
    protected $createCommand = RequestMapCmd::class;
    protected $mapperClass = Mapper::class;
    protected $addSuccessMessage = 'Map created successfully';
    protected $addContentTitle = 'Request map';

    protected $defaultData = [
        'busRegId' => 'route'
    ];

    /**
     * function returns null
     *
     * @return null
     */
    public function getLeftView()
    {
        return null;
    }
}
