<?php
require_once dirname(__FILE__) . '/model/socialhub/socialhub.class.php';
/**
 * @package socialhub
 */
class IndexManagerController extends SocialHubBaseManagerController
{
    public static function getDefaultController()
    {
        return 'home';
    }
}

abstract class SocialHubBaseManagerController extends modExtraManagerController
{
    /** @var SocialHub $socialhub */
    public $socialhub;

    public function initialize()
    {
        $this->socialhub = new SocialHub($this->modx);

        $this->addCss($this->socialhub->getOption('cssUrl').'mgr.css');
        $this->addJavascript($this->socialhub->getOption('jsUrl').'mgr/socialhub.js');
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            SocialHub.config = '.$this->modx->toJSON($this->socialhub->options).';
            SocialHub.config.connector_url = "'.$this->socialhub->getOption('connectorUrl').'";
        });
        </script>');
        return parent::initialize();
    }

    public function getLanguageTopics()
    {
        return ['socialhub:default'];
    }

    public function checkPermissions()
    {
        return true;
    }
}
