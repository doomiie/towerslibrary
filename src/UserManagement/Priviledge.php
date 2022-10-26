<?php

/**
 * Priviledges:
 * 
 * 
 * Nadrzędna w stosunku do wieży i użytkownika
 * 
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

namespace UserManagement;



class Priviledge extends \Database\DBObject
{
    protected $tableName = "priviledge";

    public  $description;       // opis dostępu
    public  $type;              // typ dostępu
    
    const PRIV_UNDEFINED = 0;   // brak definicji, alert!
    const PRIV_SYSTEM = 1;  // rola systemowa, nie można jej usuwać
    const PRIV_PAGE = 2;    // dostęp do strony
    const PRIV_FUNCTION = 4;        // dostęp do określonej funkcjonalności, na życzenie

    const USER_SUPERADMIN = 8;  // superadmin, dostęp do wszystkiego, wszędzie
    const USER_ADMIN = 16;       // admin, dostęp do wszystkich funkcji administracyjnych
    const USER_OTHER = 32;       // inny typ usera, definiowalny przez admina np.

   
}

