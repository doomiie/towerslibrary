<?php

/**
 * USER:
 * 
 * Nadrzędna w stosunku do projektu, wieży i użytkownika
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

class User extends \Database\DBObject
{
    protected $tableName = "user";

    public $email;
    //public $role;
    public $password;

    public function authorize($password)
    {
        //printf("Autoryzacja %s vs %s <br/>\n", $password, $this->password);
        return (int)password_verify( $password, $this->password);
    }

    public function isAdmin()
    {
        if( $this->hasPriviledges(array('superadmin'))) return Priviledge::USER_SUPERADMIN;
        if( $this->hasPriviledges(array('admin'))) return Priviledge::USER_ADMIN;
        return Priviledge::USER_OTHER;

    }

    public function hasPriviledge(string $priviledge)
    {
        $row = $this->checkMeIn("user_priviledge");
        if(empty($row)) return false;
        //print_r(json_encode($row));
        //print_r($row[1]['priviledge_id']);
        foreach ((array)$row as $key => $value) {
            //echo $value['priviledge_id'];
            $priv = new Priviledge((int)$value['priviledge_id']);
            //echo $priv->name;
            if(0 == strcmp($priviledge, $priv->name))
            { return true;}
            //$priv->print();
        }
        return false;
    }
    
    public function hasPriviledges(array $priviledges)
    {
        foreach ($priviledges as $key => $value) {
            # code...
            //printf("FIELD: %s, VALUE: [%s]<br>\n",  $key, $value);//$this->{$key});          
            if($this->hasPriviledge($value)) return true;
        }
        return false;
    }
    
    public function listPriviledges()
    {
        $row = $this->checkMeIn("user_priviledge");
        if(empty($row)) return false;        
        foreach ((array)$row as $key => $value) {            
            $priv = new Priviledge((int)$value['priviledge_id']);
            $list[] = $priv->name;            
        }
        return $list;
    }
}