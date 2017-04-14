<?php
/**
 * SocialHub
 *
 * Snippet to show your social hub data.
 *
 * @author Johan van der Molen
 * @author Sander Drenth
 *
 * @copyright Copyright 2015, Sterc
 *
 * FILTERS:
 *
 * &filterSource - (Opt) Filter on post source for example: twitter, youtube
 * Possible values: twitter, youtube, instagram, facebook
 * Separate multiple values by comma. [default=NULL]
 *
 * &filterSourceId - (Opt) Filter on post source id for example: 123, 345
 * Separate multiple values by comma. [default=NULL]
 *
 * &filterSourceType - (Opt) Filter on post source type for example: post, mention
 * Possible values: post, mention, reply
 * Separate multiple values by comma. [default=NULL]
 *
 * &filterLanguage - (Opt) Filter on post language for example: nl, en
 * Separate multiple values by comma. [default=NULL]
 *
 * &filterUsername - (Opt) Filter on post username for example: johndoe, janedoe
 * Separate multiple values by comma. [default=NULL]
 *
 * &filterFullname - (Opt) Filter on post fullname for example: John Doe, Jane Doe
 * Separate multiple values by comma. [default=NULL]
 *
 * &filterImage - (Opt) Is the image required for the post or not. [default=0]
 * Possible values: 0 (not required), 1 (required)
 *
 *
 * ORDER:
 *
 * &sortBy - (Opt) Field to sort the posts by.
 * For example: username [default=date]
 *
 * &sortDir - (Opt) Order which to sort the posts by.
 * For example: ASC [default=DESC]
 *
 *
 * LIMIT:
 *
 * &limit - (Opt) Limit the amount of posts.
 * Must be a numeric value. [default=30]
 *
 *
 * FORMAT:
 *
 * &toJSON - (Opt) If you want the output as JSON.
 * Possible values: 0 or 1. [default=0]
 *
 *
 * UNREAD COUNT:
 *
 * &unreadCountPlaceholder - (Opt) The name of the placeholder of unread posts.
 * For example: socialhub.unread_posts [default=socialhub.unread_posts]
 *
 * &unreadCountKey - (Opt) The key of the unread count cookie data holder.
 * For example: socialhub_unread_data [default=socialhub_unread_data]
 *
 *
 * TEMPlATES:
 *
 * &twitterTpl - (Opt) The chunk that is used for a twitter post.
 * For example: yourTwitter [default=socialhubTwitter]
 *
 * &facebookTpl - (Opt) The chunk that is used for a facebook post.
 * For example: yourFacebook [default=socialhubFacebook]
 *
 * &instagramTpl - (Opt) The chunk that is used for a instagram post.
 * For example: yourInstagram [default=socialhubInstagram]
 *
 * &youtubeTpl - (Opt) The chunk that is used for a youtube post.
 * For example: yourYoutube [default=socialhubYoutube]
 *
 * &outerTpl - (Opt) The wrapper chunk use [[+output]] within.
 * For example: yourOuter [default=socialhubOuter]
 *
 *
 * OTHER:
 *
 * &cache - (Opt) If you want to cache the output.
 * Possible values: 0 or 1. [default=1]
 *
 * &cacheTime - (Opt) The cache time.
 * Must be a numeric value. [default=120]
 *
 * &cacheKey - (Opt) The cache key.
 * For example: homeSocialhubPosts [default=socialhubPosts]
 *
 * &toPlaceholder - (Opt) If u want the output in placeholder instead of a return.
 * For example: socialhub.output [default=NULL]
 *
 */

$socialhub = $modx->getService(
    'socialhub',
    'SocialHub',
    $modx->getOption(
        'socialhub.core_path',
        null,
        $modx->getOption('core_path') . 'components/socialhub/'
    ) . 'model/socialhub/',
    array()
);

if (!($socialhub instanceof SocialHub)) {
    return '';
}

$filterSource     = $modx->getOption('filterSource', $scriptProperties, null);
$filterSourceId   = $modx->getOption('filterSourceId', $scriptProperties, null);
$filterSourceType = $modx->getOption('filterSourceType', $scriptProperties, null);
$filterLanguage   = $modx->getOption('filterLanguage', $scriptProperties, null);
$filterUsername   = $modx->getOption('filterUsername', $scriptProperties, null);
$filterFullname   = $modx->getOption('filterFullname', $scriptProperties, null);
$filterImage      = $modx->getOption('filterImage', $scriptProperties, false);

$sortBy  = $modx->getOption('sortBy', $scriptProperties, 'date');
$sortDir = $modx->getOption('sortDir', $scriptProperties, 'DESC');
$limit   = (int) $modx->getOption('limit', $scriptProperties, 30);
$toJSON  = $modx->getOption('toJSON', $scriptProperties, false);

$unreadCountPlaceholder = $modx->getOption('unreadCountPlaceholder', $scriptProperties, 'socialhub.unread_posts');
$unreadCountKey         = $modx->getOption('unreadCountKey', $scriptProperties, 'socialhub_unread_data');

$twitterTpl   = $modx->getOption('twitterTpl', $scriptProperties, 'socialhubTwitter');
$facebookTpl  = $modx->getOption('facebookTpl', $scriptProperties, 'socialhubFacebook');
$instagramTpl = $modx->getOption('instagramTpl', $scriptProperties, 'socialhubInstagram');
$youtubeTpl   = $modx->getOption('youtubeTpl', $scriptProperties, 'socialhubYoutube');
$outerTpl     = $modx->getOption('outerTpl', $scriptProperties, 'socialhubOuter');

$cache         = $modx->getOption('cache', $scriptProperties, true);
$cacheTime     = (int) $modx->getOption('cacheTime', $scriptProperties, 120);
$cacheKey      = $modx->getOption('cacheKey', $scriptProperties, 'socialhubPosts');
$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, null);

$results = array();
if (!$cache || ($cache && $modx->cacheManager->get($cacheKey) == '')) {
    $where[]['active'] = 1;

    if (!empty($filterSource)) {
        $where[]['source:IN'] = explode(',', $filterSource);
    }

    if (!empty($filterSourceId)) {
        $where[]['source_id:IN'] = explode(',', $filterSourceId);
    }

    if (!empty($filterSourceType)) {
        $where[]['source_type:IN'] = explode(',', $filterSourceType);
    }

    if (!empty($filterLanguage)) {
        $where[]['language:IN'] = explode(',', $filterLanguage);
    }

    if (!empty($filterUsername)) {
        $where[]['username:IN'] = explode(',', $filterUsername);
    }

    if (!empty($filterFullname)) {
        $where[]['fullname:IN'] = explode(',', $filterFullname);
    }

    if ($filterImage) {
        $where[]['image:!='] = '';
    }

    $query = $modx->newQuery('SocialHubItem');
    $query->where($where);
    $query->limit($limit);
    $query->sortby($sortBy, $sortDir);
    $posts = $modx->getCollection('SocialHubItem', $query);

    $idx = 1;
    foreach ($posts as $post) {
        $results[] = array_merge($post->toArray(), array('idx' => $idx));

        $idx++;
    }

    if (count($posts) > 0) {
        $modx->cacheManager->set($cacheKey, $results, $cacheTime);
    }
} else {
    $results = $modx->cacheManager->get($cacheKey);
}

$output      = '';
$counter     = 0;
$unreadCount = 0;
foreach ($results as $result) {
    if ($counter < 1) {
        setcookie($unreadCountKey, $result['id'], time() + 60*60*24, '/');
    }

    if (isset($_COOKIE[$unreadCountKey]) &&
        $_COOKIE[$unreadCountKey] == $result['id']
    ) {
        $unreadCount = $counter;
    }

    $counter++;

    if (isset($result['source'])) {
        switch ($result['source']) {
            case 'twitter':
                $output .= $modx->getChunk($twitterTpl, $result);
                break;
            case 'facebook':
                $output .= $modx->getChunk($facebookTpl, $result);
                break;
            case 'instagram':
                $output .= $modx->getChunk($instagramTpl, $result);
                break;
            case 'youtube':
                $output .= $modx->getChunk($youtubeTpl, $result);
                break;
        }
    }
}

if (!empty($output)) {
    $output = $modx->getChunk($outerTpl, array('output' => $output));
} else {
    $output = '';
}

if (!$toJSON) {
    $modx->setPlaceholder($unreadCountPlaceholder, $unreadCount);

    if (!empty($toPlaceholder)) {
        $modx->setPlaceholder($toPlaceholder, $output);

        return true;
    }

    return $output;
} else {
    return json_encode(
        array(
            'html'   => $output,
            'unread' => $unreadCount
        )
    );
}