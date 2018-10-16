<?php

namespace v4\ISPConfig;

require_once __DIR__ . '/SoapClientInterface.php';

class UsernameStatus
{
    public $username;
    public $exists;
    public $error = false;

    public static function create($username = null, $exists = null)
    {
        return new self($username, $exists);
    }

    public function __construct($username = null, $exists = null)
    {
        $this->username = $username;
        $this->exists   = $exists;
    }

    public function __toString()
    {
        return $this->username . ' ' . ($this->exists ? 'true' : 'false');
    }
}
