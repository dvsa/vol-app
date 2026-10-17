<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TranslationKey;

use Dvsa\Olcs\Api\Domain\QueryHandler\TranslationKey\ById as TranslationKeyByIdHandler;
use Dvsa\Olcs\Api\Domain\Repository\TranslationKey as TranslationKeyRepo;
use Dvsa\Olcs\Transfer\Query\TranslationKey\ById as QryClass;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\AbstractQueryByIdHandlerTestCase;
use Dvsa\Olcs\Api\Entity\System\TranslationKey as TranslationKeyEntity;
use Dvsa\Olcs\Api\Entity\System\TranslationKeyText;
use Dvsa\Olcs\Api\Entity\System\Language;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * ById Test
 *
 * @author Andy Newtom <andy@vitri.ltd>
 */
final class ByIdTest extends AbstractQueryByIdHandlerTestCase
{
    protected $sutClass = TranslationKeyByIdHandler::class;
    protected $sutRepo = 'TranslationKey';
    protected $bundle = [
        'translationKeyTexts' => ['language'],
        'translationKeyCategoryLinks' => ['category', 'subCategory']
    ];
    protected $qryClass = QryClass::class;
    protected $repoClass = TranslationKeyRepo::class;
    protected $entityClass = TranslationKeyEntity::class;

    public function testPreviewConvertsExistingHtmlWithoutChangingStoredText(): void
    {
        $query = QryClass::create(['id' => 1, 'previewEditorJs' => true]);
        $this->assertTrue(method_exists($query, 'getPreviewEditorJs'));

        $language = m::mock(Language::class);
        $language->shouldReceive('getIsoCode')->andReturn('en_GB');
        $text = m::mock(TranslationKeyText::class);
        $text->shouldReceive('getLanguage')->andReturn($language);
        $text->shouldReceive('getContentJson')->andReturnNull();
        $text->shouldReceive('getTranslatedText')->andReturn('<p>Hello</p>');
        $entity = m::mock(TranslationKeyEntity::class);
        $entity->shouldReceive('getTranslationKeyTexts')->andReturn(new ArrayCollection([$text]));
        $entity->shouldReceive('serialize')->andReturn(['translationKey' => 'markup-example']);
        $this->repoMap[$this->sutRepo]->shouldReceive('fetchUsingId')->with($query)->andReturn($entity);

        $result = $this->sut->handleQuery($query)->serialize();

        $this->assertSame('Hello', $result['editorJsPreview']['en_GB']['blocks'][0]['data']['text']);
    }

    public function testPreviewRejectsPhpPartialContent(): void
    {
        $query = QryClass::create(['id' => 1, 'previewEditorJs' => true]);
        $language = m::mock(Language::class);
        $language->shouldReceive('getIsoCode')->andReturn('en_GB');
        $text = m::mock(TranslationKeyText::class);
        $text->shouldReceive('getLanguage')->andReturn($language);
        $text->shouldReceive('getContentJson')->andReturnNull();
        $text->shouldReceive('getTranslatedText')->andReturn('<p><?php echo $this->translate("nested"); ?></p>');
        $entity = m::mock(TranslationKeyEntity::class);
        $entity->shouldReceive('getTranslationKeyTexts')->andReturn(new ArrayCollection([$text]));
        $this->repoMap[$this->sutRepo]->shouldReceive('fetchUsingId')->with($query)->andReturn($entity);

        $this->expectException(ValidationException::class);
        $this->sut->handleQuery($query);
    }
}
