<?php
require 'ad_update.php';
if(isset($_GET['logout']))
	$_SESSION=array();
if(!empty($_SESSION['manager']))
{
	header('Location: employees.php');
	die();
}

require 'DOMDocument_createElement_simple.php';
$dom=new DOMDocumentCustom;
$dom->formatOutput=true;

if(isset($_POST['submit']))
{
    try
    {
        $adtools = new ad_update('auth');
        $adtools->connect_and_bind(null, $_POST['username'] . '@' . $adtools->config['domain'], $_POST['password']);
        $manager=$adtools->find_object($_POST['username'],false,'username',array('dn','displayName'));
        if($manager===false)
            $error=$adtools->error;
        else
        {
            $_SESSION['manager']=$_POST['username'];
            $_SESSION['manager_info']=$manager;
            header('Location: employees.php');
            die();
        }
    }
    catch (Exception $e)
    {
        $error=$e->getMessage();
    }
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Endre opplysninger</title>
	<link href="static/askommune.css" rel="stylesheet" type="text/css">
</head>


<?php
	$body=$dom->createElement_simple('body');
	$form=$dom->createElement_simple('form',$body,array('method'=>'post'));
	$dom->createElement_simple('h1',$form,false,'Endre opplysninger for ansatte');
	$p=$dom->createElement_simple('p',$form,false,'Logg på med det brukernavnet og passordet du har på PCen for å endre opplysninger for ansatte du er leder for.');	
	if(isset($error))
	{
		$error='Feil ved pålogging: '.$error;
		$dom->createElement_simple('p',$form,array('class'=>'error'),$error);
	}
	$p=$dom->createElement_simple('p',$form);
	$label=$dom->createElement_simple('label',$p,array('for'=>'username'),'Brukernavn: ');
	$input=$dom->createElement_simple('input',$p,array('type'=>'text','id'=>'username','name'=>'username'));
	$p=$dom->createElement_simple('p',$form);
	$label=$dom->createElement_simple('label',$p,array('for'=>'password'),'Passord: ');
	$input=$dom->createElement_simple('input',$p,array('type'=>'password','id'=>'password','name'=>'password'));
	$p=$dom->createElement_simple('p',$form);
	$input=$dom->createElement_simple('input',$p,array('type'=>'submit','name'=>'submit','value'=>'Logg på'));

	echo $dom->saveXML($body);

?>

</html>