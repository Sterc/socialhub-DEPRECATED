<?php
/**
 * Create an Item
 *
 * @package socialstream
 * @subpackage processors
 */
class SocialStreamItemCreateProcessor extends modObjectCreateProcessor
{
    public $classKey = 'SocialStreamItem';
    public $languageTopics = ['socialstream:default'];
    public $objectType = 'socialstream.item';

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
            $this->addFieldError('name', $this->modx->lexicon('socialstream.err.item_name_ns'));
        } elseif ($this->doesAlreadyExist(['name' => $name])) {
            $this->addFieldError('name', $this->modx->lexicon('socialstream.err.item_name_ae'));
        }
        return parent::beforeSave();
    }
}
return 'SocialStreamItemCreateProcessor';
