<?php
/**
 * Update an Item
 * 
 * @package socialhub
 * @subpackage processors
 */

class SocialHubItemUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'SocialHubItem';
    public $languageTopics = ['socialhub:default'];
    public $objectType = 'socialhub.item';

    // public function beforeSet() {
    //     $name = $this->getProperty('name');

    //     if (empty($name)) {
    //         $this->addFieldError('name',$this->modx->lexicon('socialhub.err.item_name_ns'));

    //     } elseif ($this->modx->getCount($this->classKey, array('name' => $name)) && ($this->object->name != $name)) {
    //         $this->addFieldError('name',$this->modx->lexicon('socialhub.err.item_name_ae'));
    //     }
    //     return parent::beforeSet();
    // }

}

return 'SocialHubItemUpdateProcessor';
