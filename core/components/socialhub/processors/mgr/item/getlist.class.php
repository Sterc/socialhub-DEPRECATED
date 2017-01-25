<?php
/**
 * Get list Items
 *
 * @package socialhub
 * @subpackage processors
 */
class SocialHubItemGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'SocialHubItem';
    public $languageTopics = array('socialhub:default');
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'socialhub.item';

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $c->where(array('source' => $this->getProperty('source'),'active' => $this->getProperty('active')));

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
                array (
                    'language' => $language
                )
            );
        }

        return $c;
    }
}

return 'SocialHubItemGetListProcessor';
