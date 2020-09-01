<?php

use datagutten\ad_update\azure;

/**
 * Created by PhpStorm.
 * User: abi
 * Date: 07.02.2019
 * Time: 12:00
 */

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
     * @var azure
     */
    public $azure;

    /**
     * ad_update constructor.
     * @param string $domain
     */
    function __construct($domain = null)
    {
        session_name('ad-update');
        session_start();
        $loader = new Twig\Loader\FilesystemLoader(array('templates', 'templates'), __DIR__);
        $this->twig = new Twig\Environment($loader, array('strict_variables' => true));
        try {
            parent::__construct($domain);
            $this->log=new logger('ad-update');
        }
        catch (Exception $e)
        {
            echo $this->render('error.twig', array('error'=>$e->getMessage(), 'title'=>'Feil'));
        }
        $config = require 'config.php';
        $this->field_names = $config['field_names'];
        $this->editable_fields = $config['editable_fields'];
        $this->multi_value_fields = $config['multi_value_fields'];
        $this->fetch_fields = array_merge(array('manager'), array_keys($this->field_names));
        $this->azure = new azure($config['azure']);
        $this->azure->adtools = $this;
    }

    /**
     * @param $domain_key
     */
    function connect($domain_key)
    {
        try {
            parent::connect($domain_key);
        }
        catch (Exception $e)
        {
            echo $this->render('error.twig', array('error'=>$e->getMessage(), 'title'=>'Feil'));
        }
    }

    /**
     * Renders a template.
     *
     * @param string $name    The template name
     * @param array  $context An array of parameters to pass to the template
     *
     * @return string The rendered template
     */
    public function render($name, $context)
    {
        try {
            return $this->twig->render($name, $context);
        }
        catch (\Twig\Error\Error $e) {

            //$trace = sprintf('<pre>%s</pre>', $e->getTraceAsString());
            $msg = "Error rendering template:\n" . $e->getMessage();
            try {
                die($this->twig->render('error.twig', array(
                        'title'=>'Rendering error',
                        'error'=>$msg)
                ));
            }
            catch (\Twig\Error\Error $e_e)
            {
                $msg = sprintf("Original error: %s\n<pre>%s</pre>\nError rendering error template: %s\n<pre>%s</pre>",
                    $e->getMessage(), $e->getTraceAsString(), $e_e->getMessage(), $e_e->getTraceAsString());
                die($msg);
            }
        }
    }

    public function canEdit($user)
    {
        return $user['manager'][0]==$_SESSION['manager_dn'] || $user['dn']==$_SESSION['current_user']['dn'];
    }
}