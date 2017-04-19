<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = new modX();
$modx->initialize('web');

$modx->getService('error', 'error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');

if (isset($_GET['code']) && !empty($_GET['code'])) {
    $setting = $modx->getObject('modSystemSetting', 'socialhub.instagram_code');
    $setting->set('value', $_GET['code']);

    $corePath = $modx->getOption('socialhub.core_path', null, MODX_CORE_PATH . 'components/socialhub/');
    if ($setting->save()) {
        $cm = $modx->getCacheManager();
        $cm->refresh();

        echo $modx->lexicon('socialhub.instagramcode_stored_success');
    } else {
        echo $modx->lexicon('socialhub.instagramcode_stored_failed');
    }
} else {
    echo $modx->lexicon('socialhub.instragram_error_nocode');
}
