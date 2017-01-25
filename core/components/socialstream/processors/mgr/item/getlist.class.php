<?php
/**
 * Get list Items
 *
 * @package socialstream
 * @subpackage processors
 */
class SocialStreamItemGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'SocialStreamItem';
    public $languageTopics = ['socialstream:default'];
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'socialstream.item';

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $c->where(['source' => $this->getProperty('source'),'active' => $this->getProperty('active')]);

        // $query = $this->getProperty('query');
        // if (!empty($query)) {

        //     $c->where(array(
        //             'name:LIKE' => '%'.$query.'%',
        //             'OR:description:LIKE' => '%'.$query.'%',
        //         ));
        // }

        $language = $this->getProperty('language');
        if (!empty($language)) {
            $c->where(
                [
                    'language' => $language
                ]
            );
        }

        return $c;
    }
}

return 'SocialStreamItemGetListProcessor';
