<?php
/**
 * @author Sterc <modx@sterc.nl>
 *
 * Cronjob to handle import of social feed.
 */
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config/config.inc.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = new modX();
$modx->initialize('web');

$modx->getService('error', 'error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');

$socialHub = $modx->getService(
    'socialhub',
    'SocialHub',
    $modx->getOption(
        'socialhub.core_path',
        null,
        $modx->getOption('core_path') . 'components/socialhub/'
    ) . 'model/socialhub/'
);

$socialHub->runImport();
