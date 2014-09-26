<?php

/**
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Document;

use Zend\View\Model\ViewModel;
use Olcs\Controller\AbstractController;

/**
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
abstract class AbstractDocumentController extends AbstractController
{
    /**
     * Where to store any temporarily generated documents
     */
    const TMP_STORAGE_PATH = 'tmp';

    /**
     * the keyspace where we store our extra metadata about
     * each document in jackrabbit
     */
    const METADATA_KEY = 'data';

    /**
     * Labels for empty select options
     */
    const EMPTY_LABEL = 'Please select';

    protected $tmpData = [];

    protected function getContentStore()
    {
        return $this->getServiceLocator()->get('ContentStore');
    }

    protected function getDocumentService()
    {
        return $this->getServiceLocator()->get('Document');
    }

    protected function getTmpPath()
    {
        return self::TMP_STORAGE_PATH . '/' . $this->params('tmpId');
    }

    protected function removeTmpData()
    {
        $this->getUploader()->remove(
            $this->params('tmpId'),
            self::TMP_STORAGE_PATH
        );
    }

    protected function fetchTmpData()
    {
        if (empty($this->tmpData)) {
            $path = $this->getTmpPath();
            $meta = $this->getContentStore()
                ->readMeta($path);

            if ($meta['exists'] === true) {
                $key = 'meta:' . self::METADATA_KEY;

                $this->tmpData = json_decode(
                    $meta['metadata'][$key],
                    true
                );
            }
        }
        return $this->tmpData;
    }
}
