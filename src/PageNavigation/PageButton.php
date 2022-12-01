<?php

/**
 * 
 * Page Buttons
 * Various buttons to create online

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


class PageButton
{

    /**
     * Dodaje button do REMOVE!!
     *
     * @param mixed $className
     * @param mixed $id
     * 
     * @return [type]
     * 
     */
    public static function addButtonDeleteItemInTable($className, $id)
    {
        printf("<button class='btn btn-danger' id='deleteButton'  onClick=remove('%s','%s')>
        <i class='feather text-white bg-danger' data-feather='trash-2'></i>
        </button>", $className, $id);
    }    
    public static function addButtonViewItemInTable($className, $id)
    {
        printf("<button class='btn btn-warning-soft' id='viewButton'  onClick=view('%s','%s')>
        <i class='feather text-warning' data-feather='eye'></i>
        </button>", $className, $id);
    }
    public static function addButtonEditItemInTable($className, $id)
    {
        $class = explode("_",$className);
        $class = strtolower(end($class));
        printf("<button class='btn btn-warning-soft' id='editButton'  onClick=edit('%s','%s')>
        <i class='feather text-warning' data-feather='edit'></i>
        </button>", $class, $id);
    } 
    public static function addButtonMailItemInTable($className, $id)
    {
        $class = explode("_",$className);
        $class = strtolower(end($class));
        printf("<button class='btn btn-warning-soft' id='mailButton'  onClick=mail('%s','%s')>
        <i class='feather text-warning' data-feather='mail'></i>
        </button>", $class, $id);
    }

    public static function addButtonSwitchItemInTable($className, $id, $active)
    {
        if($active)
        {
        printf("<button class='btn btn-success-soft' id='editButton'  onClick=flip('%s','%s')>
        <i class='feather text-success' data-feather='trash'></i>
        </button>", $className, $id);
        }   
        else {
            # code...
            printf("<button class='btn btn-warning-soft' id='editButton'  onClick=flip('%s','%s')>
        <i class='feather text-warning' data-feather='trash-2'></i>
        </button>", $className, $id);
        
        }
    }
  
}
