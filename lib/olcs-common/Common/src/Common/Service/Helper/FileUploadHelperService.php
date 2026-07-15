<?php

namespace Common\Service\Helper;

use Common\Exception\ConfigurationException;
use Common\Exception\File\InvalidMimeException;
use Common\Service\AntiVirus\Scan;
use Common\Service\Table\Type\Selector;
use Olcs\Logging\Log\Logger;
use Laminas\Form\ElementInterface;
use Laminas\Form\FormInterface;
use Laminas\Http\Request;
use Laminas\Stdlib\RequestInterface;

/**
 * File Upload Helper Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FileUploadHelperService extends AbstractHelperService
{
    public const FILE_UPLOAD_ERR_PREFIX = 'message.file-upload-error.';

    public const FILE_UPLOAD_ERR_FILE_LENGTH_TOO_LONG = 'message.file-upload-error.lengthtoolong';

    public const FILE_NAME_MAX_LENGTH = 200;

    /**
     * @var \Laminas\Form\FormInterface
     */
    private $form;

    /**
     * @var string
     */
    private $selector;

    /**
     * @var string
     */
    private $countSelector;

    /**
     * @var callable
     */
    private $uploadCallback;

    /**
     * @var callable
     */
    private $deleteCallback;

    /**
     * @var callable
     */
    private $loadCallback;

    /**
     * @var \Laminas\Http\Request
     */
    private $request;

    public function __construct(protected UrlHelperService $urlHelper, protected Scan $antiVirusService)
    {
    }

    /**
     * Get Form
     *
     * @return \Laminas\Form\FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Set Form
     *
     * @param \Laminas\Form\FormInterface $form Form
     *
     * @return $this
     */
    public function setForm($form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * Get Selector
     *
     * @return string
     */
    public function getSelector()
    {
        return $this->selector;
    }

    /**
     * Set selector
     *
     * @param string $selector Selector
     *
     * @return $this
     */
    public function setSelector($selector)
    {
        $this->selector = $selector;
        return $this;
    }

    /**
     * Get count selector
     *
     * @return string
     */
    public function getCountSelector()
    {
        return $this->countSelector;
    }

    /**
     * Set Count selector
     *
     * @param string $selector Count Selector
     *
     * @return $this
     */
    public function setCountSelector($selector)
    {
        $this->countSelector = $selector;
        return $this;
    }

    /**
     * Get upload callback
     *
     * @return callable
     */
    public function getUploadCallback()
    {
        return $this->uploadCallback;
    }

    /**
     * Set upload callback
     *
     * @param string $uploadCallback Name of method
     *
     * @return $this
     */
    public function setUploadCallback($uploadCallback)
    {
        $this->uploadCallback = $uploadCallback;
        return $this;
    }

    /**
     * Get delete callback
     *
     * @return callable
     */
    public function getDeleteCallback()
    {
        return $this->deleteCallback;
    }

    /**
     * Set delete callback
     *
     * @param callable $deleteCallback Delete callback
     *
     * @return $this
     */
    public function setDeleteCallback($deleteCallback)
    {
        $this->deleteCallback = $deleteCallback;
        return $this;
    }

    /**
     * Get load callback
     *
     * @return callable
     */
    public function getLoadCallback()
    {
        return $this->loadCallback;
    }

    /**
     * Load callback
     *
     * @param callable $loadCallback Load callback
     *
     * @return $this
     */
    public function setLoadCallback($loadCallback)
    {
        $this->loadCallback = $loadCallback;
        return $this;
    }

    /**
     * Get request service
     *
     * @return \Laminas\Http\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set request service
     *
     * @param RequestInterface $request Request service
     *
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Process file uploads/deletions and list population
     *
     * @return boolean
     */
    public function process()
    {
        $processed = false;

        if ($this->getRequest()->isPost() && $this->processFileUploads()) {
            $processed = true;
        }

        $this->populateFileList();

        if ($this->getRequest()->isPost() && $this->processFileDeletions()) {
            return true;
        }

        return $processed;
    }

    /**
     * Populate file list
     *
     * @throws ConfigurationException
     */
    private function populateFileList(): void
    {
        $callback = $this->getLoadCallback();

        if ($callback === null) {
            return;
        }

        if (!is_callable($callback)) {
            throw new ConfigurationException('Load data callback is not callable');
        }

        $url = $this->urlHelper;

        $files = call_user_func($callback);

        $element = $this->findElement($this->getForm(), $this->getSelector());

        $element->get('list')->setFiles($files, $url);

        $this->updateCount(count($files));
    }

    /**
     * Update count
     *
     * @param int $count new count
     *
     * @return void
     */
    protected function updateCount($count)
    {
        $selector = $this->getCountSelector();

        if (!is_null($selector)) {
            $this->findElement($this->getForm(), $selector)->setValue($count);
        }
    }

    /**
     * Remove 1 from count
     *
     * @return void
     */
    protected function decrementCount()
    {
        $selector = $this->getCountSelector();

        if (!is_null($selector)) {
            $element = $this->findElement($this->getForm(), $selector);
            $count = (int)$element->getValue();
            if ($count > 0) {
                $element->setValue($count - 1);
            }
        }
    }

    /**
     * Process a file uploads
     *
     * @NOTE we return true if we have processed the files, regardless of state
     *
     * @return boolean
     */
    private function processFileUploads()
    {
        // If we don't have a callable upload callback, we can just return false
        $callback = $this->getUploadCallback();

        if (!is_callable($callback)) {
            return false;
        }

        // Check if the upload button has been pressed
        $postData = $this->findSelectorData((array)$this->request->getPost(), $this->getSelector());
        $fileData = $this->findSelectorData((array)$this->request->getFiles(), $this->getSelector());

        //  if Files and Post are empty this is as symptom that the PHP ini post_max_size has been exceeded
        if (empty($postData) && empty($fileData)) {
            $this->getForm()->setMessages(
                $this->formatErrorMessageForForm(self::FILE_UPLOAD_ERR_PREFIX . UPLOAD_ERR_INI_SIZE)
            );

            return false;
        }

        /**
         * @TODO: these next two statements *are* temporary; the old MultipleFileUpload element groups
         * all its inputs (including the file itself) under a nested 'file-controls' fieldset. The updated
         * mechanism using form annotations and the MultipleFileUpload fieldset can't do that. However, to
         * preserve BC we simply copy the top-level file data onto the expected 'file-controls' key to avoid
         * disruption and to maintain both
         *
         * At some point we should nuke the MultipleFileUpload _element_ altogether and remove this
         */
        if (isset($postData) && !isset($postData['file-controls'])) {
            $postData['file-controls'] = $postData;
        }

        if (isset($fileData) && !isset($fileData['file-controls'])) {
            $fileData['file-controls'] = $fileData;
        }

        if (
            $postData === null
            || $fileData === null
            || !isset($postData['file-controls']['upload'])
            || empty($postData['file-controls']['upload'])
            || !isset($fileData['file-controls']['file'])
        ) {
            return false;
        }

        $error = $fileData['file-controls']['file']['error'];
        if ($error !== UPLOAD_ERR_OK) {
            $this->getForm()->setMessages($this->formatErrorMessageForForm(self::FILE_UPLOAD_ERR_PREFIX . $error));

            return false;
        }

        $fileName = $fileData['file-controls']['file']['name'];
        if (strlen($fileName) > self::FILE_NAME_MAX_LENGTH) {
            $this->getForm()->setMessages(
                $this->formatErrorMessageForForm(
                    self::FILE_UPLOAD_ERR_FILE_LENGTH_TOO_LONG
                )
            );

            return false;
        }

        $fileTmpName = $fileData['file-controls']['file']['tmp_name'];
        // eg onAccess anti-virus removed it
        if (!file_exists($fileTmpName)) {
            $this->getForm()->setMessages($this->formatErrorMessageForForm(self::FILE_UPLOAD_ERR_PREFIX . 'missing'));

            return false;
        }

        // Run virus scan on file
        $scanner = $this->antiVirusService;
        if ($scanner->isEnabled() && !$scanner->isClean($fileTmpName)) {
            $this->getForm()->setMessages($this->formatErrorMessageForForm(self::FILE_UPLOAD_ERR_PREFIX . 'virus'));

            return false;
        }

        try {
            call_user_func(
                $callback,
                $fileData['file-controls']['file']
            );
        } catch (InvalidMimeException) {
            $this->invalidMime();

            return false;
        } catch (\Exception $ex) {
            $this->failedUpload();
            Logger::debug('FileUploadHelperService error', [
                'callback' => $callback,
                'Exception message' => $ex->getMessage(),
                'StackTrace' => json_encode($ex->getTrace()),
                'Filedata' => $fileData
            ]);

            return false;
        }

        return true;
    }

    /**
     * Process a single file deletion
     *
     * @return bool
     */
    private function processFileDeletions()
    {
        $callback = $this->getDeleteCallback();

        if (!is_callable($callback)) {
            return false;
        }

        $postData = $this->findSelectorData((array)$this->getRequest()->getPost(), $this->getSelector());

        if (
            $postData === null
            || !isset($postData['list'])
        ) {
            return false;
        }

        $element = $this->findElement($this->getForm(), $this->getSelector());

        $list = $element->get('list');

        foreach ($list->getFieldsets() as $listFieldset) {
            $name = $listFieldset->getName();

            if (
                isset($postData['list'][$name]['remove'])
                && !empty($postData['list'][$name]['remove'])
            ) {
                $success = call_user_func(
                    $callback,
                    $postData['list'][$name]['id']
                );

                if ($success === true) {
                    $list->remove($name);
                    $this->decrementCount();
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Build the error array for the form
     *
     * @param string $message Message for error
     *
     * @return array
     */
    private function formatErrorMessageForForm($message)
    {
        $array = [];
        $reference = &$array;
        $selector = $this->getSelector();

        while (strstr($selector, '->')) {
            [$index, $selector] = explode('->', $selector, 2);

            $reference[$index] = [];

            $reference = &$reference[$index];
        }

        $reference[$selector]['__messages__'] = [$message];

        return $array;
    }

    /**
     * Find the selector index of the given data
     *
     * @param array  $data     array of given data
     * @param string $selector selector
     *
     * @return array
     */
    private function findSelectorData($data, $selector)
    {
        if (strstr($selector, '->')) {
            [$index, $selector] = explode('->', $selector, 2);

            if (!isset($data[$index])) {
                return null;
            }

            return $this->findSelectorData($data[$index], $selector);
        }

        if (!isset($data[$selector])) {
            return null;
        }

        return $data[$selector];
    }

    /**
     * Find the element by the selector
     *
     * @param FormInterface $form     Form
     * @param string        $selector Selector
     *
     * @return \Laminas\Form\ElementInterface
     */
    public function findElement($form, $selector)
    {
        if (strstr($selector, '->')) {
            [$element, $selector] = explode('->', $selector, 2);
            return $this->findElement($form->get($element), $selector);
        }

        return $form->get($selector);
    }

    /**
     * Invalid mime
     */
    private function invalidMime(): void
    {
        $this->getForm()->setMessages($this->formatErrorMessageForForm('ERR_MIME'));
    }

    /**
     * Failed upload
     */
    private function failedUpload(): void
    {
        $this->getForm()->setMessages($this->formatErrorMessageForForm(self::FILE_UPLOAD_ERR_PREFIX . 'any'));
    }
}
