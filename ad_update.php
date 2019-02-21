<?php
/**
 * Created by PhpStorm.
 * User: abi
 * Date: 07.02.2019
 * Time: 12:00
 */
ini_set('display_errors', true);
session_name('ad-update');
session_start();
require 'adtools/adtools.class.php';
require 'vendor/autoload.php';
require 'logger/logger.class.php';

class ad_update extends adtools
{
    /**
     * @var Twig_Environment
     */
    public $twig;
    /**
     * @var logger
     */
    public $log;
    /**
     * @var array Field names
     */
    public $field_names;
    /**
     * @var array Editable fields
     */
    public $editable_fields;
    /**
     * @var array Fields with multiple values
     */
    public $multi_value_fields;
    /**
     * @var array Fields which should be fethced from A
     */
    public $fetch_fields;

    /**
     * ad_update constructor.
     * @param string $domain
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    function __construct($domain = null)
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
        $config = require 'config.php';
        $this->field_names = $config['field_names'];
        $this->editable_fields = $config['editable_fields'];
        $this->multi_value_fields = $config['multi_value_fields'];
        $this->fetch_fields = array_merge(array('manager'), array_keys($this->field_names));
    }

    /**
     * @param $domain_key
     * @return bool
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
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