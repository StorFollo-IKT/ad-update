<?php
session_start();
session_name('ad-update');
if(empty($_SESSION['manager']))
{
	header('Location: login.php');
	die();
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Endre bruker</title>
<link href="askommune.css" rel="stylesheet" type="text/css">
</head>


<?php
	$fields=array('displayname'=>'Navn','samaccountname'=>'Brukernavn','title'=>'Tittel','telephonenumber'=>'Telefon','mobile'=>'Mobil','physicaldeliveryofficename'=>'Lokasjon','manager'=>'Leder');
	$editable_fields=array('title','telephonenumber','mobile','physicaldeliveryofficename');
	require 'adtools/adtools.class.php';
	$adtools=new adtools;
	require 'DOMDocument_createElement_simple.php';
	$dom=new DOMDocumentCustom;
	require '../../lib/logger_class.php';
	$logger=new logger('ad-update');
	$body=$dom->createElement_simple('body');
	$_GET['user']=preg_replace('/[^a-z0-9\-]/i','',$_GET['user']); //Remove invalid characters from user name
	//$adtools->dn='OU=Adminnett,DC=as-admin,DC=no';
	if($adtools->connect('edit')!==false)
	{
		//$user=$adtools->query(sprintf('(sAMAccountName=%s)',$_GET['user']),$adtools->dn,array_keys($fields));
		$user=$adtools->find_object($_GET['user'],false,'username',array_keys($fields));
		if($user===false)
			$dom->createElement_simple('p',$body,array('class'=>'error'),$adtools->error);
		elseif(empty($user))
			$dom->createElement_simple('p',$body,array('class'=>'error'),sprintf('Brukernavn %s finnes ikke',$_GET['user']));
	}
	else
		$dom->createElement_simple('p',$body,array('class'=>'error'),$adtools->error);
	
	if(!empty($user))
	{
		$dom->createElement_simple('h2',$body,false,'Endrer '.$user['displayname'][0]);


		if($user['manager'][0]!=$_SESSION['manager_dn'])
			$dom->createElement_simple('p',$body,array('class'=>'error'),'Du er ikke leder for ansatt');
		else
		{
			if(isset($_POST['submit']))
			{
				$logger->writelog($user['dn']);
				foreach($fields as $field=>$label)
				{
					if($_POST[$field]!=$_POST['original_'.$field])
					{
						if(empty($_POST[$field]))
							$_POST[$field]='[blank]';
						if(empty($_POST['original_'.$field]))
							$_POST['original_'.$field]='[blank]';

						$logstring=sprintf('%s er endret fra %s til %s',$label,$_POST['original_'.$field],$_POST[$field]);
						$logger->writelog($_SESSION['manager'].': '.$logstring);

						//$logger->writelog()
						//print_r(array($field=>$_POST[$field]));
						//var_dump($user['dn']);
						ldap_mod_replace($adtools->ad,$user['dn'],array($field=>$_POST[$field])); //Update AD
						$user[$field][0]=$_POST[$field]; //Show updated value in form
						$dom->createElement_simple('p',$body,false,$logstring);
					}
				}
			}

			$form=$dom->createElement_simple('form',$body,array('method'=>'post'));
			$table=$dom->createElement_simple('table',$form,array('border'=>1));
			$tr=$dom->createElement_simple('tr',$table);
			$dom->createElement_simple('th',$tr,false,'Felt');
			$dom->createElement_simple('th',$tr,false,'Verdi');
			//print_r($user);

			foreach($editable_fields as $field) //Show editable fields
			{
				$tr=$dom->createElement_simple('tr',$table);
				$dom->createElement_simple('td',$tr,false,$fields[$field]);
				$td=$dom->createElement_simple('td',$tr);
				if(empty($user[$field]))
					$user[$field][0]='';
				$input=$dom->createElement_simple('input',$td,array('type'=>'text','name'=>$field,'value'=>$user[$field][0]));
				$input=$dom->createElement_simple('input',$td,array('type'=>'hidden','name'=>'original_'.$field,'value'=>$user[$field][0]));
			}
			$dom->createElement_simple('input',$form,array('type'=>'submit','name'=>'submit','value'=>'Lagre endringer'));

			$dom->createElement_simple('p',$body,false,'Tittel skal være funksjonstittel i henhold til enhetlig navnestandard.');
			$dom->createElement_simple('p',$body,false,'Lokasjon skal være fysisk lokasjon, fortrinnsvis gateadresse med mindre lokasjonen har et annet kjent navn (skoler og barnehager).');
			$dom->createElement_simple('p',$body,false,'Hvis bruker kun har mobil må feltet for telefon inneholde ordet "Mobil" for at nummeret skal synes i internkatalogen. Er feltet for telefon tomt får brukeren ingen oppføring i internkatalogen.');
			$dom->createElement_simple('p',$body,false,'Navn endres i agresso.');
		}
	}
	$dom->createElement_simple('a',$body,array('href'=>'employees.php'),'Endre annen bruker');
	$dom->createElement_simple('br',$body);
	$dom->createElement_simple('a',$body,array('href'=>'login.php?logout'),'Logg ut');
	echo $dom->saveXML($body);
	?>

</html>