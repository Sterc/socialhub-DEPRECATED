<?php
/**
 * Create an Item
 *
 * @package socialhub
 * @subpackage processors
 */
class SocialHubItemCreateProcessor extends modObjectCreateProcessor
{
    public $classKey = 'SocialHubItem';
    public $languageTopics = array('socialhub:default');
    public $objectType = 'socialhub.item';

    public function beforeSet()
    {
        $items = $this->modx->getCollection($this->classKey);

        $this->setProperty('position', count($items));

        return parent::beforeSet();
    }

    public function beforeSave()
    {
        $name = $this->getProperty('name');

        if (empty($name)) {
            $this->addFieldError('name', $this->modx->lexicon('socialhub.err.item_name_ns'));
        } elseif ($this->doesAlreadyExist(['name' => $name])) {
            $this->addFieldError('name', $this->modx->lexicon('socialhub.err.item_name_ae'));
        }
        return parent::beforeSave();
    }
}
return 'SocialHubItemCreateProcessor';
