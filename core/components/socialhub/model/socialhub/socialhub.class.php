<?php
/**
 * The main SocialHub service class.
 *
 * @package socialhub
 *
 * @author Sterc <modx@sterc.nl>
 */
use Facebook\FacebookSession;
use Facebook\FacebookRequest;

class SocialHub
{
    /** @var modX|null  */
    public $modx = null;

    /** @var mixed|string  */
    public $namespace = 'socialhub';

    /** @var null  */
    public $cache = null;

    /** @var array  */
    public $options = array();

    /** @var mixed|string  */
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
     * Holds all messages that are logged in the script.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array          $logs
     */
    protected $logs = array();

    /** @var int  */
    public $totalCreated = 0;

    /** @var int  */
    public $totalUpdated = 0;

    /**
     * SocialHub constructor.
     *
     * @param modX  $modx
     * @param array $options
     */
    public function __construct(modX &$modx, array $options = array())
    {
        $this->modx =& $modx;
        $this->namespace = $this->getOption('namespace', $options, 'socialhub');

        $this->corePath = $this->getOption(
            'core_path',
            $options,
            $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/socialhub/'
        );

        $assetsPath = $this->getOption(
            'assets_path',
            $options,
            $this->modx->getOption('assets_path', null, MODX_ASSETS_PATH) . 'components/socialhub/'
        );

        $assetsUrl = $this->getOption(
            'assets_url',
            $options,
            $this->modx->getOption('assets_url', null, MODX_ASSETS_URL) . 'components/socialhub/'
        );

        /* loads some default paths for easier management */
        $this->options = array_merge(
            array(
                'namespace' => $this->namespace,
                'corePath' => $this->corePath,
                'modelPath' => $this->corePath . 'model/',
                'chunksPath' => $this->corePath . 'elements/chunks/',
                'snippetsPath' => $this->corePath . 'elements/snippets/',
                'templatesPath' => $this->corePath . 'templates/',
                'assetsPath' => $assetsPath,
                'assetsUrl' => $assetsUrl,
                'jsUrl' => $assetsUrl . 'js/',
                'cssUrl' => $assetsUrl . 'css/',
                'connectorUrl' => $assetsUrl . 'connector.php'
            ),
            $options
        );

        $this->modx->addPackage('socialhub', $this->getOption('modelPath'));
        $this->modx->lexicon->load('socialhub:default');
    }

    /**
     * Get a local configuration option or a namespaced system setting by key.
     *
     * @param string $key The option key to search for.
     * @param array $options An array of options that override local options.
     * @param mixed $default The default value returned if the option is not found locally or as a
     * namespaced system setting; by default this value is null.
     * @return mixed The option value or the default value specified.
     */
    public function getOption($key, $options = array(), $default = null)
    {
        $option = $default;
        if (!empty($key) && is_string($key)) {
            if ($options != null && array_key_exists($key, $options)) {
                $option = $options[$key];
            } elseif (array_key_exists($key, $this->options)) {
                $option = $this->options[$key];
            } elseif (array_key_exists("{$this->namespace}.{$key}", $this->modx->config)) {
                $option = $this->modx->getOption("{$this->namespace}.{$key}");
            }
        }
        return $option;
    }

    /**
     * Add a log message to render on screen but
     * it also can be mailed.
     *
     * Valid levels:
     * - info (blue)
     * - error (red)
     * - notice (yellow)
     * - success (green)
     *
     * @since    1.0.0
     * @param    string    $message    The message to log.
     * @param    string    $level      The log level.
     *
     * @return   bool      true
     */
    public function log($message, $level = 'info')
    {
        switch ($level) {
            case 'error':
                $prefix = 'ERROR::';
                $color = 'red';
                break;
            case 'notice':
                $prefix = 'NOTICE::';
                $color = 'yellow';
                break;
            case 'success':
                $prefix = 'SUCCESS::';
                $color = 'green';
                break;
            case 'info':
            default:
                $prefix = 'INFO::';
                $color = 'blue';
        }

        $logMessage = $this->colorize($prefix, $color). ' ' . $message;
        $htmlMessage = '<span style="color: ' . $color . '">' . $prefix . '</span> ' . $message;

        /*
         * We use the info level in all times because
         * we dont want this function to terminate the script.
         *
         * Rather use exit(); after the log function call once
         * you have an error.
         */
        if (XPDO_CLI_MODE) {
            $this->modx->log(MODX_LOG_LEVEL_INFO, $logMessage);
        } else {
            $this->modx->log(MODX_LOG_LEVEL_INFO, $message);
        }

        /*
         * logMessage has CLI markup
         * htmlMessage has HTML markup
         * cleanMessage has no markup
         */
        $this->logs['logMessage'][]   = $logMessage;
        $this->logs['htmlMessage'][]  = $htmlMessage;
        $this->logs['cleanMessage'][] = $prefix . ' ' . $message;

        return true;
    }

    /**
     * Give a string a color for CLI use.
     *
     * Valid colors:
     * - Red
     * - Green
     * - Yellow
     * - Blue
     *
     * @since    1.0.0
     * @param    string    $string    The string that needs the color.
     * @param    string    $color     The color for the string.
     *
     * @return   string    $string
     */
    protected function colorize($string, $color = 'white')
    {
        switch ($color) {
            case 'red':
                return "\033[31m" . $string . "\033[39m";
                break;
            case 'green':
                return "\033[32m" . $string . "\033[39m";
                break;
            case 'yellow':
                return "\033[33m" . $string . "\033[39m";
                break;
            case 'blue':
                return "\033[34m" . $string . "\033[39m";
                break;
            case 'white':
            default:
                return $string;
        }
    }

    /**
     * Run the SocialHub import.
     */
    public function runImport()
    {
        $cm = $this->modx->getCacheManager();
        $cm->refresh(['system_settings' => array()]);

        if (!defined('INSTAGRAM_REDIRECT_URI')) {
            $path = $this->modx->getOption('socialhub.assets_path', null, MODX_ASSETS_PATH);
            $path = str_replace(MODX_BASE_PATH, '', $path);

            if (strpos($path, 'components') === false) {
                $path = rtrim($path, '/') . '/components/socialhub/';
            }

            $url  = MODX_SITE_URL . $path;
            define('INSTAGRAM_REDIRECT_URI', $url . 'getinstagramcode.php');
        }

        $this->activeDefaultValue   = (int) $this->modx->getOption('socialhub.active_default');

        $clients = $this->modx->fromJson($this->modx->getOption('socialhub.instagram_json'));
        foreach ($clients as $key => $value){


            if(empty($value['token']) || !isset($value['token'])){
                $instagramCode           = $value['code'];
                $this->instagramClientId = $this->modx->getOption('socialhub.instagram_client_id');
                $instagramClientSecret   = $this->modx->getOption('socialhub.instagram_client_secret');

                if (!empty($instagramCode) && !empty($this->instagramClientId) && !empty($instagramClientSecret)) {
                    $fields = array(
                        'client_id'     => trim($this->instagramClientId),
                        'client_secret' => trim($instagramClientSecret),
                        'redirect_uri'  => INSTAGRAM_REDIRECT_URI . '?user='.trim($key),
                        'grant_type'    => 'authorization_code',
                        'code'          => $instagramCode
                    );

                    $url      = 'https://api.instagram.com/oauth/access_token';
                    $response = $this->callApiPost($url, $fields);

                    if($response['code'] == 400){
                        $clients[$key]['code'] = '';
                        $this->log($response['error_message'], 'error');
                        $this->log($fields['redirect_uri'], 'notice');


                    }

                    if (!isset($response['code']) && isset($response['access_token'])) {
                        $user = $response['user']['id'];
                        $clients[$user] = $clients[trim($key)];
                        unset($clients[trim($key)]);
                        $clients[$user]['token'] = $response['access_token'];
                        $clients[$user]['code'] = '';
                    }
                }
            }
        }

        /* Code can only be used once, so clear code system setting */
        $save = $this->saveSystemSetting('socialhub.instagram_json', json_encode($clients, JSON_UNESCAPED_UNICODE));
        //$cm = $this->modx->getCacheManager();
        //$cm->refresh(['system_settings' => ['key'=>'socialhub.instagram_json']]);
        //$clients = $this->modx->fromJson($this->modx->getOption('socialhub.instagram_json'));

        foreach ($clients as $key => $value) {
            if (!empty($value['token'])) {
                $this->importInstagram($value['token'], $value['tags'], $value['username']);
            } elseif (!empty($this->instagramClientId) && !empty($instagramClientSecret)) {
                $this->log('Could not import Instagram, please specify a valid Instagram code.', 'notice');
            }
        }
        /** Check if access token is already set, then import Instagram. */
        $publicToken = $this->modx->getOption('socialhub.instagram_accesstoken');
        $tags = $this->modx->getOption('socialhub.instagram_search_query');
        $this->importInstagram($publicToken, $tags, '' );

        $this->importTwitter();
        $this->importYoutube();



        $facebookAppId     = $this->modx->getOption('socialhub.facebook_app_id');
        $facebookAppSecret = $this->modx->getOption('socialhub.facebook_app_secret');

        /** Only import Facebook if PHP version is higher or equal to 5.4.0 */
        if (!version_compare(PHP_VERSION, '5.4.0', '<')) {
            $this->importFacebook($facebookAppId, $facebookAppSecret);
        } elseif (!empty($facebookAppId) && !empty($facebookAppSecret)) {
            $this->log(
                'Could not import Facebook posts because your PHP version does not
                match the minimal required PHP version.',
                'error'
            );
        }

        $this->log('Import finished succesfully', 'success');
        $this->log('Total created: ' . $this->totalCreated);
        $this->log('Total updated: ' . $this->totalUpdated);
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
    private function importInstagram($token, $tags, $instagramUsername)
    {
        if (!isset($token) || empty($token)) {
            return false;
        }

        if (!empty($tags) ) {
            $tags                 = explode(',', $tags);
            foreach ($tags as $tag) {
                $tag                  = trim(str_replace('#', '', $tag));
                if(!empty($tag)) {
                    $instagramSearchUrl = 'https://api.instagram.com/v1/tags/';
                    $instagramSearchUrl .= $tag . '/media/recent';
                    $instagramSearchUrl .= '?access_token=' . $token;
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
        }else {
            $instagramSearchUrl = 'https://api.instagram.com/v1/users/self/media/recent?access_token=' . $token;
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
        //     }
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
                                'language'    => 'en',
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
    private function importFacebook($facebookAppId, $facebookAppSecret)
    {
        $facebookPage = $this->modx->getOption('socialhub.facebook_page');

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
        $method = 'update';
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

            $method = 'create';
        } else {
            $sourceData['active'] = $result->get('active');
        }

        $result->fromArray($sourceData);
        if ($result->save()) {
            if ($method === 'create') {
                $this->totalCreated++;
            } else {
                $this->totalUpdated++;
            }

            $this->log('Storing post from: ' . $source);
        } else {
            $this->log('Failed storing post from source: ' . $source, 'error');
        }
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
