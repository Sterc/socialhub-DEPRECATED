# socialFeed
MODX snippet for showing social feed (twitter, instagram, youtube, facebook)

To be able to use this snippet it is necessary to create API keys for the different social media accounts you want to use. 

## Requirements
    - PHP version 5.4.0 or higher (In order to use Facebook API);
    - Imagesloaded JQuery plugin (included in assets folder);
    - Masonry JQuery plugin (included in assets folder);

## Parameters

    &twitterToken: Twitter API Token
    &twitterTokenSecret: Twitter API Token secret
    &twitterConsKey: Twitter API consumer key
    &twitterConsSecret: Twitter API consumer secret
    &twitterTag: Twitter tag/username to search for (with # or @). Commadelimeted tags for OR operator use + delimiter as AND operator.
    &maxTweets: (optional) limit amount of tweets inside result
    
    &instaClientID: The client_id for the Instagram API
    &instagramTag: Instagram tag to search for (without #)
    
    &youtubeUser: Youtube username
    &youtubeAPIKey: Your Youtube API key
    &youtubePlaylistID: Youtube playlist ID (optional), if not set an additional request will be done to get the youtubeplaylistid based on the username
    
    &facebookAppID: Facebook APP ID
    &facebookAppSecret: Facebook APP secret
    &facebookPage: Facebook page name
    
    &showTwitter: 0 or 1. Include twitterfeed or not.
    &showInstagram: 0 or 1. Include instagramfeed or not.
    &showYoutube: 0 or 1. Include youtube feed or not.
    &showFacebook: 0 or 1. Include facebookfeed or not.
    &showWall: 0 or 1, defaults to 0. Display social feed wall. If is set, two jquery scripts will be added for the social wall functionality
    
    &limit: limit amount of total social feed
    &cache: 1 or 0, defaults to 0. Cache the socialfeed output
    &cachetime: cachetime in seconds.
    &cachekey: Key used for caching. Defaults to socialfeed (optional)
    &filters=`1` 1 or 0, defaults to 1 show socialaccounts filter below the widget
    
    &twitterTpl: chunk to use for Twitter
    &instagramTpl: chunk to use for Instagram
    &youtubeTpl: chunk to use for youtube
    &facebookTpl: chunk to use for facebook
    &outerClass: Class for the outer div
    &columnClass: Class for the output feed chunks
