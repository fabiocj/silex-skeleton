<?php

namespace Auth;

class Authorization
{
    
    private $session, $paths;
    
    public function __construct(\Symfony\Component\HttpFoundation\Session\Session $sessionObject)
    {
        $this->session = $sessionObject;
        $this->paths = require_once __ROOT__ . '/config/auth.php';
    }
    
    public function freePass($route)
    {
        $pass = false;
        foreach($this->paths['free'] as $path)
            if (preg_match($this->getRegexPattern($path), $route))
            {
                $pass = true;
                break;
            }
        return $pass;
    }
    
    public function isAuthorized($route)
    {
        $user = $this->session->get('user');
        return $this->isAllowed($user, $route) && !$this->isDenied($user, $route);
    }
    
    
    
    private function getRegexPattern($path)
    {
        return '/^' . str_replace('*', '.+', str_replace('.', '\.', $path)) . '/';
    }
    
    private function isAllowed(\Model\User $user, $route)
    {
        return $this->verifyAuthorization('allow', $user, $route);
    }
    
    private function isDenied(\Model\User $user, $route)
    {
        return $this->verifyAuthorization('deny', $user, $route);
    }
    
    private function verifyAuthorization($auth_type, \Model\User $user, $route)
    {
        $auth = false;
        if (isset($this->paths[$auth_type][$user->nome_perfil]))
        {
            if ($this->paths[$auth_type][$user->nome_perfil] == 'all')
                return true;

            foreach ($this->paths[$auth_type][$user->nome_perfil] as $path)
            {
                if (preg_match($this->getRegexPattern($path), $route))
                {
                    $auth = true;
                    break;
                }
            }
        }
        return $auth;
    }
    
}