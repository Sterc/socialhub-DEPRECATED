<?php
/**
 * Remove an Item.
 *
 * @package socialstream
 * @subpackage processors
 */
class SocialStreamItemRemoveProcessor extends modObjectRemoveProcessor
{
    public $classKey = 'SocialStreamItem';
    public $languageTopics = ['socialstream:default'];
    public $objectType = 'socialstream.item';
}

return 'SocialStreamItemRemoveProcessor';
