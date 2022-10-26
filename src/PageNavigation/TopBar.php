<?php

namespace PageNavigation;

class TopBar
{
    public static function topBar1($title)
    {
        TopBar::sectionHeader($title);
        TopBar::ulGlobalListStart();
        TopBar::liGlobalListStart();
        TopBar::liGlobalListItem("<button onClick='logoutGlobal()'>Wyloguj</button>", "title");
        TopBar::test();

        TopBar::liGlobalListStop();
        TopBar::ulGlobalListStop();
    }

    public static function topBar2($title)
    {
        TopBar::sectionHeader($title);
        TopBar::ulGlobalListStart();
        echo "<em id='pauseButton' class='button  border-2 text-primary fa fa-2x fa-play p-2 b-2 m-1 ' onClick='switchPause()'></em>";
        TopBar::liGlobalListStart();
        TopBar::liGlobalListItem("<button onClick='logoutGlobal()'>Wyloguj</button>", "title");
        //TopBar::test();

        TopBar::liGlobalListStop();
        TopBar::ulGlobalListStop();
    }

    public static function sectionHeader($title)
    {
        printf("<a class='navbar-brand pe-3 ps-4 ps-lg-2 mr-auto' href='index.php'>%s</a>", $title);
    }
    

    public static function ulGlobalListStart()
    {
        printf('<!--ulGlobalListStart --><ul class="navbar-nav align-items-center ml-auto mr-3">');
    }
    
    public static function liGlobalListStart()
    {
        printf("<li class='nav-item  dropdown no-caret dropdown-user me-3 me-lg-4'>
        <a class='btn btn-icon btn-transparent-dark dropdown-toggle' id='navbarDropdownUserImage' href='javascript:void(0);' role='button' data-bs-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
        <img class='img-fluid' src='assets/img/illustrations/profiles/profile-2.png' />
        </a>
        <div class='dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up' aria-labelledby='navbarDropdownUserImage'>");
    }


    public static function liGlobalListItem($title, $html, $feather="settings")
    {
        printf("<!--liGlobalListItem -->\n
                <a class='dropdown-item' href='#!'>\n
                 <div class='dropdown-item-icon'><i data-feather= %s ></i></div>%s, %s </a>\n
                 <div class='dropdown-divider'></div>\n",$feather, $title, $html);
    }

    public static function liGlobalListStop()
    {
        
        printf('</div></li>');
    }
    
    
    

    public static function ulGlobalListStop()
    {
        printf('</ul>');
    }

    public static function test()
    {
        printf('<h6 class="dropdown-header d-flex align-items-center">
        <img class="dropdown-user-img" src="assets/img/illustrations/profiles/profile-1.png" />
       
        <div class="dropdown-user-details">
            <div class="dropdown-user-details-name">username</div>
            <div class="dropdown-user-details-email">email</div>
            <div class="dropdown-user-details-name">role</div>
        </div>
    </h6>');
    }
    


} // koniec klasy TopBar

?>