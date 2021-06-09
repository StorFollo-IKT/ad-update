<?php


namespace storfollo\ad_update;


use Exception;
use Throwable;

class UserNotFoundException extends Exception
{
    /**
     * @var string UPN
     */
    public $upn;

    function __construct($upn, $code = 0, Throwable $previous = null)
    {
        $this->upn = $upn;
        parent::__construct('User not found: '.$upn, $code, $previous);
    }
}