<?php
/**
 * Created by PhpStorm.
 * User: abi
 * Date: 22.02.2019
 * Time: 09:14
 */
require 'vendor/autoload.php';
$ad = new ad_update('edit');
$token = $ad->azure->checkToken($_SESSION['token']);

if(empty($token) || empty($_SESSION['manager']))
{
    header('Location: login_azure.php');
    die();
}

if(isset($_GET['manager']))
    $_SESSION['manager']=$_GET['manager'];

try {
    $manager = $_SESSION['manager'];
}
catch (Exception $e)
{
    die($ad->render('error.twig', array('error'=> $e->getMessage())));
}

$_SESSION['manager_dn']=$manager['dn'];
$title = sprintf('Ansatte registrert med %s som leder',$manager['displayname'][0]);

try {
    $users = $ad->ldap_query(sprintf('(manager=%s)',$manager['dn']), ['single_result'=>false, 'attributes'=>$ad->fetch_fields]);
}
catch (Exception $e)
{
    $error=$e->getMessage();
}

if(!empty($users))
{
    $ou_list = array();
    unset($users['count']);

    foreach ($users as $user)
    {
        $ou = preg_replace('/CN=.+?,(OU=.+)/', '$1', $user['dn']);
        $ou = str_replace('\\', '', $ou);
        if(!isset($ou_list[$ou]))
        {
            $ou_list[$ou]['name'] = preg_replace('/OU=(.+?),[A-Z]{2}=.+/','$1',$ou);
            $ou_list[$ou]['users'] = array();
        }
        $ou_list[$ou]['users'][] = $user;
    }

    echo $ad->render('user_list.twig', array(
        'title'=>$title,
        'ou_list'=>$ou_list,
        'field_names'=>$ad->field_names,
        'current_user'=>$_SESSION['current_user']['samaccountname'][0]));
}
else
{
    if ($ad->debug)
    {
        if (empty($error))
            $error = sprintf('Ingen brukere er registrert med %s som leder', $manager['displayname'][0]);
        echo $ad->render('error.twig', array('error' => $error, 'title' => 'Feil', 'trace' => $e->getTraceAsString()));
    } else
        header('Location: edit_user.php?user=' . $_SESSION['current_user']['samaccountname'][0]);
}