<?php

namespace PageNavigation;

class PageHeader
{
    public static function pageHeaderFull($title, $subtitle)
    {
    echo "<header class='page-header page-header-dark bg-gradient-primary-to-secondary pb-10'>
        <div class='container-xl px-4'>
            <div class='page-header-content pt-4'>
                <div class='row align-items-center justify-content-between'>
                    <div class='col-auto mt-4'>

                        <h1 class='page-header-title'>
                            <div class='page-header-icon'><i data-feather='activity'></i>
                            </div>
                            $title
                            </span>
                        </h1>
                        <div class='page-header-subtitle'>$subtitle</div>
                    </div>
                </div>
            </div>
        </div>
    </header>";
    }
    
    public static function pageHeaderFullFluid($title, $subtitle)
    {
    echo "<header class='page-header page-header-dark bg-gradient-primary-to-secondary pb-10'>
        <div class='container-fluid px-4'>
            <div class='page-header-content pt-4'>
                <div class='row align-items-center justify-content-between'>
                    <div class='col-auto mt-4'>

                        <h1 class='page-header-title'>
                            <div class='page-header-icon'><i data-feather='activity'></i>
                            </div>
                            $title
                            </span>
                        </h1>
                        <div class='page-header-subtitle'>$subtitle</div>
                    </div>
                </div>
            </div>
        </div>
    </header>";
       
    }
   
} // koniec klasy TopBar

?>