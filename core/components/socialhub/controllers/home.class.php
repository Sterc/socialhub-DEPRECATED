<?php
require_once dirname(dirname(__FILE__)) . '/index.class.php';

/**
 * Loads the home page.
 *
 * @package socialhub
 * @subpackage controllers
 */
class SocialHubHomeManagerController extends SocialHubBaseManagerController
{
    public function process(array $scriptProperties = [])
    {
    }

    public function getPageTitle()
    {
        return $this->modx->lexicon('socialhub');
    }

    public function loadCustomCssJs()
    {
        $this->addJavascript($this->socialhub->getOption('jsUrl').'mgr/widgets/items.grid.js');
        $this->addJavascript($this->socialhub->getOption('jsUrl').'mgr/widgets/home.panel.js');
        $this->addLastJavascript($this->socialhub->getOption('jsUrl').'mgr/sections/home.js');
    }

    /**
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->socialhub->getOption('templatesPath').'home.tpl';
    }
}
