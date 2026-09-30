<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TranslationKey;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Service\EditorJs\EditorJsData;
use Dvsa\Olcs\Api\Service\EditorJs\HtmlToEditorJsConverter;
use Dvsa\Olcs\Api\Service\EditorJs\LongTextConverterService;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Retrieve a translation key by ID
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class ById extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'TranslationKey';
    protected $bundle = [
        'translationKeyTexts' => ['language'],
        'translationKeyCategoryLinks' => ['category', 'subCategory']
    ];

    #[\Override]
    public function handleQuery(QueryInterface $query)
    {
        $key = $this->getRepo()->fetchUsingId($query);
        if (!$query->getPreviewEditorJs()) {
            return $this->result($key, $this->bundle);
        }

        $preview = [];
        $htmlConverter = new HtmlToEditorJsConverter();
        $renderer = new LongTextConverterService();
        foreach ($key->getTranslationKeyTexts() as $text) {
            $locale = $text->getLanguage()->getIsoCode();
            if ($text->getContentJson() !== null) {
                $preview[$locale] = $text->getContentJson();
                continue;
            }

            $html = (string) $text->getTranslatedText();
            if (str_contains($html, '<?') || preg_match('/<\/?(?!p\b|h[1-6]\b|ul\b|ol\b|li\b|strong\b|b\b|em\b|i\b|a\b|span\b|u\b|br\b|abbr\b|div\b)[a-z][a-z0-9]*\b/i', $html)) {
                throw new ValidationException(['previewEditorJs' => 'This translation contains content the editor cannot convert']);
            }

            try {
                $document = EditorJsData::normalize(['blocks' => $htmlConverter->convert($html)]);
                $rendered = $renderer->convertJsonToHtml(json_encode($document, JSON_THROW_ON_ERROR));
            } catch (\Throwable $exception) {
                throw new ValidationException(['previewEditorJs' => 'This translation cannot be converted: ' . $exception->getMessage()]);
            }

            $plain = static fn (string $value): string => trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            if ($plain($html) !== $plain($rendered)) {
                throw new ValidationException(['previewEditorJs' => 'Conversion would change this translation']);
            }
            $preview[$locale] = $document;
        }

        return $this->result($key, $this->bundle, ['editorJsPreview' => $preview]);
    }
}
