<?php

//  ---------------------------------------
//  This script connects with the Instagram API to grab recent images from an Instagram feed.
//  ---------------------------------------

//  If the access token expires, generate a new one by hitting this url in a browser:

//  https://yoursite.com/instagram.php?refresh=aee3316b-0c31-4b44-a957-60163b6ec08f

//  You will be taken to a page on Instagram that has you login as the Instagram user associated with the client_id below. You will then bounce back to this script and the token generation will take place.

//  ---------------------------------------
//  Set main storage location
//  ---------------------------------------

$uuid           = 'aee3316b-0c31-4b44-a957-60163b6ec08f';
$client_id      = '1364457088359982';
$client_secret  = '1bf2f50c839483f820054dbe0e2f1387';
$redirect_uri   = 'https://yoursite.com/instagram.php';
$cacheDir       = '../storage/runtime/instagram/';
$today          = date('Ymd');

//  ---------------------------------------
//  Are we getting a fresh token?
//  ---------------------------------------

if (! empty($_GET['refresh']) AND $_GET['refresh'] == $uuid)
{
    header('Location: ' . 'https://api.instagram.com/oauth/authorize?client_id=' . $client_id . '&redirect_uri=' . $redirect_uri . '&scope=user_profile,user_media&response_type=code');
    exit();
}

//  ---------------------------------------
//  Do we have a code?
//  ---------------------------------------

if (! empty($_GET['code']) AND strlen($_GET['code']) >= 238)
{
    //  ---------------------------------------
    //  Get initial access token from Instagram using an Instagram authorization code
    //  ---------------------------------------
    
    $tokenUrl   = 'https://api.instagram.com/oauth/access_token';

    $postFields   = array(
        'client_id'     => $client_id,
        'client_secret' => $client_secret,
        'grant_type'    => 'authorization_code',
        'redirect_uri'  => $redirect_uri,
        'code'          => $_GET['code']
    );

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

    $feed   = curl_exec($ch);

    if (curl_errno($ch))
    {
        $error = curl_error($ch);
        print_r($error);
        exit();
    }
    
    curl_close($ch);

    $json   = json_decode($feed);

    $accessToken    = $json->access_token;

    //  ---------------------------------------
    //  Exchange short lived access token for long lived token
    //  ---------------------------------------

    $tokenUrl   = 'https://graph.instagram.com/access_token?';

    $postFields   = array(
        'client_secret' => $client_secret,
        'grant_type'    => 'ig_exchange_token',
        'access_token'  => $accessToken
    );

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $tokenUrl . http_build_query($postFields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $feed   = curl_exec($ch);

    if (curl_errno($ch))
    {
        $error = curl_error($ch);
        print_r($error);
        exit();
    }

    curl_close($ch);

    $json   = json_decode($feed);

    $accessToken    = $json->access_token;

    //  ---------------------------------------
    //  Store the long lived token
    //  ---------------------------------------

    $tokenFile  = $cacheDir . 'tokens/access_token.txt';
    
    file_put_contents($tokenFile, $accessToken);
    
    echo 'A new long-lived access token has been successfully obtained from Instagram.';
    exit();
}

//  ---------------------------------------
//  Check for cached feed for today
//  ---------------------------------------

$cacheFile  = $cacheDir . 'cache/' . $today . '.json';

//  Do we have this in cache already?
if (($json = file_get_contents($cacheFile)) !== FALSE)
{
    echo file_get_contents($cacheFile);
    exit();
}

//  ---------------------------------------
//  Clear the cache
//  ---------------------------------------

array_map('unlink', glob($cacheDir . 'cache/*.json'));

//  ---------------------------------------
//  Get our stored access token
//  ---------------------------------------

$tokenFile  = $cacheDir . 'tokens/access_token.txt';

//  Do we have this in cache already?
if (($accessToken = file_get_contents($tokenFile)) === FALSE)
{
    echo 'No access token was found in local storage.';
    exit();
}

//  ---------------------------------------
//  Refresh our access token
//  ---------------------------------------

$tokenUrl   = 'https://graph.instagram.com/refresh_access_token?';

$postFields   = array(
    'grant_type'    => 'ig_refresh_token',
    'access_token'  => $accessToken
);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $tokenUrl . http_build_query($postFields));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$feed   = curl_exec($ch);

if (curl_errno($ch))
{
    $error = curl_error($ch);
    print_r($error);
    exit();
}

curl_close($ch);

$json   = json_decode($feed);

$accessToken    = $json->access_token;

//  ---------------------------------------
//  Store the long lived token
//  ---------------------------------------

$tokenFile  = $cacheDir . 'tokens/access_token.txt';

file_put_contents($tokenFile, $accessToken);

//  ---------------------------------------
//  Get fresh feed data
//  ---------------------------------------

$tokenUrl   = 'https://graph.instagram.com/me/media?';

$postFields   = array(
    'fields'    => 'id,caption,media_url,timestamp',
    'access_token'  => $accessToken
);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $tokenUrl . http_build_query($postFields));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$feed   = curl_exec($ch);

if (curl_errno($ch))
{
    $error = curl_error($ch);
    print_r($error);
    exit();
}

curl_close($ch);

//  ---------------------------------------
//  Cache the feed
//  ---------------------------------------

$cacheFile  = $cacheDir . 'cache/' . $today . '.json';

//  Do we have this in cache already?
if (file_put_contents($cacheFile, $feed) === FALSE)
{
    echo 'Failed to cache feed.';
    exit();
}

//  ---------------------------------------
//  Return the feed
//  ---------------------------------------

echo $feed;
exit();