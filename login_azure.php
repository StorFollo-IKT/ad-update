<?php

use League\OAuth2\Client\Token\AccessToken;

require 'vendor/autoload.php';
$ad_update = new ad_update();
$azure = $ad_update->azure;
$provider = $ad_update->azure->provider;

if (isset($_GET['code']) && isset($_SESSION['OAuth2.state']) && isset($_GET['state']))
{
    if ($_GET['state'] == $_SESSION['OAuth2.state']) {
        unset($_SESSION['OAuth2.state']);

        // Try to get an access token (using the authorization code grant)
        /** @var AccessToken $token */
        $token = $provider->getAccessToken('authorization_code', [
            'scope' => $provider->scope,
            'code'  => $_GET['code'],
        ]);

        // Verify token
        // Save it to local server session data
        $_SESSION['token'] = $token;
        header('Location: index.php');
    } else {
        echo 'Invalid state';
        return null;
    }
} else {
    // Check local server's session data for a token
    // and verify if still valid
    /** @var ?AccessToken $token */
    // token cached in session data, null if not found;

    if (!empty($_SESSION['token'])) {
        $token = $_SESSION['token'];
        $_SESSION['token'] = $azure->checkToken($_SESSION['token']);
        if(!empty($_SESSION['token']))
        {
            header('Location: index.php');
            die();
        }
        else
            $azure->redirectToLogin();
    } else {
        $azure->redirectToLogin();
    }
}
