<?php

/**
 * Publication Controller
 */
namespace Admin\Controller;

use Olcs\Controller\AbstractInternalController;
use Dvsa\Olcs\Transfer\Query\Publication\PendingList;
use Dvsa\Olcs\Transfer\Command\Publication\Publish as PublishCmd;
use Dvsa\Olcs\Transfer\Command\Publication\Generate as GenerateCmd;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;

/**
 * Publication Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PublicationController extends AbstractInternalController implements PageLayoutProvider, PageInnerLayoutProvider
{
    protected $navigationId = 'admin-dashboard/admin-publication';
    protected $listVars = [];
    protected $inlineScripts = array('indexAction' => ['table-actions', 'file-link']);
    protected $listDto = PendingList::class;
    protected $tableName = 'admin-publication';
    protected $crudConfig = [
        'generate' => ['requireRows' => true],
        'publish' => ['requireRows' => true],
    ];

    /**
     * @return string
     */
    public function getPageLayout()
    {
        return 'layout/wide-layout'; //quick fix, will change with refactor of views
    }

    /**
     * @return string
     */
    public function getPageInnerLayout()
    {
        return 'layout/admin-publication-section';
    }

    /**
     * Generate action
     *
     * @return mixed|\Zend\Http\Response
     */
    public function generateAction()
    {
        $response = $this->handleCommand(GenerateCmd::create(['id' => $this->params()->fromRoute('id')]));

        if ($response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isClientError()) {
            $result = $response->getResult();

            if (isset($result['messages'])) {
                foreach ($result['messages'] as $message) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage($message);
                }
            } else {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }
        }

        if ($response->isOk()) {
            $this->getServiceLocator()
                ->get('Helper\FlashMessenger')
                ->addSuccessMessage('Publication generated successfully');
            return $this->redirectTo($response->getResult());
        }

        $this->redirectTo([]);
    }

    /**
     * Publish action
     *
     * @return mixed|\Zend\Http\Response
     */
    public function publishAction()
    {
        $response = $this->handleCommand(PublishCmd::create(['id' => $this->params()->fromRoute('id')]));

        if ($response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isClientError()) {
            $result = $response->getResult();

            if (isset($result['messages'])) {
                foreach ($result['messages'] as $message) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage($message);
                }
            } else {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }
        }

        if ($response->isOk()) {
            $this->getServiceLocator()
                ->get('Helper\FlashMessenger')
                ->addSuccessMessage('Publication published successfully');
            return $this->redirectTo($response->getResult());
        }

        $this->redirectTo([]);
    }
}
