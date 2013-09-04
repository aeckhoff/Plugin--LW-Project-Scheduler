<?php

class psc_auth
{
    public function __construct() 
    {
		$this->reg = lw_registry::getInstance();
        $this->auth = $this->reg->getEntry("auth");
        $this->inAuth = lw_in_auth::getInstance();
    }
    
    public function getUserId()
    {
        if ($this->isLoggedIn()) {
            return $this->getUserdata("id");
        }
        return false;
    }
    
    public function isLoggedIn()
    {
        return $this->inAuth->isLoggedIn();
    }
    
    public function getUserdata($key)
    {
        return $this->inAuth->getUserdata($key);
    }
}
