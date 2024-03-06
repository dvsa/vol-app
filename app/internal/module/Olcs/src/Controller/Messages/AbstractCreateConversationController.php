<?php

namespace Olcs\Controller\Messages;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Data\Mapper\DefaultMapper;
use Common\FeatureToggle;
use Dvsa\Olcs\Transfer\Command\Messaging\Conversation\Create;
use Dvsa\Olcs\Transfer\Query\Messaging\ApplicationLicenceList\ByApplicationToOrganisation;
use Dvsa\Olcs\Transfer\Query\Messaging\ApplicationLicenceList\ByLicenceToOrganisation;
use Dvsa\Olcs\Transfer\Query\Messaging\ApplicationLicenceList\ByCaseToOrganisation;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\MessagingControllerInterface;
use Olcs\Form\Model\Form\Conversation;
use RuntimeException;

class AbstractCreateConversationController extends AbstractInternalController implements LeftViewProvider, ToggleAwareInterface, MessagingControllerInterface
{
    protected $mapperClass = DefaultMapper::class;

    protected $createCommand = Create::class;

    protected $formClass = Conversation::class;

    protected $toggleConfig = [
        'default' => [
            FeatureToggle::MESSAGING
        ],
    ];

    protected $inlineScripts = [
        'addAction' => ['forms/message-categories']
    ];

    public function getLeftView(): ViewModel
    {
        $view = new ViewModel(['navigationId' => $this->navigationId]);
        $view->setTemplate('sections/messages/partials/left');

        return $view;
    }

    public function alterFormForAdd($form)
    {
        $appLicNoSelect = $form->get('fields')->get('appOrLicNo');

        if ($this->params()->fromRoute('licence')){
            $licenceId = $this->params()->fromRoute('licence');
            $data = $this->handleQuery(
                ByLicenceToOrganisation::create(['licence' => $licenceId])
            );
        } elseif ($this->params()->fromRoute('application')) {
            $applicationId = $this->params()->fromRoute('application');
            $data = $this->handleQuery(
                ByApplicationToOrganisation::create(['application' => $applicationId])
            );
        } elseif ($this->params()->fromRoute('case')) {
            $caseId = $this->params()->fromRoute('case');
            $data = $this->handleQuery(
                ByCaseToOrganisation::create(['case' => $caseId])
            );
        } else {
            throw new RuntimeException('Error: licence or application required');
        }

        $applicationLicenceArray = json_decode($data->getHttpResponse()->getBody(), true);

        $this->prefixArrayKey($applicationLicenceArray['results']['licences'], 'L');
        $this->prefixArrayKey($applicationLicenceArray['results']['applications'], 'A');

        $options = [];

        if($applicationLicenceArray['results']['licences']){
            $options['licence'] = [
                'label' => 'Licences',
                'options' => $applicationLicenceArray['results']['licences'],
            ];
        }

        if($applicationLicenceArray['results']['applications']){
            $options['application'] = [
                'label' => 'Applications',
                'options' => $applicationLicenceArray['results']['applications'],
            ];
        }

        $appLicNoSelect->setValueOptions($options);

        return $form;
    }

    public function onDispatch(MvcEvent $e)
    {
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $postFields = $postData->get('fields');
            $appOrLicNo = $postFields['appOrLicNo'] ?? null;
            if (!empty($appOrLicNo)) {
                switch ( str_split($appOrLicNo, 1)[0] )
                {
                    case 'A':
                        $postFields['application'] = substr_replace($appOrLicNo, '', 0, 1);
                        $postFields['licence'] = '';
                        break;
                    case 'L':
                        $postFields['licence'] = substr_replace($appOrLicNo, '', 0, 1);
                        $postFields['application'] = '';
                        break;
                    default:
                        throw new \Laminas\Validator\Exception\RuntimeException('Unexpected prefix in appOrLicNo');
                }
                $postData->set('fields', $postFields);
                $this->getRequest()->setPost($postData);
            }
        }

        return parent::onDispatch($e);
    }

    private function prefixArrayKey(array &$array, string $prefix): void
    {
        foreach ($array as $k => $v)
        {
            $array[$prefix . $k] = $v;
            unset($array[$k]);
        }
    }
}
