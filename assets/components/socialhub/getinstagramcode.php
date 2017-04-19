<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = new modX();
$modx->initialize('web');

$modx->getService('error', 'error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');
$modx->lexicon->load('socialhub:default');

$socialHub = $modx->getService(
    'socialhub',
    'SocialHub',
    $modx->getOption(
        'socialhub.core_path',
        null,
        $modx->getOption('core_path') . 'components/socialhub/'
    ) . 'model/socialhub/'
);

if (isset($_GET['code']) && !empty($_GET['code'])) {
    $setting = $modx->getObject('modSystemSetting', 'socialhub.instagram_code');
    $setting->set('value', $_GET['code']);

    $corePath = $modx->getOption('socialhub.core_path', null, MODX_CORE_PATH . 'components/socialhub/');
    if ($setting->save()) {
        $cm = $modx->getCacheManager();
        $cm->refresh();

        $socialHub->log($modx->lexicon('socialhub.instagramcode_stored_success'), 'success');
    } else {
        $socialHub->log($modx->lexicon('socialhub.instagramcode_stored_failed'), 'error');
    }
} else {
    $socialHub->log($modx->lexicon('socialhub.instragram_error_nocode'), 'error');
}
