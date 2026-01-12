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
    public function __construct(
        private readonly SectionRendererPluginManager $rendererManager
    ) {
    }

    /**
     * Render full letter preview HTML
     *
     * @param LetterInstance $letterInstance The letter instance with all relationships loaded
     * @param MasterTemplate|null $masterTemplate The master template to use (null for basic rendering)
     * @return string Complete HTML for the letter preview
     */
    public function renderPreview(LetterInstance $letterInstance, ?MasterTemplate $masterTemplate = null): string
    {
        // Render all sections
        $sectionsHtml = $this->renderSections($letterInstance);
        $issuesHtml = $this->renderIssues($letterInstance);
        $closingHtml = ''; // Closing sections would be rendered similarly when implemented

        // If no master template, return just the content
        if ($masterTemplate === null) {
            return $this->renderWithoutTemplate($sectionsHtml, $issuesHtml);
        }

        // Build placeholder values
        $placeholders = $this->buildPlaceholders($letterInstance, $sectionsHtml, $issuesHtml, $closingHtml);

        // Replace placeholders in template
        return $this->populateTemplate($masterTemplate->getTemplateContent(), $placeholders);
    }

    /**
     * Render content sections (intro, body sections)
     *
     * @param LetterInstance $letterInstance
     * @return string HTML for all content sections
     */
    private function renderSections(LetterInstance $letterInstance): string
    {
        $html = '';
        $sectionRenderer = $this->rendererManager->get('content-section');

        foreach ($letterInstance->getLetterInstanceSections() as $section) {
            $html .= $sectionRenderer->render($section);
        }

        return $html;
    }

    /**
     * Render issue sections grouped by Issue Type
     *
     * @param LetterInstance $letterInstance
     * @return string HTML for all issues grouped by type with headings
     */
    private function renderIssues(LetterInstance $letterInstance): string
    {
        $issueRenderer = $this->rendererManager->get('issue');

        // Group issues by Issue Type
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

        // Render grouped issues
        $html = '';
        foreach ($issuesByType as $typeData) {
            // Issue Type heading
            $html .= '<div class="issue-type-group">';
            $html .= '<h3 class="issue-type-heading">' . htmlspecialchars($typeData['name']) . '</h3>';

            // Render each issue under this type
            foreach ($typeData['issues'] as $issue) {
                $html .= $issueRenderer->render($issue);
            }

            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Render without a master template (fallback)
     *
     * @param string $sectionsHtml
     * @param string $issuesHtml
     * @return string Basic HTML structure
     */
    private function renderWithoutTemplate(string $sectionsHtml, string $issuesHtml): string
    {
        $html = '<div class="letter-content">';

        if (!empty($sectionsHtml)) {
            $html .= '<div class="sections">' . $sectionsHtml . '</div>';
        }

        if (!empty($issuesHtml)) {
            $html .= '<div class="issues">' . $issuesHtml . '</div>';
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
        string $closingHtml
    ): array {
        return [
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
            '{{APPENDICES_CONTENT}}' => '',
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
