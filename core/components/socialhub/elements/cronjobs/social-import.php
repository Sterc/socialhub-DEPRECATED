<?php
use Facebook\FacebookSession;
use Facebook\FacebookRequest;

class SocialImport
{
    /**
     * @var null
     */
    private $modx = null;

    /**
     * @var string
     */
    private $corePath = '';

    /**
     * Holds Instagram Client ID.
     *
     * @var string
     */
    private $instagramClientId = '';

    /**
     * Holds Instagram access token.
     *
     * @var string
     */
    private $instagramAccessToken = '';

    /**
     * Holds default active value for all socialfeed items.
     *
     * @var int
     */
    private $activeDefaultValue = 0;

    /**
     * SocialImport constructor.
     */
    public function __construct()
    {
        $this->loadModx();



        if (!$this->loadSocialHub()) {
            return false;
        }

        $this->run();
    }

    /**
     * Load modx.
     */
    private function loadModx()
    {
        if (file_exists(
            dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . '/public_html/config.core.php'
        )) {
            $basePath = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))));
            require_once  $basePath . '/public_html/config.core.php';
        } elseif (dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/config.core.php') {
            require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/config.core.php';
        }

        require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

        $this->modx = new modX();
        $this->modx->initialize('web');

        $this->modx->getService('error', 'error.modError');
        $this->modx->setLogLevel(modX::LOG_LEVEL_INFO);
        $this->modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');

        $this->corePath = $this->modx->getOption('socialhub.core_path', null, MODX_CORE_PATH . 'components/socialhub/');
    }

    /**
     * Load SocialHub.
     *
     * @return bool
     */
    private function loadSocialHub()
    {
        $socialhub = $this->modx->getService(
            'socialhub',
            'SocialHub',
            $this->modx->getOption(
                'socialhub.core_path',
                null,
                $this->modx->getOption('core_path') . 'components/socialhub/'
            ) . 'model/socialhub/',
            array()
        );

        if (!($socialhub instanceof SocialHub)) {
            return false;
        }

        return true;
    }

    /**
     * Run SocialHub Import.
     */
    private function run()
    {
        $cm = $this->modx->getCacheManager();
        $cm->refresh(['system_settings' => array()]);

        if (!defined('INSTAGRAM_REDIRECT_URI')) {
            $path = $this->modx->getOption('socialhub.assets_path', null, MODX_ASSETS_PATH);
            $path = str_replace(MODX_BASE_PATH, '', $path);
            $url  = MODX_SITE_URL . $path;

            define('INSTAGRAM_REDIRECT_URI', $url . 'getinstagramcode.php');
        }

        $this->activeDefaultValue   = (int) $this->modx->getOption('socialhub.active_default');
        $this->instagramAccessToken = $this->modx->getOption('socialhub.instagram_accesstoken');

        $setAccessToken = true;
        if (!empty($this->instagramAccessToken)) {
            $instagramUserId = $this->modx->getOption('socialhub.instagram_user_id');

            if (!empty($instagramUserId)) {
                $url      = 'https://api.instagram.com/v1/users/self?access_token=' . $this->instagramAccessToken;
                $response = file_get_contents($url);

                if ($response) {
                    $setAccessToken = false;
                }
            }
        }

        /**
         * Check if Instagram is enabled, if so check for code.
         * Return if code has not been set yet, first retrieve code.
         */
        $instagramCode           = $this->modx->getOption('socialhub.instagram_code');
        $this->instagramClientId = $this->modx->getOption('socialhub.instagram_client_id');
        $instagramClientSecret   = $this->modx->getOption('socialhub.instagram_client_secret');

        if ($setAccessToken) {
            if (empty($instagramCode) &&
                !empty($this->instagramClientId)
            ) {
                $this->retrieveInstagramCode();
                /*
                 * Exit because this script will be called once again by instagramcode script.
                 */
                exit;
            }

            if (!empty($instagramCode) && !empty($this->instagramClientId) && !empty($instagramClientSecret)) {
                $fields = array(
                    'client_id'     => $this->instagramClientId,
                    'client_secret' => $instagramClientSecret,
                    'redirect_uri'  => INSTAGRAM_REDIRECT_URI,
                    'grant_type'    => 'authorization_code',
                    'code'          => $instagramCode
                );

                $url      = 'https://api.instagram.com/oauth/access_token';
                $response = $this->callApiPost($url, $fields);
                if (!isset($response['code']) && isset($response['access_token'])) {
                    $this->saveSystemSetting('socialhub.instagram_accesstoken', $response['access_token']);
                    $this->instagramAccessToken = $response['access_token'];

                    /* Code can only be used once, so clear code system setting */
                    $this->saveSystemSetting('socialhub.instagram_code', '');

                    $cm = $this->modx->getCacheManager();
                    $cm->refresh(['system_settings' => array()]);
                }
            }
        }

        $this->importTwitter();
        $this->importInstagram();
        $this->importYoutube();

        if (!version_compare(PHP_VERSION, '5.4.0', '<')) {
            $this->importFacebook();
        }

        echo 'Import finished.';
    }

    /**
     * Import Twitter feed.
     */
    private function importTwitter()
    {
        $twitterToken       = $this->modx->getOption('socialhub.twitter_token');
        $twitterTokenSecret = $this->modx->getOption('socialhub.twitter_token_secret');
        $twitterConsKey     = $this->modx->getOption('socialhub.twitter_consumer_key');
        $twitterConsSecret  = $this->modx->getOption('socialhub.twitter_consumer_secret');
        $twitterUsernames   = $this->modx->getOption('socialhub.twitter_username');
        $twitterUsernames   = explode(',', $twitterUsernames);
        $twitterSearchQuery = $this->modx->getOption('socialhub.twitter_search_query');

        if (!empty($twitterToken) &&
            !empty($twitterTokenSecret) &&
            !empty($twitterConsKey) &&
            !empty($twitterConsSecret)
        ) {
            require_once($this->corePath . 'lib/twitter/TwitterAPIExchange.php');

            $apiSettings = array(
                'oauth_access_token'        => $twitterToken,
                'oauth_access_token_secret' => $twitterTokenSecret,
                'consumer_key'              => $twitterConsKey,
                'consumer_secret'           => $twitterConsSecret
            );

            $twitter = new TwitterAPIExchange($apiSettings);
            if (!empty($twitterUsernames) && is_array($twitterUsernames)) {
                $timelineUrl           = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
                $timelineRequestMethod = 'GET';
                $timelineTweets        = array();

                foreach ($twitterUsernames as $twitterUsername) {
                    $timelineQuery = '?screen_name=' . $twitterUsername;
                    $response      = $twitter->setGetfield($timelineQuery)
                        ->buildOauth($timelineUrl, $timelineRequestMethod)
                        ->performRequest();

                    $newTimelineTweets = $this->modx->fromJSON($response);
                    $timelineTweets    = array_merge($timelineTweets, $newTimelineTweets);
                }

                if ($timelineTweets) {
                    foreach ($timelineTweets as $tweet) {
                        $sourceType = 'post';
                        if (isset($tweet['retweeted_status']) && is_array($tweet['retweeted_status'])) {
                            $sourceType = 'share';
                        }

                        if (isset($tweet['in_reply_to_screen_name']) && !empty($tweet['in_reply_to_screen_name'])) {
                            $sourceType = 'reply';
                        }

                        if (isset($tweet['id'])) {
                            $lang     = (isset($tweet['lang'])) ? $tweet['lang'] : 'nl';
                            $fullname = (isset($tweet['user']['name'])) ? $tweet['user']['name'] : '';
                            $date     = (isset($tweet['created_at'])) ? strtotime($tweet['created_at']) : time();
                            $avatar   = '';
                            $username = '';
                            $media    = '';

                            if (isset($tweet['user']['profile_image_url_https'])) {
                                $avatar = $tweet['user']['profile_image_url_https'];
                            }

                            if (isset($tweet['user']['screen_name'])) {
                                $username = utf8_decode($tweet['user']['screen_name']);
                            }

                            if (isset($tweet['entities']['media'][0]['media_url_https'])) {
                                $media = $tweet['entities']['media'][0]['media_url_https'];
                            }

                            $item = array(
                                'source'      => 'twitter',
                                'source_id'   => $tweet['id'],
                                'source_type' => $sourceType,
                                'language'    => $lang,
                                'avatar'      => $avatar,
                                'username'    => $username,
                                'fullname'    => $fullname,
                                'content'     => $this->formatContent($tweet),
                                'image'       => $media,
                                'link'        => 'https://twitter.com/sterc/status/' . $tweet['id'],
                                'date'        => $date,
                                'data'        => $tweet
                            );

                            /* Only import twitter posts having content. */
                            if (!empty($item['content'])) {
                                $this->handlePost('twitter', $tweet['id'], $item);
                            }
                        }
                    }
                }
            }

            if (!empty($twitterSearchQuery)) {
                $searchQuery = str_replace(',', ' OR ', $twitterSearchQuery);
                $searchQuery = urlencode($searchQuery);

                $searchUrl   = 'https://api.twitter.com/1.1/search/tweets.json';
                $searchQuery = '?q=' . $searchQuery;
                $searchRequestMethod = 'GET';
                $searchTweets = $this->modx->fromJSON(
                    $twitter->setGetfield($searchQuery)
                            ->buildOauth($searchUrl, $searchRequestMethod)
                            ->performRequest()
                );

                if ($searchTweets && isset($searchTweets['statuses'])) {
                    foreach ($searchTweets['statuses'] as $tweet) {
                        if (isset($tweet['id'])) {
                            $lang     = (isset($tweet['lang'])) ? $tweet['lang'] : 'nl';
                            $fullname = (isset($tweet['user']['name'])) ? utf8_decode($tweet['user']['name']) : '';
                            $date     = (isset($tweet['created_at'])) ? strtotime($tweet['created_at']) : time();
                            $avatar   = '';
                            $username = '';
                            $media    = '';

                            if (isset($tweet['user']['profile_image_url_https'])) {
                                $avatar = $tweet['user']['profile_image_url_https'];
                            }

                            if (isset($tweet['user']['screen_name'])) {
                                $username = utf8_decode($tweet['user']['screen_name']);
                            }

                            if (isset($tweet['entities']['media'][0]['media_url_https'])) {
                                $media = $tweet['entities']['media'][0]['media_url_https'];
                            }

                            $item = array(
                                'source'      => 'twitter',
                                'source_id'   => $tweet['id'],
                                'source_type' => 'mention',
                                'language'    => $lang,
                                'avatar'      => $avatar,
                                'username'    => $username,
                                'fullname'    => $fullname,
                                'content'     => $this->formatContent($tweet),
                                'image'       => $media,
                                'link'        => 'https://twitter.com/sterc/status/' . $tweet['id'],
                                'date'        => $date,
                                'data'        => $tweet
                            );

                            $this->handlePost('twitter', $tweet['id'], $item);
                        }
                    }
                }
            }
        }
    }

    /**
     * Import Instagram feed.
     */
    private function importInstagram()
    {
        if (!isset($this->instagramAccessToken) || empty($this->instagramAccessToken)) {
            return false;
        }

        if (!empty($this->instagramClientId)) {
            $instagramSearchQuery = $this->modx->getOption('socialhub.instagram_search_query');
            $instagramUsername    = $this->modx->getOption('socialhub.instagram_username');
            $tags                 = explode(',', $instagramSearchQuery);

            if (!empty($tags) &&
                !empty($instagramUsername)
            ) {
                foreach ($tags as $tag) {
                    $tag = 'dublin';
                    $tag                  = str_replace('#', '', $tag);
                    $instagramSearchUrl   = 'https://api.instagram.com/v1/tags/';
                    $instagramSearchUrl   .= $tag . '/media/recent';
                    $instagramSearchUrl   .= '?access_token=' . $this->instagramAccessToken;
                    $instagramSearchPosts = file_get_contents($instagramSearchUrl);

                    if ($instagramSearchPosts) {
                        $instagramSearchPosts = $this->modx->fromJSON($instagramSearchPosts);

                        if (isset($instagramSearchPosts['data'])) {
                            foreach ($instagramSearchPosts['data'] as $post) {
                                $this->importInstagramItem($post, $instagramUsername);
                            }
                        }
                    }
                }
            }

            $instagramUserId = $this->modx->getOption('socialhub.instagram_user_id');
            if (!empty($instagramUserId)) {
                $instagramSearchUrl = 'https://api.instagram.com/v1/users/';
                $instagramSearchUrl .= $instagramUserId . '/media/recent?access_token=' . $this->instagramAccessToken;
                $instagramUserPosts = file_get_contents($instagramSearchUrl);
                if ($instagramUserPosts) {
                    $instagramUserPosts = $this->modx->fromJSON($instagramUserPosts);

                    if (isset($instagramUserPosts['data'])) {
                        foreach ($instagramUserPosts['data'] as $post) {
                            $this->importInstagramItem($post);
                        }
                    }
                }
            }
        }
    }

    /**
     * Import Youtube feed.
     */
    private function importYoutube()
    {
        $youtubeChannelID = trim($this->modx->getOption('socialhub.youtube_channel_id'));
        $youtubeApiKey    = trim($this->modx->getOption('socialhub.youtube_api_key'));
        $youtubeApiUrl    = 'https://www.googleapis.com/youtube/v3/';

        if (!empty($youtubeChannelID) &&
            !empty($youtubeApiKey)
        ) {
            $youtubeUrl = $youtubeApiUrl . 'channels?part=contentDetails&id=';
            $youtubeUrl .= trim($youtubeChannelID) . '&key=' . $youtubeApiKey;
            $playlist   = file_get_contents($youtubeUrl);
            $playlist   = $this->modx->fromJSON($playlist);

            $channelUrl  = $youtubeApiUrl . 'channels?part=snippet&id=' . $youtubeChannelID . '&key=' . $youtubeApiKey;
            $channelInfo = file_get_contents($channelUrl);
            $channelInfo = $this->modx->fromJSON($channelInfo);

            $youtubeUsername = '';
            $avatar          = '';
            if (isset($channelInfo['items'][0]['snippet']['title'])) {
                $youtubeUsername = $channelInfo['items'][0]['snippet']['title'];
            }

            if (isset($channelInfo['items'][0]['snippet']['thumbnails']['high']['url'])) {
                $avatar = $channelInfo['items'][0]['snippet']['thumbnails']['high']['url'];
            }

            if (isset($playlist['items'][0]['contentDetails']['relatedPlaylists']['uploads'])) {
                $playlistId = $playlist['items'][0]['contentDetails']['relatedPlaylists']['uploads'];
                $postsUrl   = $youtubeApiUrl . 'playlistItems?part=snippet&playlistId=';
                $postsUrl   .= $playlistId . '&key=' . $youtubeApiKey;
                $posts      = file_get_contents($postsUrl);
                $posts      = $this->modx->fromJSON($posts);

                if ($posts['items']) {
                    foreach ($posts['items'] as $post) {
                        if (isset($post['snippet']['resourceId']['videoId'])) {
                            $media = '';
                            if (isset($post['snippet']['thumbnails']['high']['url'])) {
                                $media = $post['snippet']['thumbnails']['high']['url'];
                            }

                            $date = time();
                            if (isset($post['snippet']['publishedAt'])) {
                                $date = strtotime($post['snippet']['publishedAt']);
                            }

                            $link = 'https://www.youtube.com/watch?v=' . $post['snippet']['resourceId']['videoId'];
                            $item = array(
                                'source'      => 'youtube',
                                'source_id'   => $post['snippet']['resourceId']['videoId'],
                                'source_type' => 'post',
                                'language'    => 'nl',
                                'avatar'      => $avatar,
                                'username'    => $youtubeUsername,
                                'fullname'    => $youtubeUsername,
                                'content'     => $this->formatContent($post, 'youtube'),
                                'image'       => $media,
                                'link'        => $link,
                                'date'        => $date,
                                'data'        => $post
                            );

                            $this->handlePost('youtube', $post['snippet']['resourceId']['videoId'], $item);
                        }
                    }
                }
            }
        }
    }

    /**
     * Import Facebook feed.
     */
    private function importFacebook()
    {
        $facebookAppId     = $this->modx->getOption('socialhub.facebook_app_id');
        $facebookAppSecret = $this->modx->getOption('socialhub.facebook_app_secret');
        $facebookPage      = $this->modx->getOption('socialhub.facebook_page');

        if (!empty($facebookAppId) &&
            !empty($facebookAppSecret) &&
            !empty($facebookPage)
        ) {
            define('FACEBOOK_SDK_V4_SRC_DIR', $this->corePath . '/lib/facebook/src/Facebook/');
            require_once $this->corePath . '/lib/facebook/autoload.php';

            FacebookSession::setDefaultApplication($facebookAppId, $facebookAppSecret);
            $session = FacebookSession::newAppSession();

            try {
                $session->validate();
            } catch (FacebookRequestException $ex) {
                return;
            } catch (\Exception $ex) {
                return;
            }

            $request = new FacebookRequest(
                $session,
                'GET',
                '/' . $facebookPage . '/feed?fields=created_time,link,message,object_id,status_type,type,from'
            );

            $response    = $request->execute();
            $graphObject = $response->getGraphObject();
            $getPosts    = $graphObject->getProperty('data');
            $posts       = $getPosts->asArray();

            $requestPageInfo  = new FacebookRequest(
                $session,
                'GET',
                '/' . $facebookPage
            );

            $pageResponse = $requestPageInfo->execute();
            $pageInfo     = $pageResponse->getGraphObject();
            $username     = $pageInfo->getProperty('name');
            foreach ($posts as $post) {
                if (isset($post->id)) {
                    $plainName = '';
                    if (isset($post->from->name)) {
                        $plainName = $this->replaceCharacters($post->from->name);
                    }

                    $sourceType = 'mention';
                    if (strtolower(str_replace(' ', '', $plainName)) == strtolower($facebookPage)) {
                        $sourceType = 'post';
                    }

                    $avatar   = '';
                    $username = (isset($post->from->name)) ? $post->from->name : $username;
                    $fullname = (isset($post->from->name)) ? $post->from->name : $username;
                    $date     = (isset($post->created_time)) ? strtotime($post->created_time) : time();
                    $link     = (isset($post->link)) ? $post->link : 'https://www.facebook.com/' . $facebookPage;

                    $media = '';
                    if (isset($post->status_type) && $post->status_type == 'added_photos') {
                        $media = 'https://graph.facebook.com/' . $post->object_id . '/picture?type=normal';
                    }

                    $item = array(
                        'source'      => 'facebook',
                        'source_id'   => $post->id,
                        'source_type' => $sourceType,
                        'language'    => 'nl',
                        'avatar'      => $avatar,
                        'username'    => $username,
                        'fullname'    => $fullname,
                        'content'     => $this->formatContent($post, 'facebook'),
                        'image'       => $media,
                        'link'        => $link,
                        'date'        => $date,
                        'data'        => (array) $post
                    );

                    $this->handlePost('facebook', $post->id, $item);
                }
            }
        }
    }

    /**
     * Prepare Instagram Item response and handle the import.
     *
     * @param $post
     * @param $instagramUsername
     */
    private function importInstagramItem($post, $instagramUsername = '')
    {
        if (isset($post['id'])) {
            $avatar = '';
            if (isset($post['user']['profile_picture'])) {
                $avatar = $post['user']['profile_picture'];
            }

            $username = '';
            if (isset($post['user']['username'])) {
                $username = utf8_decode($post['user']['username']);
            }

            $fullname = '';
            if (isset($post['user']['full_name'])) {
                $fullname = utf8_decode($post['user']['full_name']);
            }

            $media = '';
            if (isset($post['images']['standard_resolution']['url'])) {
                $media = $post['images']['standard_resolution']['url'];
            }

            $link = '';
            if (isset($post['link'])) {
                $link = $post['link'];
            }

            $date = date('Y-m-d H:i:s');
            if (isset($post['created_time'])) {
                $date = date('Y-m-d H:i:s', $post['created_time']);
            }

            $sourceType = 'mention';
            if ($instagramUsername == $username) {
                $sourceType = 'post';
            }

            $item = array(
                'source'      => 'instagram',
                'source_id'   => $post['id'],
                'source_type' => $sourceType,
                'language'    => 'nl',
                'avatar'      => $avatar,
                'username'    => $username,
                'fullname'    => $fullname,
                'content'     => $this->formatContent($post, 'instagram'),
                'image'       => $media,
                'link'        => $link,
                'date'        => $date,
                'data'        => $post
            );

            $this->handlePost('instagram', $post['id'], $item);
        }
    }

    /*
     * Removes all emoji from content.
     */
    public static function removeEmoji($text)
    {
        $cleanText = $text;

        $replaceCharacters = '/([0-9|#][\x{20E3}])|[\x{00ae}|\x{00a9}|\x{203C}';
        $replaceCharacters .= '|\x{2047}|\x{2048}|\x{2049}|\x{3030}|\x{303D}|';
        $replaceCharacters .= '\x{2139}|\x{2122}|\x{3297}|\x{3299}][\x{FE00}-\x{FEFF}]?';
        $replaceCharacters .= '|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?';
        $replaceCharacters .= '|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?';
        $replaceCharacters .= '|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?';
        $replaceCharacters .= '|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u';

        $cleanText = preg_replace($replaceCharacters, '', $cleanText);

        return $cleanText;
    }

    /**
     * Handle and save feed.
     *
     * @param $source
     * @param $sourceId
     * @param $sourceData
     */
    private function handlePost($source, $sourceId, $sourceData)
    {
        if (isset($sourceData['content']) && $sourceData['content'] === null) {
            $sourceData['content'] = '';
        }

        $sourceData['content'] = $this->removeEmoji($sourceData['content']);

        $c = $this->modx->newQuery('SocialHubItem');
        $c->where(
            array(
                'SocialHubItem.source'    => $source,
                'SocialHubItem.source_id' => $sourceId,
            )
        );

        $sourceData['active'] = $this->activeDefaultValue;

        $result = $this->modx->getObject('SocialHubItem', $c);
        if ($result === null) {
            $result = $this->modx->newObject('SocialHubItem');
        } else {
            $sourceData['active'] = $result->get('active');
        }

        $result->fromArray($sourceData);
        $result->save();
    }

    /**
     * Format content for all feed types.
     *
     * @param        $item
     * @param string $source
     *
     * @return string
     */
    private function formatContent($item, $source = 'twitter')
    {
        $content = '';

        if ($source == 'twitter') {
            if (!isset($item['text'])) {
                return '';
            }
            $content = $item['text'];
        }

        if ($source == 'instagram') {
            if (!isset($item['caption']['text'])) {
                return '';
            }
            $content = $item['caption']['text'];
        }

        if ($source == 'facebook') {
            if (!isset($item->message)) {
                return '';
            }
            $content = $item->message;
        }

        if ($source == 'youtube') {
            if (!isset($item['snippet']['description'])) {
                return '';
            }
            $content = $item['snippet']['description'];
        }

        $content = htmlentities($content, ENT_NOQUOTES, 'UTF-8');
        $content = html_entity_decode($content);
        $content = str_replace('&hellip;', '', $content);

        if ($source == 'twitter') {
            $entities = null;
            if (is_array($item['entities']['urls'])) {
                foreach ($item['entities']['urls'] as $e) {
                    $tmp['start'] = $e['indices'][0];
                    $tmp['end']   = $e['indices'][1];

                    $e['display_url']   = htmlentities($e['display_url'], ENT_NOQUOTES, 'UTF-8');
                    $tmp['replacement'] = '<a href="'.$e['expanded_url'].'" target="_blank">'.$e['display_url'].'</a>';
                    $entities[] = $tmp;
                }
            }

            if (is_array($entities)) {
                usort($entities, function ($a, $b) {
                    return($b['start'] - $a['start']);
                });

                foreach ($entities as $item) {
                    $content = substr_replace(
                        $content,
                        $item['replacement'],
                        $item['start'],
                        $item['end'] - $item['start']
                    );
                }
            }
        }

        if ($source == 'instagram' || $source == 'facebook' || $source == 'youtube') {
            $content = preg_replace(
                '@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s\)])?)?)@',
                '<a href="$1" target="_blank">$1</a>',
                $content
            );
        }

        $content = preg_replace('/(^|\s)#(\w*[a-zA-Z_]+\w*)/u', '\1<span class="hashtag">#\2</span>', $content);
        if ($content == null) {
            $content = '';
        }

        return $content;
    }

    /**
     * Replace characters method.
     *
     * @param $string
     *
     * @return string
     */
    private function replaceCharacters($string)
    {
        $replacers = array(
            'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A',
            'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I',
            'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U',
            'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a',
            'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i',
            'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u',
            'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y'
        );

        return strtr($string, $replacers);
    }

    /**
     * Request Instagram Code in order to retrieve accesstoken.
     * Instagram will redirect to redirect_uri which will contain the code for retrieving access token.
     *
     * @return bool
     */
    private function retrieveInstagramCode()
    {
        /**
         * Code URL (manual in browser for testing purposes):
         * https://instagram.com/oauth/authorize/?client_id=[[+client_id]]&redirect_uri=[[+domain]]%2Fassets%2Fcomponents%2Fsocialhub%2Fgetinstagramcode.php&response_type=code&scope=public_content
         */
        $params = array(
            'client_id'     => $this->instagramClientId,
            'redirect_uri'  => INSTAGRAM_REDIRECT_URI,
            'response_type' => 'code',
            'scope'         => 'public_content'
        );

        $codeUrl = 'https://instagram.com/oauth/authorize/?' . http_build_query($params);
        $this->callApi($codeUrl);

        return true;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return bool
     */
    private function saveSystemSetting($key, $value)
    {
        $setting = $this->modx->getObject('modSystemSetting', $key);
        if (!$setting) {
            return false;
        }

        $setting->set('value', $value);
        $setting->save();

        return true;
    }

    /**
     * Call the API based on URL. Returns the response
     * json decoded.
     *
     * @since    1.0.0
     * @param    string  $url    A valid API url.
     *
     * @return   array   $response
     */
    private function callApi($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($curl, CURLOPT_TIMEOUT, 120);

        $response     = curl_exec($curl);
        $responseInfo = curl_getinfo($curl);
        $curlError    = curl_error($curl);
        curl_close($curl);

        /*
         * If curl failed somehow try file_get_contents.
         */
        if (!$response) {
            $response = file_get_contents($url);
        }

        /*
         * Fallback.
         */
        if (!$response) {
            for ($i = 1; $i <= 5; $i++) {
                if ($response) {
                    break;
                }

                $response = file_get_contents($url);
            }
        }

        return $this->modx->fromJSON($response);
    }

    /**
     * Call the API based on URL. Returns the response
     * json decoded.
     *
     * @since    1.0.0
     * @param    string  $url           A valid API url.
     * @param    array   $parameters    An array containing POST parameters.
     *
     * @return   array   $response
     */
    private function callApiPost($url, $parameters)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($parameters));
        curl_setopt($curl, CURLOPT_TIMEOUT, 120);

        $response     = curl_exec($curl);
        $responseInfo = curl_getinfo($curl);
        $curlError    = curl_error($curl);
        curl_close($curl);

        $responseArray = $this->modx->fromJSON($response);

        return $responseArray;
    }
}

new SocialImport();
