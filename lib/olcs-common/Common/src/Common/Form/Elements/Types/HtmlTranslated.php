<?php

namespace Common\Form\Elements\Types;

class HtmlTranslated extends Html
{
    /**
     * @var array
     */
    protected $tokens = [];

    /**
     * Set the tokens to be translated
     *
     * @return HtmlTranslated
     */
    public function setTokens(array $tokens)
    {
        $this->tokens = $tokens;
        return $this;
    }

    /**
     * Get the tokens to be translated
     *
     * @return array
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    #[\Override]
    public function setOptions($options)
    {
        parent::setOptions($options);

        if (isset($options['tokens']) && is_array($options['tokens'])) {
            $this->setTokens($options['tokens']);
        }

        return $this;
    }
}
