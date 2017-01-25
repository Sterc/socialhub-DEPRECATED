<?php
/**
 * Update an Item
 * 
 * @package socialstream
 * @subpackage processors
 */

class SocialStreamItemUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'SocialStreamItem';
    public $languageTopics = ['socialstream:default'];
    public $objectType = 'socialstream.item';

    // public function beforeSet() {
    //     $name = $this->getProperty('name');

    //     if (empty($name)) {
    //         $this->addFieldError('name',$this->modx->lexicon('socialstream.err.item_name_ns'));

    //     } elseif ($this->modx->getCount($this->classKey, array('name' => $name)) && ($this->object->name != $name)) {
    //         $this->addFieldError('name',$this->modx->lexicon('socialstream.err.item_name_ae'));
    //     }
    //     return parent::beforeSet();
    // }

}

return 'SocialStreamItemUpdateProcessor';
