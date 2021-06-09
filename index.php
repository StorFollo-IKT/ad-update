<?php

use storfollo\ad_update;

require 'vendor/autoload.php';
$ad_update = new \ad_update();
if(!empty($_SESSION['token']) && !empty($ad_update->azure->checkToken($_SESSION['token'])))
{
    if(isset($_GET['logout']))
    {
        $_SESSION = [];
        header('Location: ' . $ad_update->azure->provider->getLogoutUrl($ad_update->base_url));
        die();
    }
    $_SESSION['token'] = $ad_update->azure->checkToken($_SESSION['token']);

    try
    {
        $dn = $ad_update->azure->getLocalUser($_SESSION['token'], ['dn', 'displayName', 'samAccountName']);
    }
    catch (ad_update\UserNotFoundException $e)
    {
        die($ad_update->render('error.twig', ['title' => 'Feil: Finner ikke lokal bruker', 'error' => 'Finner ikke lokal bruker ' . $e->upn, 'trace' => $e->getTraceAsString()]));
    }

    $_SESSION['manager'] = $dn;
    $_SESSION['current_user'] = $dn;
    header('Location: user_list.php');
}
else
    header('Location: login_azure.php');