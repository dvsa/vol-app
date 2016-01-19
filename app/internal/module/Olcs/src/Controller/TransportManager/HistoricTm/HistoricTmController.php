<?php

/**
 * Historic Tm Details Controller
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
namespace Olcs\Controller\TransportManager\HistoricTm;

use Dvsa\Olcs\Transfer\Query\Tm\HistoricTm as HistoricTmQry;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\TransportManager as Mapper;
use Common\Service\Entity\TransportManagerEntityService;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\TransportManagerControllerInterface;
use Olcs\Form\Model\Form\TransportManager as TransportManagerForm;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use Zend\View\Model\ViewModel;
use Common\RefData;

/**
 * Historic Tm Details Controller
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class HistoricTmController extends AbstractInternalController
{
    protected $section = 'transport-manager';

    /* for view */
    protected $detailsViewTemplate = 'sections/transport-manager/pages/historic-tm-details';
    protected $itemDto = HistoricTmQry::class;
    protected $itemParams = ['id' => 'transportManager'];
    protected $detailsViewPlaceholderName = 'details';
    protected $detailsContentTitle = 'Historic Transport Manager Details';

    protected $redirectConfig = [
        'index' => [
            'action' => 'index',
            'route' => 'transport-manager/details',
            'reUseParams' => true,
            'resultIdMap' => [
                'transportManager' => 'transportManager'
            ]
        ]
    ];
}
