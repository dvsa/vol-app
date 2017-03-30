<?php

namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Zend\Form\Form;

/**
* Internal Application Undertakings Controller
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DeclarationsInternalController extends \Olcs\Controller\Lva\AbstractDeclarationsInternalController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';

    /**
     * Alter the form
     *
     * @param \Common\Form\Form $form The form
     * @param null              $data Optional array of data
     *
     * @return null
     */
    protected function alterFormForLva(Form $form, $data = null)
    {
        // Get signature data from application declaration DTO
        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Application\Declaration::create(['id' => $this->getIdentifier()])
        );
        // If all ok and verify signature exists on the application
        if ($response->isOk() && $response->getResult()['signature']) {
            // Add signature details into form
            $signatureDetails = $response->getResult()['signature'];
            $form->get('declarations')->get('verifySignatureText')->setValue(
                sprintf(
                    'This application has been digitally signed on %s by %s with date of birth %s',
                    (new \DateTime($signatureDetails['date']))->format(\DATE_FORMAT),
                    $signatureDetails['name'],
                    (new \DateTime($signatureDetails['dob']))->format(\DATE_FORMAT)
                )
            );

            // Change checkbox label
            $form->get('declarations')->get('declarationConfirmation')->setLabel('Signature accepted');
        }

        parent::alterFormForLva($form, $data);
    }
}
