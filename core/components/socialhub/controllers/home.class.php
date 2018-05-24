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
    public function process(array $scriptProperties = array())
    {
    }

    public function getPageTitle()
    {
        return $this->modx->lexicon('socialhub');
    }

    public function loadCustomCssJs()
    {
        $InstaAuth ='';
        if ($this->modx->user->get('sudo')) {
            $insta = $this->modx->fromJson($this->modx->getOption('socialhub.instagram_json'));
            $clientid = $this->modx->getOption('socialhub.instagram_client_id');
            $su = $this->modx->getOption('site_url');
            $out = [];
            foreach ($insta as $key => $value) {
                if (empty($value['token'])) {
                    $uri = $su . "assets/components/socialhub/getinstagramcode.php?user={$key}&response_type=code&scope=public_content";
                    $out[] = "<a target ='_blank' href='https://api.instagram.com/oauth/authorize/?client_id={$clientid}&redirect_uri={$uri}'>Get Instagram Token {$key}</a>";
                }
            }
            $InstaAuth = implode("<br>", $out);
        }
        $this->addHtml('<script>
            var InstaAuth = "'.$InstaAuth.'";
        </script>
        ');

        $this->addJavascript($this->socialhub->getOption('jsUrl') . 'mgr/widgets/items.grid.js');
        $this->addJavascript($this->socialhub->getOption('jsUrl') . 'mgr/widgets/home.panel.js');
        $this->addLastJavascript($this->socialhub->getOption('jsUrl') . 'mgr/sections/home.js');
    }

    /**
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->socialhub->getOption('templatesPath') . 'home.tpl';
    }
}
