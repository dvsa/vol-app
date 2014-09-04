<?php

/**
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Document;

use Olcs\Controller\AbstractController;

/**
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
abstract class AbstractDocumentController extends AbstractController
{
    const TMP_STORAGE_PATH = 'tmp/documents';

    public function getContentStore()
    {
        return $this->getServiceLocator()->get('ContentStore');
    }

    public function getDocumentService()
    {
        return $this->getServiceLocator()->get('Document');
    }
}
