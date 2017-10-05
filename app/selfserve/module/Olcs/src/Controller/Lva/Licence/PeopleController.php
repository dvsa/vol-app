<?php

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
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


    public function addAction()
    {


        //@todo get the variation type and check
        /*
            Controller needs to check if the new type of variation already exists (i expect we will know this by using a new id parameter in the route)

            Create a new DTO in transfer eg Dvsa\Olcs\Transfer\Command\Licence\CreatePersonVariation
            Create a new Command handler in backend Dvsa\Olcs\Api\Domain\CommandHandler\Licence\CreatePersonVariation
            Link them up in Api/config

            Create a form (probably very similar to the application one "olcs-common/Common/src/Common/Form/Model/Form/Lva/Person.php")

            There are already DTO handlers for adding a person to a licence, this will be because some type of organisation will already similar functionality, so we need to either modify these or create new ones.
         */

    }
}
