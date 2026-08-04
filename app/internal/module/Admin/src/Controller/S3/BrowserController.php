<?php

declare(strict_types=1);

namespace Admin\Controller\S3;

use Admin\Form\Model\Form\S3BucketOverwrite as S3BucketOverwriteForm;
use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Common\Service\AntiVirus\Scan;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Util\FileContent;
use Dvsa\Olcs\Transfer\Command\Document\BucketBrowserOverwrite;
use Dvsa\Olcs\Transfer\Query\Document\BucketBrowserDownload;
use Dvsa\Olcs\Transfer\Query\Document\BucketBrowserList;
use Laminas\Http\Headers;
use Laminas\Http\Response;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

/**
 * Super-admin S3 document-store browser. Lists straight from S3 (decoupled from the document
 * table), proxies downloads through the API (audited, no presigned URLs), and (Phase 2) overwrites
 * objects. Access is gated by RBAC (system-admin route guard) and feature toggles: browse/download
 * by S3_BUCKET_BROWSER; overwrite additionally by S3_BUCKET_BROWSER_OVERWRITE.
 */
class BrowserController extends AbstractInternalController implements LeftViewProvider, ToggleAwareInterface
{
    protected $navigationId = 'admin-dashboard/admin-s3-browser';

    protected $toggleConfig = [
        'default' => [FeatureToggle::S3_BUCKET_BROWSER],
        'overwrite' => [FeatureToggle::S3_BUCKET_BROWSER, FeatureToggle::S3_BUCKET_BROWSER_OVERWRITE],
    ];

    /** Only these headers from the API stream response are passed through to the browser. */
    private const ALLOWED_DOWNLOAD_HEADERS = ['Content-Disposition', 'Content-Encoding', 'Content-Type', 'Content-Length', 'X-Content-Type-Options'];

    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelperService,
        Navigation $navigation,
        protected Scan $avScanner
    ) {
        parent::__construct($translationHelper, $formHelper, $flashMessengerHelperService, $navigation);
    }

    #[\Override]
    public function getLeftView()
    {
        $view = new ViewModel([
            'navigationId' => $this->navigationId,
            'navigationTitle' => 'Document store browser',
        ]);
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Browse one delimiter-grouped level of the bucket.
     */
    #[\Override]
    public function indexAction(): ViewModel
    {
        $prefix = trim((string) $this->params()->fromQuery('prefix', ''));
        $continuationToken = (string) $this->params()->fromQuery('ct', '');

        $response = $this->handleQuery(BucketBrowserList::create([
            'prefix' => $prefix,
            'continuationToken' => $continuationToken !== '' ? $continuationToken : null,
        ]));

        if ($response->isOk()) {
            // The API list route wraps the handler's payload in the standard list envelope
            // ({count, results, ...}), so the listing is under 'results'.
            $listing = $response->getResult()['results'] ?? [];
        } else {
            $this->flashMessengerHelperService->addUnknownError();
            $listing = [];
        }

        $prefix = $listing['prefix'] ?? $prefix;

        $this->placeholder()->setPlaceholder('pageTitle', 'Document store browser');

        $view = new ViewModel([
            'prefix' => $prefix,
            'breadcrumb' => $this->buildBreadcrumb($prefix),
            'folders' => $listing['folders'] ?? [],
            'objects' => $listing['objects'] ?? [],
            'nextContinuationToken' => $listing['nextContinuationToken'] ?? null,
            'canOverwrite' => $this->featuresEnabled(
                ['default' => [FeatureToggle::S3_BUCKET_BROWSER_OVERWRITE]],
                $this->getEvent()
            ),
        ]);
        $view->setTemplate('admin/sections/admin/pages/s3-browser/index');

        return $view;
    }

    /**
     * Proxy an object's bytes from the API to the browser (gated + audited server-side). The raw
     * key is passed as a query param (keys contain slashes), defaulting to a download (attachment).
     *
     * @return \Laminas\Http\Response
     */
    public function downloadAction()
    {
        $response = $this->handleQuery(BucketBrowserDownload::create([
            'key' => (string) $this->params()->fromQuery('key', ''),
            'isStream' => true,
        ]));

        if (!$response->isOk()) {
            throw new \RuntimeException('Error downloading file');
        }

        $httpResponse = $response->getHttpResponse();

        $headers = new Headers();
        foreach ($httpResponse->getHeaders() as $header) {
            if (in_array($header->getFieldName(), self::ALLOWED_DOWNLOAD_HEADERS, true)) {
                $headers->addHeader($header);
            }
        }
        $httpResponse->setHeaders($headers);

        return $httpResponse;
    }

    /**
     * Phase 2: confirm + overwrite an object at a raw key. Gated by the overwrite toggle (via the
     * 'overwrite' toggleConfig). The target key is a query param.
     *
     * @return ViewModel|\Laminas\Http\Response
     */
    public function overwriteAction()
    {
        $key = trim((string) $this->params()->fromQuery('key', ''));
        if ($key === '') {
            return $this->redirectToBrowser('');
        }

        $form = $this->getForm(S3BucketOverwriteForm::class);
        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData((array) $request->getPost());
            if ($form->isValid()) {
                $result = $this->processOverwrite($key, $form);
                if ($result instanceof Response) {
                    return $result;
                }
            }
        }

        $this->placeholder()->setPlaceholder('pageTitle', 'Overwrite object');

        $view = new ViewModel(['form' => $form, 'key' => $key]);
        $view->setTemplate('admin/sections/admin/pages/s3-browser/overwrite');

        return $view;
    }

    /**
     * @return \Common\Form\Form|\Laminas\Http\Response
     */
    private function processOverwrite(string $key, \Common\Form\Form $form)
    {
        $fileField = $form->get('fields')->get('file');
        $files = $this->getRequest()->getFiles()->toArray();
        $file = $files['fields']['file'] ?? null;

        if ($file === null || $file['error'] !== UPLOAD_ERR_OK) {
            $fileField->setMessages(['Please choose a file to upload']);
            return $form;
        }

        $tmpName = $file['tmp_name'];
        if (!file_exists($tmpName)) {
            $fileField->setMessages(['The uploaded file could not be found']);
            return $form;
        }

        if ($this->avScanner->isEnabled() && !$this->avScanner->isClean($tmpName)) {
            $fileField->setMessages(['The uploaded file failed the virus scan']);
            return $form;
        }

        $response = $this->handleCommand(BucketBrowserOverwrite::create([
            'key' => $key,
            'content' => new FileContent($tmpName, $file['type'] ?? null),
        ]));

        if ($response->isOk()) {
            $this->flashMessengerHelperService->addSuccessMessage('Object overwritten: ' . $key);
            return $this->redirectToBrowser($this->parentPrefix($key));
        }

        $this->flashMessengerHelperService->addUnknownError();
        return $form;
    }

    /**
     * @return \Laminas\Http\Response
     */
    private function redirectToBrowser(string $prefix)
    {
        return $this->redirect()->toRoute('admin-dashboard/admin-s3-browser', [], ['query' => ['prefix' => $prefix]]);
    }

    private function parentPrefix(string $key): string
    {
        $pos = strrpos($key, '/');
        return $pos === false ? '' : substr($key, 0, $pos + 1);
    }

    /**
     * Build breadcrumb segments (root + each prefix part) for the explorer view.
     *
     * @return array<int, array{label:string, prefix:string}>
     */
    private function buildBreadcrumb(string $prefix): array
    {
        $crumbs = [['label' => 'Document store', 'prefix' => '']];

        $accumulated = '';
        foreach (array_filter(explode('/', $prefix)) as $segment) {
            $accumulated .= $segment . '/';
            $crumbs[] = ['label' => $segment, 'prefix' => $accumulated];
        }

        return $crumbs;
    }
}
