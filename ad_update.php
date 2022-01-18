<?php

use storfollo\ad_update\azure;
use storfollo\adtools\adtools;

/**
 * Created by PhpStorm.
 * User: abi
 * Date: 07.02.2019
 * Time: 12:00
 */

class ad_update
{
    /**
     * @var string Base URL
     */
    public $base_url;
    /**
     * @var Twig_Environment
     */
    public $twig;
    /**
     * @var bool Show trace in errors
     */
    public $debug = false;
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
     * @var array Fields which should be fetched from AD
     */
    public $fetch_fields;
    /**
     * @var array Users allowed to edit any user
     */
    public $global_editors = [];
    /**
     * @var azure
     */
    public $azure;
    /**
     * @var adtools
     */
    public $ad;

    /**
     * ad_update constructor.
     */
    function __construct()
    {
        session_name('ad-update');
        session_start();
        $config = require 'config.php';

        $loader = new Twig\Loader\FilesystemLoader(array('templates', 'templates'), __DIR__);
        $this->twig = new Twig\Environment($loader, array('strict_variables' => true));
        try {
            $this->ad = adtools::connect_config($config['ad']);
            $this->log=new logger('ad-update', $config['log_path'] ?? '/home/logs');
        }
        catch (Exception $e)
        {
            echo $this->render('error.twig', array('error'=>$e->getMessage(), 'title'=>'Feil', 'trace'=>$e->getTraceAsString()));
        }

        $this->field_names = $config['field_names'];
        $this->editable_fields = $config['editable_fields'];
        $this->multi_value_fields = $config['multi_value_fields'];
        $this->global_editors = $config['global_editors'];
        $this->fetch_fields = array_merge(array('manager'), array_keys($this->field_names));
        $this->azure = new azure($this->ad, $config['azure']);
        $this->base_url = $config['base_url'];
    }

    /**
     * Renders a template.
     *
     * @param string $name The template name
     * @param array $context An array of parameters to pass to the template
     *
     * @return string The rendered template
     */
    public function render(string $name, array $context): string
    {
        try {
            $context['debug'] = $this->debug;
            return $this->twig->render($name, $context);
        }
        catch (\Twig\Error\Error $e) {

            //$trace = sprintf('<pre>%s</pre>', $e->getTraceAsString());
            $msg = "Error rendering template:\n" . $e->getMessage();
            try {
                die($this->twig->render('error.twig', array(
                        'title'=>'Rendering error',
                        'error'=>$msg,
                        'trace'=>$e->getTraceAsString(),
                        'debug'=>$this->debug,
                )));
            }
            catch (\Twig\Error\Error $e_e)
            {
                $msg = sprintf("Original error: %s\n<pre>%s</pre>\nError rendering error template: %s\n<pre>%s</pre>",
                    $e->getMessage(), $e->getTraceAsString(), $e_e->getMessage(), $e_e->getTraceAsString());
                die($msg);
            }
        }
    }

    public function canEdit(array $user): bool
    {
        return $user['manager'][0]==$_SESSION['manager_dn'] || $user['dn']==$_SESSION['current_user']['dn'];
    }
}