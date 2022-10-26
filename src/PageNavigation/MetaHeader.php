<?php

namespace PageNavigation;

class MetaHeader
{
    public static function printMeta($title="CodeBois::Towers v2")
    {
        // <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        //<link href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/font-awesome.css" rel="stylesheet"  type='text/css'>
        printf('<head>
        
        <!-- Zawartość meta tutaj -->
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="Jerzy Zientkowski 2022" />
        <title>%s</title>
        <!-- css styling -->
        <link href="assets/css/fontawesome.css" rel="stylesheet">
        <link href="assets/css/brands.css" rel="stylesheet">
        <link href="assets/css/solid.css" rel="stylesheet">
        
        <link href="css/styles.css" rel="stylesheet" />
        <link rel="stylesheet" type="text/css" href="DataTables/datatables.min.css"/>
        
        <!-- JQUERY needs to be the first script -->
        <script src="DataTables/jQuery-3.6.0/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js" crossorigin="anonymous"></script>
        <script type="text/javascript" src="DataTables/datatables.min.js"></script>
    </head>

', $title);
    }

    public static function printFooter()
    {
        echo '<footer class="footer-admin mt-auto footer-light">
        <div class="container-xl px-4">
            <div class="row">
                <div class="col-md-6 small">Copyright &copy; Jerzy Zientkowski, 2021-2022</div>
                
            </div>
        </div>
    </footer>';
    }
} // koniec klasy TopBar

?>