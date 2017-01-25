<?php
require_once dirname(__FILE__) . '/model/socialstream/socialstream.class.php';
/**
 * @package socialstream
 */
class IndexManagerController extends SocialStreamBaseManagerController
{
    public static function getDefaultController()
    {
        return 'home';
    }
}

abstract class SocialStreamBaseManagerController extends modExtraManagerController
{
    /** @var SocialStream $socialstream */
    public $socialstream;

    public function initialize()
    {
        $this->socialstream = new SocialStream($this->modx);

        $this->addCss($this->socialstream->getOption('cssUrl').'mgr.css');
        $this->addJavascript($this->socialstream->getOption('jsUrl').'mgr/socialstream.js');
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            SocialStream.config = '.$this->modx->toJSON($this->socialstream->options).';
            SocialStream.config.connector_url = "'.$this->socialstream->getOption('connectorUrl').'";
        });
        </script>');
        return parent::initialize();
    }

    public function getLanguageTopics()
    {
        return ['socialstream:default'];
    }

    public function checkPermissions()
    {
        return true;
    }
}
