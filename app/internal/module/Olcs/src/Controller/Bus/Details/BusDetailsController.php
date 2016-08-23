<?php

/**
 * Bus Details Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Details;

use Dvsa\Olcs\Transfer\Query\Bus\BusReg as ItemDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use \Olcs\Data\Mapper\BusReg as BusRegMapper;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Olcs\Controller\Traits as ControllerTraits;
use Olcs\Form\Model\Form\BusServiceNumberAndType as ServiceForm;
use Dvsa\Olcs\Transfer\Command\Bus\UpdateServiceDetails as UpdateServiceCmd;
use Olcs\Form\Model\Form\BusRegTa as TaForm;
use Dvsa\Olcs\Transfer\Command\Bus\UpdateTaAuthority as UpdateTaCmd;
use Olcs\Form\Model\Form\BusRegStop as StopForm;
use Dvsa\Olcs\Transfer\Command\Bus\UpdateStops as UpdateStopCmd;
use Olcs\Form\Model\Form\BusRegQuality as QualityForm;
use Dvsa\Olcs\Transfer\Command\Bus\UpdateQualitySchemes as UpdateQualityCmd;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use Zend\View\Model\ViewModel;

/**
 * Bus Details Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusDetailsController extends AbstractInternalController implements
    BusRegControllerInterface,
    LeftViewProvider
{
    use ControllerTraits\BusControllerTrait;

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/bus/partials/left');

        return $view;
    }

    protected $redirectConfig = [
        'service' => [
            'action' => 'service'
        ],
        'ta' => [
            'action' => 'ta'
        ],
        'stop' => [
            'action' => 'stop'
        ],
        'quality' => [
            'action' => 'quality'
        ],
    ];

    protected $navigationId = 'licence_bus_details';

    protected $inlineScripts = ['serviceAction' => ['bus-servicenumbers'], 'taAction' => ['forms/bus-details-ta']];

    protected $itemDto = ItemDto::class;
    protected $itemParams = ['id' => 'busRegId'];
    protected $mapperClass = BusRegMapper::class;
    protected $section = 'details';
    protected $subNavRoute = 'licence_bus_details';

    /**
     * @return array|\Zend\View\Model\ViewModel
     */
    public function serviceAction()
    {
        return $this->edit(
            ServiceForm::class,
            $this->itemDto,
            new GenericItem($this->itemParams),
            UpdateServiceCmd::class,
            $this->mapperClass,
            $editViewTemplate = 'pages/crud-form',
            $successMessage = 'Updated record',
            $contentTitle = 'Service No. & type'
        );
    }

    /**
     * @return array|\Zend\View\Model\ViewModel
     */
    public function taAction()
    {
        return $this->edit(
            TaForm::class,
            $this->itemDto,
            new GenericItem($this->itemParams),
            UpdateTaCmd::class,
            $this->mapperClass,
            $editViewTemplate = 'pages/crud-form',
            $successMessage = 'Updated record',
            $contentTitle = 'TA\'s & authorities'
        );
    }

    /**
     * @return array|\Zend\View\Model\ViewModel
     */
    public function stopAction()
    {
        return $this->edit(
            StopForm::class,
            $this->itemDto,
            new GenericItem($this->itemParams),
            UpdateStopCmd::class,
            $this->mapperClass,
            $editViewTemplate = 'pages/crud-form',
            $successMessage = 'Updated record',
            $contentTitle = 'Stops, manoeuvres & subsidies'
        );
    }

    /**
     * @return array|\Zend\View\Model\ViewModel
     */
    public function qualityAction()
    {
        return $this->edit(
            QualityForm::class,
            $this->itemDto,
            new GenericItem($this->itemParams),
            UpdateQualityCmd::class,
            $this->mapperClass,
            $editViewTemplate = 'pages/crud-form',
            $successMessage = 'Updated record',
            $contentTitle = 'Quality schemes'
        );
    }

    /**
     * If not latest variation, or is EBSR, or status is 'registered' or 'cancelled', show read only form
     * @param \Common\Form\Form $form
     * @param array $formData
     * @return \Common\Form\Form
     */
    protected function alterForm($form, $formData)
    {
        $busReg = $this->getBusReg();

        if ($busReg['isReadOnly'] || $busReg['isFromEbsr']) {
            $form->setOption('readonly', true);
        }

        return $form;
    }

    /**
     * @param \Common\Form\Form $form
     * @param array $formData
     * @return \Common\Form\Form
     */
    protected function alterFormForService($form, $formData)
    {
        return $this->alterForm($form, $formData);
    }

    /**
     * @param \Common\Form\Form $form
     * @param array $formData
     * @return \Common\Form\Form
     */
    protected function alterFormForTa($form, $formData)
    {
        return $this->alterForm($form, $formData);
    }

    /**
     * @param \Common\Form\Form $form
     * @param array $formData
     * @return \Common\Form\Form
     */
    protected function alterFormForStop($form, $formData)
    {
        return $this->alterForm($form, $formData);
    }

    /**
     * @param \Common\Form\Form $form
     * @param array $formData
     * @return \Common\Form\Form
     */
    protected function alterFormForQuality($form, $formData)
    {
        return $this->alterForm($form, $formData);
    }
}
