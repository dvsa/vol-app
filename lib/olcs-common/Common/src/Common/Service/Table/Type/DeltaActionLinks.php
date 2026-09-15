<?php

namespace Common\Service\Table\Type;

use Common\Util\Escape;

class DeltaActionLinks extends Selector
{
    #[\Override]
    public function render(array $data, array $column, string|null $formattedContent = null): string
    {
        $translator = $this->getTable()->getServiceLocator()->get('translator');
        $ariaDescription = $this->getAriaDescription($data, $column, $translator);

        if ($this->isRestoreVisible($data)) {
            $restore = $translator->translate(self::KEY_ACTION_LINKS_RESTORE);
            $restoreAria = $translator->translate(self::KEY_ACTION_LINKS_RESTORE_ARIA);
            $ariaLabel = sprintf(self::ARIA_LABEL_FORMAT, $restoreAria, $ariaDescription);

            return sprintf(
                '<button data-prevent-double-click="true" data-module="govuk-button" type="submit" class="right-aligned govuk-button govuk-button--secondary" ' .
                    'name="table[action][restore][%s]" aria-label="%s">%s</button>',
                Escape::htmlAttr($data['id']),
                Escape::htmlAttr($ariaLabel),
                Escape::htmlAttr($restore)
            );
        }

        if ($this->isRemoveVisible($data)) {
            $remove = $translator->translate(self::KEY_ACTION_LINKS_REMOVE);
            $removeAria = $translator->translate(self::KEY_ACTION_LINKS_REMOVE_ARIA);
            $ariaLabel = sprintf(self::ARIA_LABEL_FORMAT, $removeAria, $ariaDescription);

            return sprintf(
                '<button data-prevent-double-click="true" data-module="govuk-button" type="submit" class="right-aligned govuk-button govuk-button--secondary trigger-modal" ' .
                    'name="table[action][delete][%s]" aria-label="%s">%s</button>',
                Escape::htmlAttr($data['id']),
                Escape::htmlAttr($ariaLabel),
                Escape::htmlAttr($remove)
            );
        }

        return '';
    }

    /**
     * Is the Remove link visible
     *
     * @param array $data
     *
     * @return bool
     */
    private function isRemoveVisible($data)
    {
        return isset($data['action']) && !in_array($data['action'], ['C', 'D']);
    }

    /**
     * Is the Restore link visible
     *
     * @param array $data
     *
     * @return bool
     */
    private function isRestoreVisible($data)
    {
        // Default to checking "action" being C (current) or D (deleted)
        return isset($data['action']) && in_array($data['action'], ['C', 'D']);
    }
}
