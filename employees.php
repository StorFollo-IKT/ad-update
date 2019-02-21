<?php
session_start();
session_name('ad-update');
if(empty($_SESSION['manager']))
{
	header('Location: index.php');
	die();
}
if(isset($_GET['manager']))
	$_SESSION['manager']=$_GET['manager'];
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Velg ansatt</title>
    <link href="static/brukerliste.css" rel="stylesheet" type="text/css">
    <link href="static/askommune.css" rel="stylesheet" type="text/css">
</head>

<?php
	require 'adtools/adtools.class.php';
	$adtools=new adtools('edit');
	require 'DOMDocument_createElement_simple.php';
	$dom=new DOMDocumentCustom;
	$body=$dom->createElement_simple('body');
	$dom->formatOutput=true;

	$manager=$adtools->query(sprintf('(samAccountName=%s)',$_SESSION['manager']),false,array('dn','displayName'));
	$_SESSION['manager_dn']=$manager['dn'];
	if($manager===false)
		die($adtools->error);
	else
	{
		$users=$adtools->query(sprintf('(manager=%s)',$manager['dn']),$base,array('title','telephoneNumber','samaccountname','mobile','employeeid','physicalDeliveryOfficeName','department','givenName','displayName'),false);
		//print_r($users);
		if($users===false)
			$error=$adtools->error;
		elseif(empty($users))
			$error=sprintf('Ingen brukere er registrert med %s som leder',$manager['displayname'][0]);
		
		if(isset($error))
			$dom->createElement_simple('p',$body,array('class'=>'error'),$error);
		else
		{
			unset($users['count']);

			$p=$dom->createElement_simple('h2',$body,false,sprintf('Ansatte registrert med %s som leder',$manager['displayname'][0]));
			foreach($users as $user)
			{
				$ou=preg_replace('/CN=.+?,(OU=.+)/','$1',$user['dn']);
				$ou=str_replace('\\','',$ou);
				//var_dump($user['dn']);
				$fields=array('displayname'=>'Navn','samaccountname'=>'Brukernavn','title'=>'Tittel','telephonenumber'=>'Telefon','mobile'=>'Mobil','physicaldeliveryofficename'=>'Lokasjon');
				if(!isset($tables[$ou]))
				{
					$ouname=preg_replace('/OU=(.+?),[A-Z]{2}=.+/','$1',$ou);
					$p_tables[$ou]=$dom->createElement_simple('p');
					$dom->createElement_simple('h3',$p_tables[$ou],false,$ouname);
					$tables[$ou]=$dom->createElement_simple('table',$p_tables[$ou],array('border'=>1));
					$tr=$dom->createElement_simple('tr',$tables[$ou]);
					//foreach(array('Navn','Tittel','Telefon','Mobil','Lokasjon') as $header)
					foreach($fields as $header)
					{
						$dom->createElement_simple('th',$tr,false,$header);
					}
					$dom->createElement_simple('td',$tr/*,array('colspan'=>'2')*/); //Empty field above links
				}
				$tr=$dom->createElement_simple('tr',$tables[$ou]);
				foreach(array_keys($fields) as $field)
				{
					if(empty($user[$field]))
						$user[$field][0]='';
					$dom->createElement_simple('td',$tr,array('class'=>$field),$user[$field][0]);
				}
				$td=$dom->createElement_simple('td',$tr);
				$dom->createElement_simple('a',$td,array('href'=>'edit_user.php?user='.$user['samaccountname'][0]),'Endre opplysninger');
				//$td=$dom->createElement_simple('td',$tr,false,'Tilbakestill passord');
				//$dom->createElement_simple('a',$td,array('href'=>'reset_password.php?user='.$user['samaccountname'][0]),'Tilbakestill passord');
				$ou_keys=array_keys($tables);
				sort($ou_keys);
				foreach($ou_keys as $ou)
				{
					$body->appendChild($p_tables[$ou]);
				}
			}
		}
	}
	$p=$dom->createElement_simple('p',$body,false,'Ansatte som har sluttet meldes ut med ');
	$dom->createElement_simple('a',$p,array('href'=>'https://opplaring.as-admin.no/fagsystemer/agresso/elektronisk-lonnsmelding/#Sluttmelding'),'elektronisk lønnsmelding i agresso.');
	$dom->createElement_simple('a',$body,array('href'=>'login.php?logout'),'Logg ut');
	echo $dom->saveXML($body);
?>
</html>