<?php
require 'vendor/autoload.php';
$ad_update = new ad_update();
if(!empty($_SESSION['token']) && !empty($ad_update->azure->checkToken($_SESSION['token'])))
{
    if(isset($_GET['logout']))
    {
        $_SESSION = [];
        header('Location: ' . $ad_update->azure->provider->getLogoutUrl('https://ad-update.as-admin.no'));
        die();
    }
    $_SESSION['token'] = $ad_update->azure->checkToken($_SESSION['token']);

    $ad_update->connect('edit');
    $dn = $ad_update->azure->getLocalUser($_SESSION['token'], ['dn', 'displayName']);
    $_SESSION['manager'] = $dn;
    header('Location: user_list.php');
}
else
    header('Location: login_azure.php');