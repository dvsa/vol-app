<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Entity\Letter\MasterTemplate;
use Dvsa\Olcs\Api\Service\Letter\SectionRenderer\SectionRendererPluginManager;

/**
 * Service for rendering letter previews
 *
 * Orchestrates the rendering of all letter sections and populates
 * the master template with the rendered content.
 */
class LetterPreviewService
{
    private const string LOGO_TEMPLATE_SLUG = 'otclogo-letters';

    public function __construct(private readonly SectionRendererPluginManager $rendererManager, private $contentStore, private $docTemplateRepo, private readonly VolGrabReplacementService $volGrabReplacementService)
    {
    }

    /**
     * Render full letter preview HTML
     *
     * @param LetterInstance $letterInstance The letter instance with all relationships loaded
     * @param MasterTemplate|null $masterTemplate The master template to use (null for basic rendering)
     * @return string Complete HTML for the letter preview
     */
    public function renderPreview(LetterInstance $letterInstance, ?MasterTemplate $masterTemplate = null, bool $excludePdfAppendices = false): string
    {
        // Render assembled content (sections + issues interleaved by display order)
        $assembledHtml = $this->renderAssembledContent($letterInstance);
        $appendicesHtml = $this->renderAppendices($letterInstance, $excludePdfAppendices);

        $context = $this->buildVolGrabContext($letterInstance);

        // If no master template, return just the content
        if ($masterTemplate === null) {
            $html = $this->renderWithoutTemplate($assembledHtml, '', $appendicesHtml);
        } else {
            // Build placeholder values — assembled content goes into SECTIONS_CONTENT,
            // ISSUES_CONTENT is empty since issues are now inline within the assembly
            $placeholders = $this->buildPlaceholders($letterInstance, $assembledHtml, '', '', $appendicesHtml);

            $html = $this->populateTemplate($masterTemplate->getTemplateContent(), $placeholders);
        }

        return $this->volGrabReplacementService->replaceGrabsInHtml($html, $context);
    }

    /**
     * Render assembled content — sections and issues interleaved by display order.
     *
     * Iterates through letter instance sections in display order. When a section's
     * parent LetterSection has the reserved key __ISSUES__, the issues block is rendered
     * at that position. Otherwise, the section content is rendered normally.
     *
     * If no __ISSUES__ meta-section is present in the assembly, issues are appended
     * after all sections (backwards-compatible fallback).
     *
     * @param LetterInstance $letterInstance
     * @return string HTML for all assembled content
     */
    private function renderAssembledContent(LetterInstance $letterInstance): string
    {
        $html = '';
        $sectionRenderer = $this->rendererManager->get('content-section');
        $context = $this->buildVolGrabContext($letterInstance);
        $issuesRendered = false;

        foreach ($letterInstance->getLetterInstanceSections() as $section) {
            $sectionVersion = $section->getLetterSectionVersion();
            $parentSection = $sectionVersion?->getLetterSection();
            $sectionKey = $parentSection?->getSectionKey();

            if ($sectionKey === '__ISSUES__') {
                // Render issues at this position in the assembly
                $html .= $this->renderIssues($letterInstance);
                $issuesRendered = true;
            } else {
                $html .= $sectionRenderer->render($section, $context);
            }
        }

        // Fallback: if no __ISSUES__ meta-section was in the assembly, append issues at the end
        if (!$issuesRendered && $letterInstance->getLetterInstanceIssues()->count() > 0) {
            $html .= $this->renderIssues($letterInstance);
        }

        return $html;
    }

    /**
     * Render issue sections grouped by Issue Type. After each type's issues, render
     * the "What you need to do" block listing the type's to-dos — deduplication is
     * done at generate-time (one LetterInstanceTodo per unique to-do per letter,
     * attached to the FIRST issue that brought it), so the to-do naturally appears
     * under whichever issue-type that first issue belongs to (VOL-7280).
     *
     * @param LetterInstance $letterInstance
     * @return string HTML for all issues grouped by type with headings
     */
    private function renderIssues(LetterInstance $letterInstance): string
    {
        $issueRenderer = $this->rendererManager->get('issue');
        $todoRenderer = null;
        $context = $this->buildVolGrabContext($letterInstance);

        // Group issues by Issue Type, preserving display order via the order rows arrive in
        $issuesByType = [];
        foreach ($letterInstance->getLetterInstanceIssues() as $issue) {
            $issueVersion = $issue->getLetterIssueVersion();
            $issueType = $issueVersion ? $issueVersion->getLetterIssueType() : null;
            $typeName = $issueType ? ($issueType->getDescription() ?: $issueType->getName()) : 'Other Issues';
            $typeId = $issueType ? $issueType->getId() : 0;

            if (!isset($issuesByType[$typeId])) {
                $issuesByType[$typeId] = [
                    'name' => $typeName,
                    'issues' => [],
                ];
            }
            $issuesByType[$typeId]['issues'][] = $issue;
        }

        // Bucket each LetterInstanceTodo into its first-issue's type group (one bucket per type)
        $todosByType = [];
        foreach ($letterInstance->getLetterInstanceTodos() as $todo) {
            $instanceIssue = $todo->getLetterInstanceIssue();
            if ($instanceIssue === null) {
                continue;
            }
            $issueType = $instanceIssue->getLetterIssueVersion()?->getLetterIssueType();
            $typeId = $issueType ? $issueType->getId() : 0;
            $todosByType[$typeId][] = $todo;
        }

        // Render grouped issues, then the type's to-do block (if any)
        $html = '';
        foreach ($issuesByType as $typeId => $typeData) {
            $html .= '<div class="issue-type-group">';
            $html .= '<h3 class="issue-type-heading">' . htmlspecialchars((string) $typeData['name']) . '</h3>';

            foreach ($typeData['issues'] as $issue) {
                $html .= $issueRenderer->render($issue, $context);
            }

            if (!empty($todosByType[$typeId])) {
                if ($todoRenderer === null) {
                    $todoRenderer = $this->rendererManager->get('todo');
                }
                $items = '';
                foreach ($todosByType[$typeId] as $todo) {
                    $items .= $todoRenderer->render($todo, $context);
                }
                if ($items !== '') {
                    $html .= '<div class="issue-todos">';
                    $html .= '<h4 class="todo-heading" style="font-size:14pt;font-weight:bold;color:#000;">What you need to do</h4>';
                    $html .= '<ul class="todo-list">' . $items . '</ul>';
                    $html .= '</div>';
                }
            }

            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Render appendix sections
     *
     * @param LetterInstance $letterInstance
     * @return string HTML for all appendices
     */
    private function renderAppendices(LetterInstance $letterInstance, bool $excludePdf = false): string
    {
        $appendixRenderer = $this->rendererManager->get('appendix');
        $context = $this->buildVolGrabContext($letterInstance);
        $html = '';

        foreach ($letterInstance->getLetterInstanceAppendices() as $appendix) {
            if ($excludePdf && $appendix->isPdf()) {
                continue;
            }
            $html .= $appendixRenderer->render($appendix, $context);
        }

        if (!empty($html)) {
            $html = '<div class="appendices"><h2 class="appendices-heading">Appendices</h2>' . $html . '</div>';
        }
        return $html;
    }

    /**
     * Render without a master template (fallback)
     *
     * @param string $sectionsHtml
     * @param string $issuesHtml
     * @param string $appendicesHtml
     * @return string Basic HTML structure
     */
    private function renderWithoutTemplate(string $sectionsHtml, string $issuesHtml, string $appendicesHtml = ''): string
    {
        $html = '<div class="letter-content">';

        if (!empty($sectionsHtml)) {
            $html .= '<div class="sections">' . $sectionsHtml . '</div>';
        }

        if (!empty($issuesHtml)) {
            $html .= '<div class="issues">' . $issuesHtml . '</div>';
        }

        if (!empty($appendicesHtml)) {
            $html .= $appendicesHtml;
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Build placeholder values for the template
     *
     * @param LetterInstance $letterInstance
     * @param string $sectionsHtml
     * @param string $issuesHtml
     * @param string $closingHtml
     * @return array<string, string> Map of placeholder names to values
     */
    private function buildPlaceholders(
        LetterInstance $letterInstance,
        string $sectionsHtml,
        string $issuesHtml,
        string $closingHtml,
        string $appendicesHtml = ''
    ): array {
        return [
            '{{LOGO_IMAGE}}' => $this->buildLogoImage(),
            '{{LETTER_REFERENCE}}' => htmlspecialchars($letterInstance->getReference() ?? ''),
            '{{LETTER_DATE}}' => date('jS F Y'),
            '{{SECTIONS_CONTENT}}' => $sectionsHtml,
            '{{ISSUES_CONTENT}}' => $issuesHtml,
            '{{CLOSING_CONTENT}}' => $closingHtml,
            '{{CASEWORKER_NAME}}' => $this->buildCaseworkerName($letterInstance),
            '{{DVSA_ADDRESS}}' => $this->buildDvsaAddress(),
            '{{ENTITY_REFERENCE}}' => $this->buildEntityReference($letterInstance),
            '{{SALUTATION}}' => $this->buildSalutation($letterInstance),
            '{{SIGNATURE_NAME}}' => '', // To be populated from user/config
            '{{SIGNATURE_TITLE}}' => '', // To be populated from user/config
            '{{FOOTER_CONTENT}}' => '',
            '{{APPENDICES_CONTENT}}' => $appendicesHtml,
        ];
    }

    /**
     * Build caseworker name from the user who created the letter instance
     *
     * @param LetterInstance $letterInstance
     * @return string Caseworker name
     */
    private function buildCaseworkerName(LetterInstance $letterInstance): string
    {
        $createdBy = $letterInstance->getCreatedBy();
        if ($createdBy !== null) {
            $contactDetails = $createdBy->getContactDetails();
            if ($contactDetails !== null) {
                $person = $contactDetails->getPerson();
                if ($person !== null) {
                    return htmlspecialchars($person->getForename() . ' ' . $person->getFamilyName());
                }
            }
        }
        return 'Caseworker';
    }

    /**
     * Build static DVSA address
     *
     * @return string HTML formatted DVSA address
     */
    private function buildDvsaAddress(): string
    {
        return 'The Central Licensing Office<br>' .
               'Hillcrest House<br>' .
               '386 Harehills Lane<br>' .
               'Leeds<br>' .
               'LS9 6NF';
    }

    /**
     * Build entity reference (Application or Licence ID)
     *
     * @param LetterInstance $letterInstance
     * @return string Entity reference line
     */
    private function buildEntityReference(LetterInstance $letterInstance): string
    {
        $application = $letterInstance->getApplication();
        if ($application !== null) {
            return 'Application: ' . htmlspecialchars((string) $application->getId());
        }

        $licence = $letterInstance->getLicence();
        if ($licence !== null) {
            $licNo = $licence->getLicNo();
            return 'Licence: ' . htmlspecialchars($licNo ?? (string) $licence->getId());
        }

        return '';
    }

    /**
     * Build salutation
     *
     * @param LetterInstance $letterInstance
     * @return string Salutation text
     */
    private function buildSalutation(LetterInstance $letterInstance): string
    {
        $organisation = $letterInstance->getOrganisation();

        if ($organisation === null) {
            // Try to get organisation from licence
            $licence = $letterInstance->getLicence();
            if ($licence !== null) {
                $organisation = $licence->getOrganisation();
            }
        }

        if ($organisation !== null) {
            return '<p>Dear ' . htmlspecialchars($organisation->getName()) . ',</p>';
        }

        return '<p>Dear Sir or Madam,</p>';
    }

    /**
     * Build OTC logo as base64 data URI
     *
     * Fetches logo by template slug for reliability across environments
     *
     * @return string Base64 data URI or empty string if logo not found
     */
    private function buildLogoImage(): string
    {
        try {
            // Fetch DocTemplate by slug
            $docTemplate = $this->docTemplateRepo->fetchByTemplateSlug(self::LOGO_TEMPLATE_SLUG);

            if ($docTemplate === null) {
                return '';
            }

            // Get document identifier (path in content store)
            $document = $docTemplate->getDocument();
            if ($document === null) {
                return '';
            }

            $identifier = $document->getIdentifier();

            // Read from content store
            $file = $this->contentStore->read($identifier);
            if ($file === null) {
                return '';
            }

            $content = $file->getContent();
            $base64 = base64_encode((string) $content);

            return 'data:image/png;base64,' . $base64;
        } catch (\Exception) {
            // Log error but don't fail rendering
            return '';
        }
    }

    /**
     * Build context array for vol-grab replacement
     *
     * @param LetterInstance $letterInstance
     * @return array Context containing entity IDs for bookmark resolution
     */
    private function buildVolGrabContext(LetterInstance $letterInstance): array
    {
        return array_filter([
            'licence' => $letterInstance->getLicence()?->getId(),
            'application' => $letterInstance->getApplication()?->getId(),
            'user' => $letterInstance->getCreatedBy()?->getId(),
            'case' => $letterInstance->getCase()?->getId(),
            'busRegId' => $letterInstance->getBusReg()?->getId(),
            'organisation' => $letterInstance->getOrganisation()?->getId(),
        ]);
    }

    /**
     * Populate template with placeholder values
     *
     * @param string $template
     * @param array<string, string> $placeholders
     * @return string Populated template
     */
    private function populateTemplate(string $template, array $placeholders): string
    {
        return str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $template
        );
    }
}
