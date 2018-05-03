<?php
/**
 * Default English Lexicon Entries for SocialHub
 *
 * @package socialhub
 * @subpackage lexicon
 */

$_lang['socialhub'] = 'SocialHub';

$_lang['socialhub.menu.socialhub'] = 'SocialHub';
$_lang['socialhub.menu.socialhub_desc'] = 'Manage your social feed.';

$_lang['socialhub.global.search'] = 'Search';

$_lang['socialhub.item.items'] = 'Social Feed';
$_lang['socialhub.item.intro_msg'] = 'Manage your social feed.';

$_lang['socialhub.approve'] = 'Approve';
$_lang['socialhub.deny'] = 'Deny';
$_lang['socialhub.msg.approve'] = 'After approval this feed can be shown on the website.';
$_lang['socialhub.msg.deny'] = 'After denying this feed cannot be shown on the website.';

$_lang['socialhub.status.approved'] = 'Approved';
$_lang['socialhub.status.denied']   = 'Denied';

$_lang['socialhub.item.name'] = 'Create Item';
$_lang['socialhub.item.user'] = 'User';
$_lang['socialhub.item.date'] = 'Datum';
$_lang['socialhub.item.content'] = 'Content';
$_lang['socialhub.item.description'] = 'Create Item';
$_lang['socialhub.item.position'] = 'Position';
$_lang['socialhub.item.create'] = 'Create Item';
$_lang['socialhub.item.update'] = 'Update Item';
$_lang['socialhub.item.remove'] = 'Remove Item';
$_lang['socialhub.item.remove_confirm'] = 'Are you sure you want to remove this Item?';

$_lang['socialhub.err.item_name_ae'] = 'An Item already exists with that name.';
$_lang['socialhub.err.item_nf'] = 'Item not found.';
$_lang['socialhub.err.item_name_ns'] = 'Name is not specified.';
$_lang['socialhub.err.item_remove'] = 'An error occurred while trying to remove the Item.';
$_lang['socialhub.err.item_save'] = 'An error occurred while trying to save the Item.';

$_lang['socialhub.instagramcode_stored_success'] = 'The Instagram code has been stored succesfully.';
$_lang['socialhub.instagramcode_stored_failed']  = 'Something went wrong while trying to save the Instagram code,
please try again.';
$_lang['socialhub.instragram_error_nocode'] = 'Error: missing code from Instagram API response.';
/**
 * System settings.
 */

/* Sterc Extra. */
$_lang['setting_socialhub.user_name']       = 'Your name';
$_lang['setting_socialhub.user_name_desc']  = 'Is used for the Sterc Extra\'s newsletter subscription. (optional)';
$_lang['setting_socialhub.user_email']      = 'Your emailaddress';
$_lang['setting_socialhub.user_email_desc'] = 'Is used for the Sterc Extra\'s newsletter subscription. (optional)';

/* General. */
$_lang['setting_socialhub.active_default']      = 'Import social feed as active by default?';
$_lang['setting_socialhub.active_default_desc'] = 'Import social feed either marked as active or not.';

/* Facebook. */
$_lang['setting_socialhub.facebook_app_id']          = 'Facebook App ID';
$_lang['setting_socialhub.facebook_app_id_desc']     = 'Enter Facebook App ID retrieved from
https://developers.facebook.com.';
$_lang['setting_socialhub.facebook_app_secret']      = 'Facebook App Secret';
$_lang['setting_socialhub.facebook_app_secret_desc'] = 'Enter Facebook App Secret retrieved from
https://developers.facebook.com.';
$_lang['setting_socialhub.facebook_page']            = 'Facebook page';
$_lang['setting_socialhub.facebook_page_desc']       = 'Enter Facebook page name, retrieved from Facebook page url.
For example: sterc.internet.marketing. Retrieved from: https://www.facebook.com/sterc.internet.marketing.';

/* Instagram. */
$_lang['setting_socialhub.instagram_accesstoken']        = 'Instagram Access Token';
$_lang['setting_socialhub.instagram_accesstoken_desc']   = 'The Instagram Access Token system setting will be
set automatically while the cronjob is running and does not have to be set manually.';
$_lang['setting_socialhub.instagram_client_id']          = 'Instagram Client ID';
$_lang['setting_socialhub.instagram_client_id_desc']     = 'Instagram Client ID from
https://www.instagram.com/developer/.';
$_lang['setting_socialhub.instagram_client_secret']      = 'Instagram Client Secret';
$_lang['setting_socialhub.instagram_client_secret_desc'] = 'Instagram Client Secret from
 https://www.instagram.com/developer/.';
//$_lang['setting_socialhub.instagram_code']               = 'Instagram Code';
$_lang['setting_socialhub.instagram_code_desc']          = 'The Instagram Code is necessary in order to retrieve the Instagram Access Token.
 You can get the Access Token code by visiting the following URL. Please replace the placeholders with the correct values.
 <a href="https://instagram.com/oauth/authorize/?client_id=CLIENTID
&redirect_uri=DOMAIN/assets/components/socialhub/getinstagramcode.php&response_type=code&scope=public_content"
 target="_blank">
 https://instagram.com/oauth/authorize/?client_id=CLIENTID
&redirect_uri=DOMAIN/assets/components/socialhub/getinstagramcode.php&response_type=code&scope=public_content</a>';
$_lang['setting_socialhub.instagram_search_query']       = 'Instagram Search Query';
$_lang['setting_socialhub.instagram_search_query_desc']  = 'A comma delimited list of tags to search for.';

$_lang['setting_socialhub.instagram_json']       = 'JSON String with userId, username, tags';
$_lang['setting_socialhub.instagram_json_desc']  = 'Example: {"userid": {"username": "__username__", "tags": "__tags__"}}';


//$_lang['setting_socialhub.instagram_user_id']            = 'Instagram User ID';
//$_lang['setting_socialhub.instagram_user_id_desc']       = 'Enter your Instagram User ID.';
//$_lang['setting_socialhub.instagram_username']           = 'Instagram Username';
//$_lang['setting_socialhub.instagram_username_desc']      = 'Enter your Instagram username.';

/* Twitter. */
$_lang['setting_socialhub.twitter_consumer_key']         = 'Twitter Consumer Key';
$_lang['setting_socialhub.twitter_consumer_key_desc']    = 'Twitter Consumer Key from https://apps.twitter.com/.';
$_lang['setting_socialhub.twitter_consumer_secret']      = 'Twitter Consumer Secret';
$_lang['setting_socialhub.twitter_consumer_secret_desc'] = 'Twitter Consumer Secret from https://apps.twitter.com/.';
$_lang['setting_socialhub.twitter_search_query']         = 'Twitter Search Query';
$_lang['setting_socialhub.twitter_search_query_desc']    = 'A comma delimited list of keywords to search for.';
$_lang['setting_socialhub.twitter_token']                = 'Twitter Token';
$_lang['setting_socialhub.twitter_token_desc']           = 'Twitter Token from https://apps.twitter.com/.';
$_lang['setting_socialhub.twitter_token_secret']         = 'Twitter Token Secret';
$_lang['setting_socialhub.twitter_token_secret_desc']    = 'Twitter Token Secret from https://apps.twitter.com/.';

/* Youtube. */
$_lang['setting_socialhub.youtube_api_key']         = 'Youtube API Key';
$_lang['setting_socialhub.youtube_api_key_desc']    = 'Youtube Data API V3 API key from
 https://console.developers.google.com/.';
$_lang['setting_socialhub.youtube_channel_id']      = 'Youtube Channel ID';
$_lang['setting_socialhub.youtube_channel_id_desc'] = 'Your channel ID can be found on:
 https://www.youtube.com/account_advanced';
