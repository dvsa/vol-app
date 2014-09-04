<?php
/**
 * Case Public Inquiry Hearing Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Pi;

use Zend\View\Model\ViewModel;
use Common\Controller\CrudInterface;
use Olcs\Controller\AbstractController;
use Olcs\Controller\Traits\DeleteActionTrait;

/**
 * Case Public Inquiry Hearing Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class HearingController extends AbstractController implements CrudInterface
{
    use DeleteActionTrait;

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'PiHearing';

    /**
     * Data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'main'
            )
        )
    );

    public function indexAction()
    {
        return $this->redirect()->toRoute('case_pi', [], [], true);
    }

    public function cancelAction()
    {
        return $this->redirect()->toRoute('case_pi', [], [], true);
    }

    public function addAction()
    {
        //
    }

    public function editAction()
    {
        //
    }

    /**
     * Should return the name of the service to call for deleting the item
     *
     * @return string
     */
    public function getDeleteServiceName()
    {
        return $this->getService();
    }
}