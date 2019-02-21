<?php
/**
 * Created by PhpStorm.
 * User: abi
 * Date: 07.02.2019
 * Time: 12:00
 */
session_name('ad-update');
session_start();
require 'adtools/adtools.class.php';
require 'vendor/autoload.php';
require 'logger/logger.class.php';

class ad_update extends adtools
{
    public $twig;
    public $log;
    function __construct($domain = false)
    {
        $loader = new Twig_Loader_Filesystem(array('templates', 'templates'), __DIR__);
        $this->twig = new Twig_Environment($loader, array('debug' => true, 'strict_variables' => true));
        try {
            parent::__construct($domain);
            $this->log=new logger('ad-update');
        }
        catch (Exception $e)
        {
            echo $this->twig->render('error.twig', array('error'=>$e->getMessage(), 'title'=>'Feil'));
        }
    }

    function connect($domain_key)
    {
        try {
            return parent::connect($domain_key);
        }
        catch (Exception $e)
        {
            echo $this->twig->render('error.twig', array('error'=>$e->getMessage(), 'title'=>'Feil'));
        }
    }
}