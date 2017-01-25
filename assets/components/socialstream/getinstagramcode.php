<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = new modX();
$modx->initialize('web');

$modx->getService('error', 'error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');

if (isset($_GET['code']) && !empty($_GET['code'])) {
    $setting = $modx->getObject('modSystemSetting', 'socialstream.instagram_code');
    $setting->set('value', $_GET['code']);

    if ($setting->save()) {
        $cm = $modx->getCacheManager();
        $cm->refresh();
        /*
         * Has the code, now import feed.
         */
        require_once MODX_CORE_PATH . 'components/socialstream/elements/cronjobs/social-import.php';
    }
}