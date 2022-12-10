<?php

namespace PageNavigation;

use UserManagement\Priviledge;

class SideNav
{
    public static function sidenavFooter($username, $pageProtected)
    {
        echo "<div class='sidenav-footer'>
        <div class='sidenav-footer-content'>
            <div class='sidenav-footer-subtitle'>Zalogowany:</div>
            <div class='sidenav-footer-title'>[".$username."]  [".$pageProtected."]</div>
        </div>
    </div>";
    }

    public static function sectionHeader($title)
    {
        echo "<div class='sidenav-menu-heading'>$title</div>" . PHP_EOL;
    }

    /**
     * [Description for sectionHeaderCollapsed]
     *
     * @param mixed $title tytuł sekcji
     * @param string $feather typ ikonki
     * 
     * @return string collapse ID, do kolejnych elementów
     * 
     */
    public static function sectionHeaderCollapsed($title, $feather = 'activity')
    {
        $collapseUID = "collapse" . uniqid();


        printf('<a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#%s" aria-expanded="false" aria-controls="%s">
                                <div class="nav-link-icon"><i data-feather="%s"></i></div>
                                %s
                                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>', $collapseUID, $collapseUID, $feather, $title);
        // echo "<a class='nav-link collapsed' href='javascript:void(0);' data-bs-toggle='collapse' data-bs-target='#$collapseUID' aria-expanded='false' aria-controls='$collapseUID'>" . PHP_EOL;
        // echo "<div class='nav-link-icon'><em data-feather='activity'></em></div>". PHP_EOL;
        //echo $title;
        //echo "<div class='sidenav-collapse-arrow'><em class='fas fa-angle-down'></em></div>". PHP_EOL;
        //echo "</a>". PHP_EOL;
        return $collapseUID;
    }

    public static function sectionCollapseOpen($sectionUID)
    {
        printf('<div class="collapse" id="%s" data-bs-parent="#accordionSidenav">
                                <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPages">', $sectionUID);
        //echo "<div class='' id='$sectionUID' data-bs-parent='#accordionSidenav'>". PHP_EOL;
        //echo "<nav class='sidenav-menu-nested nav accordion' id='accordionSidenavPages'>";
        return $sectionUID;
    }
    public static function sectionCollapseClose($sectionUID)
    {
        printf("</nav></div>\n");
        return $sectionUID;
    }

    protected static function colorLink($href, $title, $color)
    {
        printf('<a class="nav-link %s" href="%s">%s</a>',$color, $href, $title);
    }
    protected static function simpleLink($href, $title)
    {
        printf('<a class="nav-link" href="%s">%s</a>', $href, $title);
        //echo "<a class='nav-link' href='$href'>$title</a>". PHP_EOL;
    }


    public static function Menu2()
    {
        self::sectionHeader("Aplikacje");
        $sectionUID = self::sectionHeaderCollapsed("Status systemu", 'list');
        self::sectionCollapseOpen($sectionUID);
        self::simpleLink("#!", "Status");
        self::simpleLink("#!", "Mapa projektów");
       
        self::sectionCollapseClose($sectionUID);

        self::sectionHeader("ZARZĄDZANIE");
        $sectionUID = self::sectionHeaderCollapsed("Zarządzanie wieżami", 'list');
        self::sectionCollapseOpen($sectionUID);
        self::simpleLink("#!", "Lista wież");
        self::simpleLink("#!", "Dodaj wieżę");
        self::sectionCollapseClose($sectionUID);
        $sectionUID = self::sectionHeaderCollapsed("Zarządzanie projektami", 'grid');
        self::sectionCollapseOpen($sectionUID);
        self::simpleLink("#!", "Lista projektów");
        self::simpleLink("#!", "Dodaj projekt");
        self::sectionCollapseClose($sectionUID);  
        $sectionUID = self::sectionHeaderCollapsed("Zarządzanie organizacjami", 'server');
        self::sectionCollapseOpen($sectionUID);
        self::simpleLink("#!", "Lista organizacji");
        self::simpleLink("organization-add.php", "Dodaj organizację");
        self::sectionCollapseClose($sectionUID);
        $sectionUID = self::sectionHeaderCollapsed("Zarządzanie użytkownikami", 'grid');
        self::sectionCollapseOpen($sectionUID);
        self::simpleLink("#!", "Lista użytkowników");
        self::simpleLink("user-management-user-add.php", "Dodaj użytkownika");
        self::simpleLink("user-management-priviledges-list.php", "Zarządzanie dostępami");
        self::simpleLink("#!", "Dodaj projekt");
        self::sectionCollapseClose($sectionUID);
    }
    public static function Menu1()
    {
        self::sectionHeader("Aplikacje");
        $sectionUID = self::sectionHeaderCollapsed("Status systemu", 'list');
        self::sectionCollapseOpen($sectionUID);
        self::simpleLink("status.php", "Status");
        self::simpleLink("#!", "Mapa projektów");
       
        self::sectionCollapseClose($sectionUID);

        self::sectionHeader("ZARZĄDZANIE");
        $sectionUID = self::sectionHeaderCollapsed("Zarządzanie", 'list');
        
        self::sectionCollapseOpen($sectionUID);
        self::simpleLink("manage-organization.php", "Organizacje");
        self::simpleLink("manage-project.php", "Projekty");
        self::simpleLink("manage-homebase.php", "Bazy");
        self::simpleLink("manage-tower.php", "Wieże");
        self::simpleLink("manage-tower-waiting.php", "Wieże w poczekalni");
        self::simpleLink("symulator.php", "Symulator wieży");
        
        self::simpleLink("manage-user.php", "Użytkownicy");
        self::simpleLink("manage-priviledge.php", "Dostępy");
        self::simpleLink("manage-emails.php", "Wysyłka raportów");
        self::simpleLink("user-management-user-add.php", "Użytkownicy, dodaj");
        self::simpleLink("user-management-priviledges-list.php", "Dostępy");
        self::simpleLink("manage-log.php", "Logi systemowe");
        self::sectionCollapseClose($sectionUID);       
        
    }
    /**
     * Dodatkowe menu tylko dla admina i superadmina
     *
     * @param mixed $user
     * @param mixed $pageProtected
     * 
     * @return [type]
     * 
     */
    public static function menuInfo($user, $pageProtected, array $pageProtectionArray)
    {
        if($user->isAdmin() == Priviledge::USER_OTHER) return;  // to menu jest TYLKO dla admina i superadmina!
        $sectionUID = self::sectionHeaderCollapsed("Informacje o stronie", 'layout');
        self::sectionCollapseOpen($sectionUID);
        //
        self::colorLink("#!","USER: ". $user->name, "text-success small");
        self::colorLink("#!","PRIVS: ".  json_encode($user->listPriviledges()), "text-success small");
        self::colorLink("#!","PR Level: ". $pageProtected . $user->getConstantName("PageNavigation\PageProtection", $pageProtected), "text-success small");
        self::colorLink("#!","PR access: ". json_encode($pageProtectionArray), "text-success small");
       // self::simpleLink("test2.php", "Test");
        //self::simpleLink("user-management-priviledges-list.php", "Zarządzanie dostępami");
        //self::simpleLink("#!", "Dodaj projekt");
        self::sectionCollapseClose($sectionUID); 
    }
}
