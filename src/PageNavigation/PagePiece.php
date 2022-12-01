<?php

namespace PageNavigation;

class PagePiece
{

    public static function statusPieceTower($title, $value, $description, $id)
    {
        echo "<div class='justify-content-between d-flex'>
                                        <div>$title</div>
                                        <div class='' id =$id>$value</div>
                                    </div>
                                    <div class='small muted justify-content-between d-flex mb-2  ps-2 '>
                                        $description
                                    </div>";
    }
    public static function statusPieceTowerDescriptionOnHover($title, $value, $description, $id)
    {
        echo "<div class='justify-content-between d-flex mb-3'>
                                        <div><em class='fa fa-small fa-question-circle m-1' title='$description'></em>$title</div>
                                        <div class='' id =$id>
                                            $value
                                        </div>
                                    </div>
                                    ";
    }

    public static function statusPieceBadge($title, $color = 'bg-primary')
    {
        printf('<element class="badge %s badge-sm  m-1"><em class="fa fa-flag fa-small pe-3"></em>%s</element>', $color, $title);
    }

    public static function buttonResetFlag($title, $flag, $cta, $description, $buttonID, $towerID, $extraAction = "",  $color = 'btn-primary')
    {
        printf(' <div class="justify-content-between d-flex mb-2">
        <div>%s<element class="badge bg-primary badge-sm  m-1"><em class="fa fa-flag fa-small pe-3"></em>%s</element>
        </div>
        <button data-function="%s" data-towerid="%s", onClick="resetFlag(this, \'%s\')" class="button %s"><em class="fa fa-flag fa-small pe-3"></em> %s </button>
    </div>
    <div class="small muted justify-content-between d-flex mb-2  ps-2 ">
        %s
    </div>', $title, $flag, $buttonID, $towerID, $extraAction, $color, $cta,  $description);
    }

    public static function hrefFormattedPrint($href, $title, $class = "")
    {
        printf("%s", PagePiece::hrefFormatted($href, $title, $class = ""));
    }
    public static function hrefFormatted($href, $title, $class = "")
    {
        return sprintf("<a href='%s' target='_blank' class='%s'>%s</a>", $href, $class, $title);
    }

    public function shortTowerRow($tower)
    {
        return sprintf('<div class="justify-content-between d-flex mb-3">
                                        <div><em class="fa fa-xl fa-tower-broadcast"></em>
                                        <span class="x-small position-relative top-0 start-50  translate-middle badge small rounded-pill bg-primary">%s</span></div>
                                        <div>%s</div>   
                                        <div>%s</div>   
                                        <div class="justify-content-between d-flex">
                                        <div>
                                            <a href="%s"> %s</a>
                                        </div>
                                        </div>
                                    </div>', $tower->id, $tower->name, $tower->serial_nr, $tower->linkToSinglePage(),  $tower->name);
    }
    public function shortBondTowerProject($tower, $project, $operation)
    {

        return sprintf('<div class="justify-content-between d-flex mb-3">
                                        
        <div>%s</div>   
        <div>%s</div>   
                                        <div>%s</div>   
                                        <div>%s</div>   
                                        <div class="justify-content-between d-flex">
                                        <div>
                                            <a href="%s"> %s</a>
                                        </div>
                                        <div class="ms-2">
                                            <button class="btn btn-primary  btn-sm" onClick="bondTowerProject(%s,%s, \'%s\');  return false;">%s</button>
                                        </div>
                                        </div>
                                    </div>', $tower->id, $tower->tower_nr, $tower->serial_nr, $tower->getTowerProject()->name, $tower->linkToSinglePage(),  $tower->name, $tower->id, $project->id, $operation, $operation);
    }

    public function shortBondHomebaseProject($homeBase, $project, $operation)
    {

        return sprintf('<div class="justify-content-between d-flex mb-3">
        <div>BAZA:<a href="%s">%s</a></div>   
        <div>%s</div>   
        <div>PROJEKT:<a href="%s">%s</a></div>   
            <div class="justify-content-between d-flex">
            
            <div class="ms-2">
                <button class="btn btn-primary  btn-sm" onClick="bondHomeBaseProject(%s,%s, \'%s\');  return false;">%s</button>
            </div>
            </div>
        </div>', $homeBase->linkToSinglePage(), $homeBase->name, $homeBase->id, $homeBase->getParent()->linkToSinglePage(), $homeBase->getParent()->name,  $homeBase->name, $homeBase->id, $project->id, $operation, $operation);
    }
    public function shortBondProjectOrganization($project, $organization, $operation)
    {

        return sprintf('<div class="justify-content-between d-flex mb-3">
        <div>%s</div>   
        <div>%s</div>   
            <div class="justify-content-between d-flex">
            <div>
                <a href="%s"> %s</a>
            </div>
            <div class="ms-2">
                <button class="btn btn-primary  btn-sm" onClick="bondProjectOrganization(%s,%s, \'%s\');  return false;">%s</button>
            </div>
            </div>
        </div>', $project->id, $project->name, $project->linkToSinglePage(),  $project->name, $project->id, $organization->id, $operation, $operation);
    }

    public static function shortObjectRow($object)
    {
        return sprintf('<div class="justify-content-between d-flex mb-3">
                                        
                                        <div>%s:</div>   
                                        <div class="justify-content-between d-flex">
                                        <div id="%s_id" class="me-3">%s</div>   
                                        <div>
                                            <a href="%s"> %s</a>
                                        </div>
                                        </div>
                                    </div>', $object->getClassName(), $object->getClassName(), $object->id, $object->linkToSinglePage(),  $object->name);
    }

    public static function heartBeat($towerid)
    {

        return sprintf('<div class="heart-rate-container"><div id="%s" data-id="%s" class="heart-rate full-wx">
  <svg version="1.0" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="70px" height="33px" viewBox="0 0 150 73" enable-background="new 0 0 150 73" xml:space="preserve">
    <polyline fill="none" stroke="red" stroke-width="3" stroke-miterlimit="10" points="0,45.486 38.514,45.486 44.595,33.324 50.676,45.486 57.771,45.486 62.838,55.622 71.959,9 80.067,63.729 84.122,45.486 97.297,45.486 103.379,40.419 110.473,45.486 150,45.486"
    />
  </svg>
  <div class="fade-in"></div>
  <div class="fade-out"></div>
</div></div>', "heart-rate-" . $towerid, $towerid);
    }

    public function rowConfigTower($title, $flagTitle, $inputID, $fieldName, $value, $description)
    {
        return sprintf('<div class="justify-content-between d-flex">
        <div>%s<element class="badge bg-primary badge-sm m-1"><em class="fa fa-flag fa-small pe-3"></em>%s</element>
        </div>
        <div class="form-check for-input">
            <input class="form-input border-1" role="input" type="text" id="%s" name="%s" value="%s"></input>
        </div>
    </div>
    <div class="small muted justify-content-between d-flex mb-2  ps-2 ">
        %s
    </div>', $title, $flagTitle, $inputID, $fieldName, $value, $description);
    }

    public function inputItemForManagement($formName, $label, $idName, $value)
    {
        return sprintf('<div class="col-md-12">
                        <label class="small mb-1" for="%s">%s</label>
                        <input required form="%s" class="form-control" id="%s" name="%s" type="text" placeholder="" value="%s" /></div>',
            $formName,
            $label,
            $formName,
            $idName,
            $idName,
            $value
        );
    }
    public function inputDropDownForManagement($formName, $label, $idName, $value)
    {
        return sprintf('<div class="col-md-12">
                        <label class="small mb-1" for="%s">%s</label>
                        <input required form="%s" class="form-control" id="%s" name="%s" type="text" placeholder="" value="%s" /></div>',
            $formName,
            $label,
            $formName,
            $idName,
            $idName,
            $value
        );
    }
} // koniec klasy PagePiece
