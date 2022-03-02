# PHP Instagram Feed With Token Refresh

This script grabs the most recent posts from the Instagram Basic Display API for a specified account so that you can display them on a website. It handles access token creation as well as refresh. 

## Overview

In June of 2020 Instagram released it's new Basic Display API. This took the place of the previous API. You'll know you were using the old one if your API url looked like this:

`https://api.instagram.com/v1/`

The new API endpoint looks like this:

`https://graph.instagram.com/`

The new Instagram API requires modern day OAuth access tokens. You can get a short-lived access token for one hour. But for practical purposes you need to get a long-lived token and refresh it prior to it expiring in 90 days.

This script handles the token management as well as feed fetch for you. The output of the script is the JSON response returned by the Instagram API. The script uses your server's file system to cache the feed for 24 hours. The script also keeps your access token fresh on a daily basis.

This script assumes that you will take care of the following:

- A web server running PHP
- FTP access to the server
- Ability to parse JSON using Javascript to populate HTML elements on a web page
- Completion of this Instagram tutorial: [Get Started With The Instagram Basic Display API](https://developers.facebook.com/docs/instagram-basic-display-api/getting-started)
- Set up something to ping the script each day to maintain fresh tokens and feed.

## Get Started

1. First grab this script and upload it to your web server.
2. Next, update the variables at the top of the script.
    - Get a fresh [UUID here](https://www.uuidgenerator.net/version4) and swap yours in. This is a little safeguard to help prevent people from triggering the token generation routine.
    - Change the client_id to the one Instagram provided in the [Get Started](https://developers.facebook.com/docs/instagram-basic-display-api/getting-started) tutorial.
    - Change the client_secret to that provided by the Instagram tutorial.
    - Set the redirect_uri to the location of this script on your server. For example, you might load the script to webroot and thereby have https://yoursite.com/instagram.php as your redirect URI.
    - Set the cache directory ($cacheDir) to a read/write directory on your web server that is above web root.
3. Start the access token process by loading your script in a web browser. If you loaded the script into webroot and your UUID was aee3316b-0c31-4b44-a957-60163b6ec08f then you would load this URL in a browser: https://yoursite.com/instagram.php?refresh=aee3316b-0c31-4b44-a957-60163b6ec08f
4. You will be redirected to Instagram to login and allow the appropriate app to access your Instagram feed. Any questions, refer to the [Instagram tutorial](https://developers.facebook.com/docs/instagram-basic-display-api/getting-started).

## Javascript Time!
Unless something goes pear shaped with the above steps, the script has now saved a long-lived token to your local file storage. It has also grabbed the most recent 25 Instagram posts from your feed and saved that as JSON to your file system.

You can now write some Javascript to fetch the Instagram JSON and iterate through it. You'll make an AJAX call to https://yoursite.com/instagram.php. You'll get cached results and loop through them to add images to your website. Take a look at the structure of the JSON. You'll loop through the `data` object in the feed. Each time the script runs, it will check for cache freshness. If the feed is more than a day old, the access token and feed will be refreshed.

Happy Coding!