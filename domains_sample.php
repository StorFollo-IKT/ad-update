<?Php
//Innstillinger for domenet det skal autentiseres mot
$domains['auth']=array('ldaps'=>true,'domain'=>'dc.as-admin.no','dn'=>'DC=as-admin,DC=no','username'=>'script','password'=>'xxx');
//Innstillinger for domenet det skal endres passord i
$domains['reset']=array('ldaps'=>true,'domain'=>'dc.as-skole.no','dn'=>'DC=as-skole,DC=no','username'=>'script','password'=>'xx');
