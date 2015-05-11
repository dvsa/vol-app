<?php

/**
 * Send an email to a transport manager with details to complete the digital application form
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Olcs\Service\Email;

use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Send an email to a transport manager with details to complete the digital application form
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TransportManagerCompleteDigitalForm implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Send the email
     *
     * @param int $tmaId Transport Manager Application ID
     */
    public function send($tmaId)
    {
        // Get data
        $transportManagerApplication = $this->getServiceLocator()
            ->get('Entity\TransportManagerApplication')->getContactApplicationDetails($tmaId);
        $contactDetails = $transportManagerApplication['transportManager']['homeCd'];
        $application = $transportManagerApplication['application'];
        $lva = ($application['isVariation']) ? 'variation' : 'application';
        // This URL will always contain the hostname of the system that generated it
        $url = $this->getServiceLocator()->get('Helper\Url')->fromRoute(
            "lva-{$lva}/transport_manager_details/action",
            ['action' => 'edit-details', 'application' => $application['id'], 'child_id' => $tmaId],
            ['force_canonical' => true],
            true
        );

        // Get the email content
        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');
        $content = $translationHelper->translateReplace(
            'markup-email-transport-manager-complete-digital-form',
            [
                $contactDetails['person']['forename'],
                $application['licence']['organisation']['name'],
                $application['licence']['licNo'],
                $application['id'],
                $url
            ],
            $transportManagerApplication['application']['licence']['translateToWelsh']
        );

        // Put content into the template
        $view = new \Zend\View\Model\ViewModel();
        $view->setTemplate('layout/email');
        $view->setVariable('content', $content);

        // send it
        $this->getServiceLocator()->get('Email')->sendEmail(
            'donotreply@otc.gsi.gov.uk',
            'OLCS do not reply',
            $contactDetails['emailAddress'],
            $translationHelper->translate(
                'email.transport-manager-complete-digital-form.subject',
                $transportManagerApplication['application']['licence']['translateToWelsh']
            ),
            $this->getServiceLocator()->get('ViewRenderer')->render($view),
            true
        );
    }
}
