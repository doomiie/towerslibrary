<?php

/**
 * Page Protection
 * Needs
 * session_start() to run session
 * autoload to load itself
 * THEN
 * 1 checks against logged user
 * 2 checks logged user page access lvl
 * - superadmin and admin override page priviledges
 * @see       https://github.com/doomiie/gps/

 *
 *
 * @author    Jerzy Zientkowski <jerzy@zientkowski.pl>
 * @copyright 2020 - 2022 Jerzy Zientkowski
 

 * @license   FIXME need to have a licence
 * @note      This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace PageNavigation;
use UserManagement\Priviledge;
use UserManagement\User;
class PageProtection
{
    const ACCESS_ALL = 1;           // DOSTĘP DLA WSZYSTKICH
    const ACCESS_PAGE = 2;          // dostęp dla usera, do strony
    const ACCESS_ROLE = 4;          // dostęp dla roli, do strony
    const ACCESS_SUPERADMIN = 8;    // dostęp superadmina
    const ACCESS_DENIED = -1;    // brak dostępu

    /**
     * Funkcja sprawdza dostęp na 4 poziomach 
     * jeśli roles === null, wpuszcza wszystkich
     * jeśli user jest superadmin, wpuszcza
     * jeśli roles == rola usera, wpuszcza
     * jeśli user ma dostęp do tej konkretnej strony, wpuszcza
     *
     * @param mixed $user obiekt user
     * @param mixed $roles array lista ról lub null
     * 
     * @return bool false jeśli nie wpuszcza, 1,2,3,4 jeśli wpuszcza
     * 
     */
    public static function startPage($user, $roles)
    {
         if(null === $roles) return self::ACCESS_ALL;   // PAGE NOT PROTECTED!
        // let's see if user is superadmin
        if((int)$user->hasPriviledge("superadmin")) return self::ACCESS_SUPERADMIN;
        // let's see, if user hase page level access
        $result = (int)$user->hasPriviledges($roles);
        if($result) return self::ACCESS_ROLE;
        //if not, maybe he has specific access to this page
        $page = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $priv = new Priviledge($page);
        if($priv->id > -1)
        {
            $result = $priv->matchUs($user, $priv);
            if(is_array($result)) return self::ACCESS_PAGE;
        }
        return self::ACCESS_DENIED;
    }

    public static function goHome($userid)
    {
        
        if (!isset($_SESSION['tower_user_id'])) {
            header("Location: login.php");
            exit;
        }
        return new User((int)$_SESSION['tower_user_id']);
    }

    public static function forceGoHome()
    {
        header("Location: index.php"); 
    }

    public static function logout()
    {
        unset($_SESSION['tower_user_id']);
        self::goHome();
    }

 
}

?>