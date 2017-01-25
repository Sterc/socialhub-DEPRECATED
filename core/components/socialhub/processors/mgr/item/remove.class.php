<?php
/**
 * Remove an Item.
 *
 * @package socialhub
 * @subpackage processors
 */
class SocialHubItemRemoveProcessor extends modObjectRemoveProcessor
{
    public $classKey = 'SocialHubItem';
    public $languageTopics = ['socialhub:default'];
    public $objectType = 'socialhub.item';
}

return 'SocialHubItemRemoveProcessor';
