<?php

namespace Admin\Controller;

use Admin\Data\Mapper\Template as Mapper;
use Admin\Form\Model\Form\TemplateEdit;
use Admin\Form\Model\Form\TemplateFilter;
use Dvsa\Olcs\Transfer\Command\Template\SendTestEmail as SendTestEmailDto;
use Dvsa\Olcs\Transfer\Command\Template\UpdateTemplateSource as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Template\AvailableTemplateGroups as ListDto;
use Dvsa\Olcs\Transfer\Query\Template\PreviewTemplateSource;
use Dvsa\Olcs\Transfer\Query\Template\TemplateSource as ItemDto;
use Laminas\Form\Form;
use Laminas\Validator\EmailAddress as EmailValidator;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class TemplateController extends AbstractInternalController implements LeftViewProvider
{
    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
        'editAction' => ['forms/template-modal'],
    ];

    protected $tableName = 'admin-email-template-groups';
    protected $defaultTableSortField = 'description';
    protected $defaultTableOrderField = 'ASC';

    protected $listDto = ListDto::class;
    protected $itemDto = ItemDto::class;
    protected $updateCommand = UpdateDto::class;

    protected $navigationId = 'admin-dashboard/content-management';

    protected $filterForm = TemplateFilter::class;
    protected $formClass = TemplateEdit::class;
    protected $mapperClass = Mapper::class;

    /**
     * Get left view
     *
     * @return ViewModel
     */
    #[\Override]
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/content-management',
                'navigationTitle' => 'Templates'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Set filter choices (category + format) from querystring into List DTO params
     *
     * @param array $parameters parameters
     *
     * @return array
     */
    #[\Override]
    protected function modifyListQueryParameters($parameters)
    {
        $parameters['emailTemplateCategory'] = $this->params()->fromQuery('emailTemplateCategory');
        // VOL-7238: optional filter by format (html / plain / md). Empty string = no filter.
        $parameters['format'] = (string) $this->params()->fromQuery('format', '');
        return $parameters;
    }

    /**
     * Sets the edit modal title, source-label and AJAX preview URL.
     *
     * VOL-7238 additions: makes the title format-aware ("Edit Markdown template: …"), sets a
     * matching textarea label, populates the dynamic Html elements (sibling pills + md hint)
     * inside the form, and removes the "Send test via Notify" button when it doesn't apply
     * (non-md row, or env has no notify_test DSN configured).
     *
     * @return Form
     */
    public function alterFormForEdit(Form $form, array $formData)
    {
        $format = (string) ($formData['format'] ?? '');
        $formatLabel = match ($format) {
            'md' => 'Markdown',
            'html' => 'HTML',
            'plain' => 'Plain text',
            default => $format !== '' ? ucfirst($format) : 'Template',
        };

        $this->placeholder()->setPlaceholder(
            'contentTitle',
            sprintf('Edit %s template: %s', $formatLabel, $formData['description'] ?? '')
        );

        $form->get('source')->setLabel(sprintf('%s source', $formatLabel));

        // Sibling pills above the source textarea (VOL-7238). The currently-edited variant
        // is included in the pill list (with a "current" flag so it renders distinctly —
        // no anchor, bold outline), so admins see the full coverage at a glance and can
        // see which variant they're on. Sibling pills swap the modal via AJAX.
        $variants = $this->buildVariantsList(
            (int) ($formData['id'] ?? 0),
            (string) ($formData['locale'] ?? ''),
            (string) ($formData['format'] ?? ''),
            $formData['siblings'] ?? []
        );
        $form->get('templateSiblings')->setValue($this->renderSiblingPills($variants));

        // Format-specific help hint below the source textarea label. Empty for non-md rows.
        if ($format === 'md') {
            $form->get('mdHint')->setValue($this->renderMdHint());
        } else {
            $form->get('mdHint')->setValue('');
        }

        // Send-test button only applies to md rows in envs where notify_test DSN is set. Remove
        // the button outright otherwise so it never renders. The notifyTestEnabled flag comes
        // from the TemplateSource query handler (server-side authority).
        // The fieldset is composed under Form\Name "form-actions" (hyphenated), not the PHP
        // property name "formActions" — Laminas uses the declared name for child lookup.
        $sendTestAllowed = $format === 'md' && !empty($formData['notifyTestEnabled']);
        if ($form->has('form-actions')) {
            $formActions = $form->get('form-actions');
            if (!$sendTestAllowed && $formActions->has('sendTestViaNotify')) {
                $formActions->remove('sendTestViaNotify');
            }
            if ($sendTestAllowed && $formActions->has('sendTestViaNotify')) {
                $sendTestButton = $formActions->get('sendTestViaNotify');
                $sendTestButton->setAttribute(
                    'data-send-test-url',
                    $this->url()->fromRoute(
                        'admin-dashboard/admin-email-templates',
                        ['action' => 'sendTestEmail']
                    )
                );
                $sendTestButton->setAttribute(
                    'data-template-id',
                    (string) ($formData['id'] ?? '')
                );
                // Env-aware hint baked in by the TemplateSource query handler. JS reads it
                // and shows it next to the recipient input.
                $sendTestButton->setAttribute(
                    'data-send-test-hint',
                    (string) ($formData['notifyTestHint'] ?? ''),
                );
            }
        }

        $form->get('jsonUrl')
            ->setValue(
                $this->url()->fromRoute(
                    'admin-dashboard/admin-email-templates',
                    [
                        'action' => 'previewTemplate'
                    ]
                )
            );

        return $form;
    }

    /**
     * Build the full variants list for an edit-modal row: the current variant + its siblings,
     * sorted by locale then format so rendering is stable. The current variant carries a
     * `current => true` flag so the pill renderer can highlight it.
     *
     * @param array<int, array{id:int, locale:string, format:string}> $siblings
     * @return array<int, array{id:int, locale:string, format:string, current?:bool}>
     */
    private function buildVariantsList(int $currentId, string $currentLocale, string $currentFormat, array $siblings): array
    {
        $variants = $siblings;
        $variants[] = [
            'id' => $currentId,
            'locale' => $currentLocale,
            'format' => $currentFormat,
            'current' => true,
        ];

        usort($variants, static function (array $a, array $b): int {
            return [$a['locale'], $a['format']] <=> [$b['locale'], $b['format']];
        });

        return $variants;
    }

    /**
     * Renders the variant-pill row HTML for the edit modal. The current variant renders as a
     * non-clickable `<strong>` with a bold yellow outline; the others render as anchors that
     * AJAX-swap the modal contents (see template-modal.js, .js-template-sibling). Empty
     * string only when there are zero variants total (shouldn't happen for normal templates).
     *
     * @param array<int, array{id:int, locale:string, format:string, current?:bool}> $variants
     */
    private function renderSiblingPills(array $variants): string
    {
        if ($variants === []) {
            return '';
        }

        $colourMap = ['md' => 'green', 'html' => 'blue', 'plain' => 'grey'];

        $pills = '';
        foreach ($variants as $v) {
            $colour = $colourMap[$v['format'] ?? ''] ?? 'grey';
            $label = sprintf(
                '%s · %s',
                htmlspecialchars((string) ($v['locale'] ?? ''), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars(strtoupper((string) ($v['format'] ?? '')), ENT_QUOTES, 'UTF-8'),
            );

            if (!empty($v['current'])) {
                // Non-clickable, visually distinct (3px GOV.UK-yellow outline). No marker
                // text — keeping the label identical to the siblings so pill widths don't
                // shift when the modal swaps via AJAX.
                $pills .= sprintf(
                    '<strong class="govuk-tag govuk-tag--%s govuk-!-margin-right-1" '
                    . 'style="outline:3px solid #ffdd00; outline-offset:1px;" '
                    . 'aria-label="Currently editing">%s</strong> ',
                    htmlspecialchars($colour, ENT_QUOTES, 'UTF-8'),
                    $label,
                );
            } else {
                $url = $this->url()->fromRoute(
                    'admin-dashboard/admin-email-templates',
                    ['action' => 'edit', 'id' => (int) ($v['id'] ?? 0)]
                );
                $pills .= sprintf(
                    '<a class="govuk-tag govuk-tag--%s govuk-!-margin-right-1 js-template-sibling" href="%s">%s</a> ',
                    htmlspecialchars($colour, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($url, ENT_QUOTES, 'UTF-8'),
                    $label,
                );
            }
        }

        return '<p class="govuk-body govuk-!-margin-bottom-3">' . $pills . '</p>';
    }

    private function renderMdHint(): string
    {
        return '<p class="govuk-hint govuk-!-margin-bottom-3">'
            . 'This template is rendered by GOV.UK Notify. Use Markdown only — no raw HTML, no layout chrome.'
            . '</p>';
    }

    /**
     * AJAX endpoint for the "Send test via Notify" button on the edit modal (VOL-7238).
     * Dispatches the SendTestEmail command which renders the markdown body, builds a Mime\Email,
     * and hands it to NotifyTestMailer. Recipient is validated server-side.
     */
    public function sendTestEmailAction(): JsonModel
    {
        $postData = $this->getRequest()->getPost();

        $recipient = trim((string) ($postData['recipient'] ?? ''));
        $id = (int) ($postData['id'] ?? 0);

        $validator = new EmailValidator();
        if ($recipient === '' || !$validator->isValid($recipient)) {
            $this->getResponse()->setStatusCode(422);
            return new JsonModel(['error' => 'Please provide a valid recipient email address.']);
        }

        $response = $this->handleCommand(
            SendTestEmailDto::create([
                'id' => $id,
                'recipient' => $recipient,
            ])
        );

        if (!$response->isOk()) {
            $this->getResponse()->setStatusCode(422);
            return new JsonModel([
                'error' => implode(' ', (array) $response->getMessages()) ?: 'Test send failed.',
            ]);
        }

        return new JsonModel([
            'message' => sprintf('Test email sent to %s', $recipient),
        ]);
    }

    public function previewTemplateAction()
    {
        $request = $this->getRequest();
        $postData = $request->getPost();

        $response = $this->handleQuery(
            PreviewTemplateSource::create(
                [
                    'id' => $postData['id'],
                    'source' => $postData['source']
                ]
            )
        );

        $returnData = $response->getResult();

        if (isset($returnData['error'])) {
            $this->getResponse()->setStatusCode(422);
            unset($returnData['error']);
        }

        return new JsonModel($returnData);
    }
}
