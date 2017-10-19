<?php

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Dvsa\Olcs\Transfer\Command\Licence\CreatePersonVariation;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Zend\Form\Form;

/**
 * External Licence People Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PeopleController extends Lva\AbstractPeopleController
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'external';
    protected $section = 'people';

    /**
     * Alter form for LVA
     *
     * @param Form  $form Form
     * @param array $data Api/Form Data
     *
     * @return void
     */
    protected function alterFormForLva(Form $form, $data = null)
    {
        $table = $form->get('table')->get('table')->getTable();

        $table->removeColumn('actionLinks');
    }

    /**
     * handle the add (Person|Director) button click
     *
     * @return \Common\View\Model\Section|void|\Zend\Http\Response
     */
    public function addAction()
    {
        $adapter = $this->getAdapter();
        $request = $this->getRequest();

        $form = $this->getServiceLocator()
            ->get('FormServiceManager')
            ->get('lva-licence-addperson')
            ->getForm(
                ['canModify' => $adapter->canModify(), 'isPartnership' => $adapter->isPartnership()]
            );

        if ($request->isPost()) {
            $data = (array)$request->getPost();
            $id = $this->getEvent()->getRouteMatch()->getParam('licence');

            $form->setData($data);

            if ($form->isValid()) {
                $validData = $form->getData()['data'];
                $validData['id'] = $id;
                $return = $this->handleCommand(
                    CreatePersonVariation::create(
                        $validData
                    )
                );
                exit("return from command" . $return);
            } else {
                exit("form not valid");
            }
        }


        $companyType = "test";

        $variables = ['sectionText' => 'licence_add-Person-PersonType' . $companyType];

        return $this->render('people', $form, $variables);
    }
}
