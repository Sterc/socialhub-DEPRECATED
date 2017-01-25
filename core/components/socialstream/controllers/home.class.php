<?php
/**
 * Loads the home page.
 *
 * @package socialstream
 * @subpackage controllers
 */
class SocialStreamHomeManagerController extends SocialStreamBaseManagerController
{
    public function process(array $scriptProperties = [])
    {
    }

    public function getPageTitle()
    {
        return $this->modx->lexicon('socialstream');
    }

    public function loadCustomCssJs()
    {
        $this->addJavascript($this->socialstream->getOption('jsUrl').'mgr/widgets/items.grid.js');
        $this->addJavascript($this->socialstream->getOption('jsUrl').'mgr/widgets/home.panel.js');
        $this->addLastJavascript($this->socialstream->getOption('jsUrl').'mgr/sections/home.js');
    }

    /**
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->socialstream->getOption('templatesPath').'home.tpl';
    }
}
