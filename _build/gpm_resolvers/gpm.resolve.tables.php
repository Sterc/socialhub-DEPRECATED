<?php
/**
 * Resolve creating db tables
 *
 * THIS RESOLVER IS AUTOMATICALLY GENERATED, NO CHANGES WILL APPLY
 *
 * @package socialhub
 * @subpackage build
 */

if ($object->xpdo) {
    $modx =& $object->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $modelPath = $modx->getOption('socialhub.core_path', null, $modx->getOption('core_path') . 'components/socialhub/') . 'model/';
            $modx->addPackage('socialhub', $modelPath, 'modx_');

            $manager = $modx->getManager();

            $manager->createObjectContainer('SocialHubItem');

            break;
    }
}

return true;