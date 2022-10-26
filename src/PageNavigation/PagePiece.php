<?php

namespace PageNavigation;

class PagePiece
{
    
    public static function statusPieceTower($title, $value, $description, $id)
    {
    echo "<div class='justify-content-between d-flex'>
                                        <div>$title</div>
                                        <div class='' id =$id>
                                            $value
                                        </div>
                                    </div>
                                    <div class='small muted justify-content-between d-flex mb-2  ps-2 '>
                                        $description
                                    </div>";
    }

    public static function statusPieceBadge($title, $color='bg-primary')
    {
        printf('<element class="badge %s badge-sm  m-1"><em class="fa fa-flag fa-small pe-3"></em>%s</element>', $color, $title);
    }

    public static function buttonResetFlag($title, $flag, $cta, $description,$buttonID, $towerID,$color = 'btn-primary')
    {
        printf(' <div class="justify-content-between d-flex mb-2">
        <div>%s<element class="badge bg-primary badge-sm  m-1"><em class="fa fa-flag fa-small pe-3"></em>%s</element>
        </div>
        <button data-function="%s" data-towerid="%s", onClick="resetFlag(this)" class="button %s"><em class="fa fa-flag fa-small pe-3"></em> %s </button>
    </div>
    <div class="small muted justify-content-between d-flex mb-2  ps-2 ">
        %s
    </div>', $title, $flag, $buttonID,$towerID, $color,$cta, $description);
    }
} // koniec klasy PagePiece

?>