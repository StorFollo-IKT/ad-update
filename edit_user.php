<?php
require 'vendor/autoload.php';
$ad=new ad_update;
if(empty($_SESSION['manager']))
{
	header('Location: index.php');
	die();
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Endre bruker</title>
<link href="static/askommune.css" rel="stylesheet" type="text/css">
</head>


<?php
	require 'DOMDocument_createElement_simple.php';
	$dom=new DOMDocumentCustom;
	$body=$dom->createElement_simple('body');
	$_GET['user']=preg_replace('/[^a-z0-9\-]/i','',$_GET['user']); //Remove invalid characters from user name

    try {
        $ad->connect('edit');
        $user=$ad->find_object($_GET['user'],false,'username',$ad->fetch_fields);
    }
    catch (Exception $e)
    {
        $dom->createElement_simple('p',$body,array('class'=>'error'),$e->getMessage());
    }

    if(empty($user))
        $dom->createElement_simple('p',$body,array('class'=>'error'),sprintf('Brukernavn %s finnes ikke',$_GET['user']));
	else
	{
		$dom->createElement_simple('h2',$body,false,'Endrer '.$user['displayname'][0]);

		if(!$ad->canEdit($user))
			$dom->createElement_simple('p',$body,array('class'=>'error'),'Du er ikke leder for ansatt');
		else
		{
			if(isset($_POST['submit']))
			{
                $update_fields = array_intersect(array_keys($_POST), $ad->editable_fields);
				$ad->log->writelog($user['dn']);
				foreach($update_fields as $field)
				{
					if($_POST[$field]!=$_POST['original_'.$field])
					{
						if(empty($_POST[$field]))
                        {
                            $ad->log->writelog('Remove '.$field);
                            $logstring_to = '[blank]';
                            ldap_mod_replace($ad->ad,$user['dn'],array($field=>array())); //Update AD
                        }
                        else
                        {
                            $logstring_to = $_POST[$field];
                            ldap_mod_replace($ad->ad,$user['dn'],array($field=>$_POST[$field])); //Update AD
                        }

                        if(empty($_POST['original_'.$field]))
                            $logstring_from ='[blank]';
                        else
                            $logstring_from = $_POST['original_'.$field];

                        $label = $ad->field_names[$field];
                        $logstring=sprintf('%s er endret fra %s til %s',$label, $logstring_from, $logstring_to);
                        $ad->log->writelog($_SESSION['manager'].': '.$logstring);

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

			foreach($ad->editable_fields as $field) //Show editable fields
			{
				$tr=$dom->createElement_simple('tr',$table);
				$dom->createElement_simple('td',$tr,false,$ad->field_names[$field]);
				$td=$dom->createElement_simple('td',$tr);
				if(empty($user[$field]))
					$user[$field][0]='';
				if((isset($user[$field]['count']) && $user[$field]['count']>1) || isset($ad->multi_value_fields[$field]))
				{
                    unset($user[$field]['count']);
                    foreach($user[$field] as $value)
                    {
                        $dom->createElement_simple('span', $td, false, $value);
                        $dom->createElement_simple('br', $td);
                    }
                    $dom->createElement_simple('a', $td, array('href'=>sprintf('multi_value.php?user=%s&field=%s', $user['samaccountname'][0], $field)), 'Endre');
                }
                else
                {
					$input=$dom->createElement_simple('input',$td,array('type'=>'text','name'=>$field,'value'=>$user[$field][0]));
					$input=$dom->createElement_simple('input',$td,array('type'=>'hidden','name'=>'original_'.$field,'value'=>$user[$field][0]));
				}
			}
			$dom->createElement_simple('input',$form,array('type'=>'submit','name'=>'submit','value'=>'Lagre endringer'));

			$dom->createElement_simple('p',$body,false,'Tittel skal være funksjonstittel i henhold til enhetlig navnestandard.');
			$dom->createElement_simple('p',$body,false,'Lokasjon skal være fysisk lokasjon, fortrinnsvis gateadresse med mindre lokasjonen har et annet kjent navn (skoler og barnehager).');
			$dom->createElement_simple('p',$body,false,'Hvis bruker kun har mobil må feltet for telefon inneholde ordet "Mobil" for at nummeret skal synes i internkatalogen. Er feltet for telefon tomt får brukeren ingen oppføring i internkatalogen.');
			$dom->createElement_simple('p',$body,false,'Navn endres i agresso.');
		}
	}
	$dom->createElement_simple('a',$body,array('href'=>'user_list.php'),'Endre annen bruker');
	$dom->createElement_simple('br',$body);
	$dom->createElement_simple('a',$body,array('href'=>'index.php?logout'),'Logg ut');
	echo $dom->saveXML($body);
	?>

</html>