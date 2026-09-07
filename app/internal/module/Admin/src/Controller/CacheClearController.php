<?php

namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Command\Cache\Clear;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class CacheClearController extends AbstractInternalController implements LeftViewProvider
{
    protected $navigationId = 'admin-dashboard/admin-cache-clear';

    /**
     * Maps each checkbox on the form to the cache namespaces it clears.
     *
     * The grouping exists because a couple of caches are only meaningful as a pair - clearing
     * translation keys without their replacements would leave the page half updated - and
     * because "sys_param" means nothing to the person using this screen.
     *
     * Keys here must match the value_options on Admin\Form\Model\Form\CacheClear; values must be
     * drawn from Clear::NAMESPACES, which is what the API command handler will accept.
     */
    private const array CACHE_NAMESPACE_MAP = [
        'translations' => [
            CacheEncryption::TRANSLATION_KEY_IDENTIFIER,
            CacheEncryption::TRANSLATION_REPLACEMENT_IDENTIFIER,
        ],
        'system_parameters' => [
            CacheEncryption::SYS_PARAM_IDENTIFIER,
            CacheEncryption::SYS_PARAM_LIST_IDENTIFIER,
        ],
        'cqrs' => [
            Clear::NAMESPACE_CQRS,
        ],
        'doctrine' => [
            Clear::NAMESPACE_DOCTRINE,
        ],
        'jwks' => [
            Clear::NAMESPACE_JWKS,
        ],
    ];

    #[\Override]
    public function getLeftView()
    {
        $view = new ViewModel([
            'navigationId' => $this->navigationId,
            'navigationTitle' => 'Clear cache',
        ]);

        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    #[\Override]
    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Clear cache');
        $this->placeholder()->setPlaceholder('contentTitle', 'Clear cache');

        $request = $this->getRequest();

        $form = $this->formHelperService
            ->createFormWithRequest('CacheClear', $request);

        $formActions = $form->get('form-actions');

        $formActions->get('submit')
            ->setLabel('Clear cache')
            ->setAttribute('aria-label', 'Clear cache');

        $formActions->remove('cancel');
        $formActions->remove('addAnother');

        $isPost = $request->isPost();

        if ($isPost) {
            $form->setData((array) $request->getPost());
        }

        if ($isPost && $form->isValid()) {
            $data = $form->getData();

            $response = $this->handleCommand(
                Clear::create([
                    'namespace' => implode(',', $this->resolveNamespaces($data['cacheTypes'] ?? [])),
                    'dryRun' => false,
                ])
            );

            if ($response->isOk()) {
                $this->flashMessengerHelperService
                    ->addSuccessMessage($this->describeOutcome($response->getResult()));
            } elseif ($response->isClientError() || $response->isServerError()) {
                $this->flashMessengerHelperService
                    ->addErrorMessage('Cache could not be cleared');
            }

            return $this->redirect()->toRoute(
                'admin-dashboard/admin-cache-clear'
            );
        }

        $view = new ViewModel([
            'form' => $form,
        ]);

        $view->setTemplate('pages/form');

        return $view;
    }

    /**
     * Expand the selected checkboxes into the namespace list the API command takes.
     *
     * @param string[] $selectedTypes
     * @return string[]
     */
    private function resolveNamespaces(array $selectedTypes): array
    {
        $namespaces = [];

        foreach ($selectedTypes as $selectedType) {
            $namespaces = array_merge(
                $namespaces,
                self::CACHE_NAMESPACE_MAP[$selectedType] ?? []
            );
        }

        return array_values(array_unique($namespaces));
    }

    /**
     * Report what the clear actually did.
     *
     * A 200 only says the command ran. Reporting the key count as well is what distinguishes a
     * clear that worked from one that matched nothing, which is otherwise invisible from here.
     *
     * @param array $result the command Result, as returned over the wire
     */
    private function describeOutcome(array $result): string
    {
        $keysDeleted = $result['flags'][Clear::RESULT_FLAG_KEYS_DELETED] ?? null;

        if ($keysDeleted === null) {
            return 'Cache cleared';
        }

        return sprintf(
            'Cache cleared - %d %s removed',
            $keysDeleted,
            $keysDeleted === 1 ? 'entry' : 'entries'
        );
    }
}
