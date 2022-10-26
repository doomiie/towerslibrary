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


class PageToast
{
public static function addPageToast()
{
    echo "<!-- Toast container -->
    <div style='position: absolute; bottom: 1rem; right: 1rem;'>
        <!-- Toast -->
        <div class='toast' id='toastInfo' role='alert' aria-live='assertive' aria-atomic='true' data-bs-autohide='false'>
            <div class='toast-header justify-content-between'>
                <i data-feather='bell'></i>
                <strong class='mr-auto' id='toastTitle'>Toast without Autohide</strong>
                <small class='text-muted ml-2'>".date('H:i:s') ."</small>
                <button class='ml-2 mb-1 btn-close' type='button' data-bs-dismiss='toast' aria-label='Close'>                                                                </button>
            </div>
            <div class='toast-body' id='toastBody'></div>
        </div>
    </div>";
}

 
}
