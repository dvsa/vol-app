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
use Olcs\Logging\Log\Logger;

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
    protected $itemParams = ['historicId'];
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

    public function detailsAction()
    {
        $itemDto = $this->itemDto;
        $paramProvider = new GenericItem($this->itemParams);

        $this->placeholder()->setPlaceholder('contentTitle', $this->detailsContentTitle);

        $paramProvider->setParams($this->plugin('params'));
        $params = $paramProvider->provideParameters();

        $query = $itemDto::create($params);

        $response = $this->handleQuery($query);

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $data = $response->getResult();

            if (isset($data)) {
                $this->placeholder()->setPlaceholder($this->detailsViewPlaceholderName, $data);
                $this->placeholder()->setPlaceholder(
                    'applicationsTable',
                    $this->table()->buildTable(
                        'historic-tm-applications',
                        $data['applicationData'],
                        []
                    )->render()
                );
                $this->placeholder()->setPlaceholder(
                    'licencesTable',
                    $this->table()->buildTable(
                        'historic-tm-licences',
                        $data['licenceData'],
                        []
                    )->render()
                );
            }
        }

        return $this->viewBuilder()->buildViewFromTemplate($this->detailsViewTemplate);
    }
}
