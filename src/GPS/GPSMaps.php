<?php

/**
 * GPS Map handler
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

namespace GPS;

class GPSMaps
{

    

    public static function getGoogleMapsLink($lat, $lng, $title="")
    {
        if($title!="") $title = "(".$title.")";
        return "https://maps.google.com/maps?q=".$lat.",".$lng."&ll=".$lat.",".$lng.$title;
        //return "https://maps.google.com/maps?q=".$lat.",".$lng."";
    }

    public static function getGoogleMapPicture($lat, $lng, $title="")
    {
        /*!SECTION

        http://maps.googleapis.com/maps/api/staticmap?&size=600x400&style=visibility:on
&style=feature:water%7Celement:geometry%7Cvisibility:on
&style=feature:landscape%7Celement:geometry%7Cvisibility:on
&markers=icon:https://goo.gl/5y3S82%7CCanberra+ACT
&markers=icon:http://tinyurl.com/jrhlvu6%7CMelbourne+VIC
&markers=icon:https://goo.gl/1oTJ9Y%7CSydney+NSW&key=YOUR_API_KEY&signature=YOUR_SIGNATURE

https://maps.googleapis.com/maps/api/staticmap?center=63.259591,-144.667969&zoom=6&size=400x400
&markers=color:blue%7Clabel:S%7C62.107733,-145.541936&markers=size:tiny%7Ccolor:green%7CDelta+Junction,AK
&markers=size:mid%7Ccolor:0xFFFF00%7Clabel:C%7CTok,AK"&key=YOUR_API_KEY&signature=YOUR_SIGNATURE
*/
        //https://maps.googleapis.com/maps/api/staticmap?center=Williamsburg,Brooklyn,NY&zoom=13&size=400x400&
//markers=color:blue%7Clabel:S%7C11211%7C11206%7C11222&key=YOUR_API_KEY&signature=YOUR_SIGNATURE
        //https://maps.googleapis.com/maps/api/staticmap?center=Berkeley,CA&zoom=14&size=400x400&key=YOUR_API_KEY&signature=YOUR_SIGNATURE
        
        return "<img style='height: 100%; width: 100%; object-fit: contain' src='http://maps.googleapis.com/maps/api/staticmap?zoom=17&size=512x512&maptype=normal&markers=color:red|size:large|label:B|$lat,$lng&key=AIzaSyDxRzfVBGUNOAz21DCwK8_P-fJE1h7dpHc'>";
        return "<img src='https://maps.googleapis.com/maps/api/staticmap?center=$lat,$lng&zoom=12&key=AIzaSyDxRzfVBGUNOAz21DCwK8_P-fJE1h7dpHc&size=600x400&markers=size:tiny%7label:Tutaj'>";
    }
}// koniec klasy GPSMaps
