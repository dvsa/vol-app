<?php

namespace Olcs\Controller\BusReg;

use Common\Controller\Lva\AbstractController;
use Dvsa\Olcs\Transfer\Query\Bus\BusRegBrowseExport;
use Olcs\Form\Model\Form\BusRegBrowseForm as Form;
use Zend\View\Model\ViewModel;

/**
 * Class BusRegBrowseController
 */
class BusRegBrowseController extends AbstractController
{
    /**
     * Index action
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        /** @var \Zend\Form\Form $form */
        $form = $this->getServiceLocator()->get('Helper\Form')->createForm(Form::class);

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                // export data
                $postData = $request->getPost();

                $query = BusRegBrowseExport::create(
                    [
                        'trafficAreas' => $postData['trafficAreas'],
                        'status' => $postData['status'],
                        'acceptedDate' => sprintf(
                            '%s-%s-%s',
                            $postData['acceptedDate']['year'],
                            $postData['acceptedDate']['month'],
                            $postData['acceptedDate']['day']
                        ),
                    ]
                );

                $response = $this->handleQuery($query);

                if ($response->isOk()) {
                    // return HTTP response from the api
                    $httpResponse = $response->getHttpResponse();

                    // but make sure we only return allowed headers
                    $headers = new \Zend\Http\Headers();
                    $allowedHeaders = ['Content-Disposition', 'Content-Encoding', 'Content-Type', 'Content-Length'];

                    foreach ($httpResponse->getHeaders() as $header) {
                        if (in_array($header->getFieldName(), $allowedHeaders)) {
                            $headers->addHeader($header);
                        }
                    }
                    $httpResponse->setHeaders($headers);

                    return $httpResponse;

                } elseif ($response->isNotFound()) {
                    // no results found
                    $this->getServiceLocator()->get('Helper\FlashMessenger')
                        ->addErrorMessage('selfserve.search.busreg.browse.no-results');

                } else {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addCurrentUnknownError();
                }
            }
        }

        $view = new ViewModel(['searchForm' => $form]);
        $view->setTemplate('search/index-bus-browse.phtml');

        return $view;
    }
}
