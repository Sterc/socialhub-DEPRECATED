# Social Hub #

## Introduction ##
A MODX Extra which allows you to display social feed from Twitter, Facebook, Instagram and Youtube.

It retrieves the following content per Social Media channel:

* **Twitter:** Retrieve posts from your own user timeline and/or retrieve posts by specifying one or multiple search keywords.
* **Facebook:** Retrieve posts from your own user timeline.
* **Instagram:** Retrieve posts from your own user timeline and/or retrieve posts by specifying one or multiple search keywords. **Note**: In order to retrieve posts from other users by using the search query the app needs the public_scope permission: https://www.instagram.com/developer/authorization/
* **Youtube:** Retrieve videos from your own user channel.

### Features ###
* Stores social posts in the database for optimal performance.
* Allows you to allow/disallow individual posts to be displayed on the website with a MODX CMP.
* Comes with a snippet which accepts lots of options.

## Installation guide ##
* Install package via Package manager
* Fill in the API credentials for the Social Media accounts you want to import feed from
* Setup and run the SocialHub Cronjob located at /core/components/socialhub/elements/cronjobs/import.php

**Note: Instagram API Credentials**

In order to retrieve the Instagram Access Token you'll need to specify the Instagram API Code. You can retrieve this by visiting the following url:
https://instagram.com/oauth/authorize/?client_id=CLIENTID&redirect_uri=DOMAIN/assets/components/socialhub/getinstagramcode.php&response_type=code&scope=public_content

After the authorization you will be redirected to the specified redirect URI and the Instagram Code will be stored automatically for you.

### SocialHub Cronjob ###
The SocialHub cronjob imports the Social Feed from the social media accounts and store the data into the database.
Feed that has already been imported into the database will be updated.

The cronjob is located at:

/core/components/socialhub/elements/cronjobs/import.php

### SocialHub Snippet ###
The SocialHub snippet can be used to retrieve the social posts from the database.

#### Properties ####
| **Name**               | **Default**            | **Description**                                                                                                                      |
|------------------------|------------------------|--------------------------------------------------------------------------------------------------------------------------------------|
| filterSource           |                        | Comma delimited list of sources to filter on, for example: twitter, youtube. Possible values: twitter, youtube, instagram, facebook. |
| filterSourceId         |                        | Comma delimited list of source ID's to filter on, for example: 346054858127851521,346047103086235649.                                |
| filterSourceType       |                        | Comma delimited list of source types to filter on, for example: post, mention. Possible values: post, mention, reply.                |
| filterLanguage         |                        | Comma delimited list of languages to filter on, for example: nl,en.                                                                  |
| filterUsername         |                        | Comma delimited list of usernames to filter on, for example: johndoe, janedoe                                                        |
| filterFullname         |                        | Comma delimited list of fullnames to filter on, for example: johndoe, janedoe                                                        |
| filterImage            | 0                      | Only retrieve posts that have an image. Possible values are either 0 or 1.                                                           |                                                                           |
| sortBy                 | date                   | Field to sort the posts by.                                                                                                          |
| sortDir                | desc                   | Order which to sort the posts by.                                                                                                    |
| limit                  | 30                     | Limit the amount of posts.                                                                                                           |
| offset                 | 0                      | Retrieve posts by the defined offset.                                                                                                |
| toJSON                 | 0                      | Set to 1 to output the results to JSON.                                                                                              |
| twitterTpl             | socialhubTwitter       | The chunk that is used for a twitter post.                                                                                           |
| facebookTpl            | socialhubFacebook      | The chunk that is used for a facebook post.                                                                                          |
| instagramTpl           | socialhubInstagram     | The chunk that is used for a instagram post.                                                                                         |
| youtubeTpl             | socialhubYoutube       | The chunk that is used for a youtube post.                                                                                           |
| outerTpl               | socialhubOuter         | The wrapper chunk use [[+output]] within.                                                                                            |
| cache                  | 1                      | If you want to cache the output.                                                                                                     |
| cacheTime              | 120                    | The cache time.                                                                                                                      |
| cacheKey               | socialhubPosts         | The cache key.                                                                                                                       |
| toPlaceholder          |                        | If you want the output in placeholder provide the placeholder key.                                                                           |

## Requirements ##
* PHP 5.4 or higher is needed for the Facebook API. Else Facebook posts will not be imported.

