<?php

/**
 * Test document services.
 *
 * OLCS-1587
 *
 * @package		olcs
 * @subpackage          document
 * @author		Shaun Lizzio
 */

namespace Olcs\Controller;

use Common\Controller\AbstractActionController;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

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
        $template_bookmarks = $this->service('Olcs\Template')->get('bookmarks/'.$templateId.'/'.$format.'/'.$country);

        return new JsonModel($template_bookmarks);

    }

    public function generateDocumentAction()
    {
        $country = $this->params('country');
        $format = $this->params('format');
        $templateId = $this->params('templateId');
        $bookmarks = $_POST;
        $documentData = $this->sendPost('Olcs\Document\Generate', [
            'bookmarks' => $bookmarks,
            'country' => $country,
            'templateId' => $templateId
            ]);

        return new JsonModel($documentData);

    }


    public function retrieveDocumentAction()
    {

        $filename = $this->params('filename');
        $country = $this->params('country');
        $format = $this->params('format');

        $serviceData = $this->service('Olcs\Document\Retrieve')->get('retrieve/'.$filename.'/'.$format.'/'.$country);
        $documentData = $this->sendPost('Olcs\Document\Retrieve', [
            'filename' => $filename,
            'format' => $format,
            'country' => $country
            ]);
        $filename = $serviceData['filename'];
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->getHeaders()->addHeaders(array("Content-type"=>"application/rtf",
                                                  "Content-Disposition: attachment; filename=".$filename.".rtf"));


        $documentData = $serviceData['documentData'];
        $response->setContent($documentData);
        return $response;

    }
}
