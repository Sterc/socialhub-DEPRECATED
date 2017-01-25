<?php
/**
 * SocialStream Connector
 *
 * @package socialstream
 */
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$corePath = $modx->getOption('socialstream.core_path', null, $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/socialstream/');
$socialstream = $modx->getService(
    'socialstream',
    'SocialStream',
    $corePath . 'model/socialstream/',
    array(
        'core_path' => $corePath
    )
);

/* handle request */
$modx->request->handleRequest(
    array(
        'processors_path' => $socialstream->getOption('processorsPath', null, $corePath . 'processors/'),
        'location' => '',
    )
);