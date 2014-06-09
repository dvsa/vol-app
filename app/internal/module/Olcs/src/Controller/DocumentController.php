<?php

/**
 * Test document services.
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
namespace Olcs\Controller;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

/**
 * Test document services.
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class DocumentController extends AbstractActionController
{

    public $messages = null;

    /*
     * Generates a case list using the DataListPlugin
     */
    public function indexAction()
    {

        $allTemplates = $this->service('Olcs\Template')->get('list');

        $view = new ViewModel(['allTemplates' => $allTemplates]);
        $view->setTemplate('olcs/document/test-templates');
        return $view;
    }

    /**
     * Method to extract and return a template bookmarks
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function getTemplateBookmarksAction()
    {

        $country = $this->params('country');
        $format = $this->params('format');
        $templateId = $this->params('template');
        $template_bookmarks = $this->service('Olcs\Template')
            ->get('bookmarks/' . $templateId . '/' . $format . '/' . $country);

        return new JsonModel($template_bookmarks);
    }

    public function generateDocumentAction()
    {
        $country = $this->params('country');
        $templateId = $this->params('templateId');
        $bookmarks = $_POST;
        $documentData = $this->sendPost(
            'Olcs\Document\Generate', array(
                'bookmarks' => $bookmarks,
                'country' => $country,
                'templateId' => $templateId
            )
        );

        return new JsonModel($documentData);
    }

    public function retrieveDocumentAction()
    {
        $filename = $this->params('filename');
        $country = $this->params('country');
        $format = $this->params('format');

        //$serviceData = $this->service('Olcs\Document\Retrieve')->get('retrieve/'.$filename.'/'.$format.'/'.$country);
        $documentData = $this->sendGet(
            'Olcs\Document\Retrieve',
            array(
                'filename' => $filename,
                'format' => $format,
                'country' => $country
            )
        );

        $filename = $documentData['filename'];
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->getHeaders()->addHeaders(
            array(
                "Content-type" => "application/rtf",
                "Content-Disposition: attachment; filename=" . $filename . ".rtf"
            )
        );

        $response->setContent($documentData['documentData']);
        return $response;
    }
}
