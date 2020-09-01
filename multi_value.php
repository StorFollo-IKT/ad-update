<?php
/**
 * Created by PhpStorm.
 * User: abi
 * Date: 30.01.2019
 * Time: 12:26
 */
require 'vendor/autoload.php';
$ad=new ad_update('edit');

if(isset($_GET['logout']))
    $_SESSION=array();
if(empty($_SESSION['manager']))
{
    header('Location: index.php');
    die();
}

$_GET['field'] = strtolower($_GET['field']);
if(array_search($_GET['field'], $ad->editable_fields)===false)
    die($ad->render('error.twig', array('error'=>'Ugyldig felt', 'title'=>'Feil')));
else
    $field=$_GET['field'];

try {
    $user = $ad->ldap_query(sprintf('(samAccountName=%s)',$_GET['user']), array(
        'attributes'=>array('dn','manager', $field)));
}
catch (Exception $e)
{
    echo $ad->render('error.twig', array('error'=>$e->getMessage(), 'title'=>'Feil'));
}


if($user['manager'][0]!=$_SESSION['manager_dn'])
    die($ad->render('error.twig', array('error'=>'Du er ikke leder for ansatt', 'title'=>'Feil')));

if(!isset($_GET['field']) || !isset($_GET['user']))
    die();


$count = $user[$field]['count'];
unset($user[$field]['count']);
$title = sprintf('Endre %s for %s', strtolower($ad->field_names[$field]), $_GET['user']);

if(!empty($_POST))
{
    $values = array();
    foreach($_POST['values'] as $key=>$value)
    {
        if(isset($_POST['remove'][$key])) {
            //ldap_mod_del($adtools->ad, $user['dn'], array($field=>$value));
            continue;
        }
        //TODO: Validate characters in value
        $values[]=$value;
    }
    $values = array_filter($values);
    if(empty($values))
        ldap_mod_del($ad->ad, $user['dn'], array($field=>array()));
    else
    {
        ldap_mod_replace($ad->ad, $user['dn'], array($field=>$values));
    }
    $user[$field] = $values;
    header('Location: edit_user.php?user='.$_GET['user']);
}

echo $ad->render('multivalue_edit.twig', array('values'=>$user[$field], 'title'=>$title, 'count'=>$count));
