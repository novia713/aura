#!/usr/bin/php
<?php
/*********

Copyright (C) 2012 Leandro Vázquez Cervantes (leandro@leandro.org)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.

**********/
class Aura
{
    function colorize($str, $color)
    {
        switch ($color) {
            case 'red':
                $n = "31";
                break;
            case 'green':
                $n = "32";
                break;
            case 'orange':
                $n = "33";
                break;
            case 'blue':
                $n = "34";
                break;
            case 'pur':
                $n = "35";
                break;
            default:
                $n = "36";
                break;
        }
        return "\033[" . $n . "m" . $str . "\033[37m\r\n";
    }
    
    function check_args()
    {
        global $argv, $argc;
        $url = null;
        
        if (!@$argv[1]) {
            print "provide an URL as argument. Example: ruby aura.rb http://drupal.org \n";
            print "don't use https \n";
            die();
        }
        
        if (preg_match('/^https:/', @$argv[1], $res)) {
            print "use http, don't use https \n";
            die();
        }
        
        if (preg_match('/^http:/', @$argv[1])) {
            $url = "http://" . $argv[1];
        }
        
        if (!$url) {
            $url = (@$argv[1]) ? "http://" . @$argv[1] : null;
        }
        
        print "scanning $url ... \n";
        return $url;
    }
    
    #first we try changelog.txt
    function get_changelog($checked_url)
    {
        $out = "";
        
        $html = @file_get_contents($checked_url . "/CHANGELOG.txt");
        if (!$html)
            return false;
        
        foreach ($http_response_header as $item_header) {
            //print_r($item_header);
            if (preg_match('/Content-Type(.*)/', $item_header, $res)) {
                if (strstr($res[1], 'text/plain')) {
                    $out .= "changelog found \n";
                    $out = $this->colorize($out, 'orange');
                }
            }
            if (stristr($item_header, 'server')) {
                $out .= "$item_header \n";
            }
            if (stristr($item_header, 'x-powered-by')) {
                $out .= "$item_header \n";
            }
            if (stristr($item_header, 'x-varnish-cache')) {
                $out .= "$item_header \n";
            }
            if (stristr($item_header, 'x-cache')) {
                $out .= "$item_header \n";
            }
        }
        
        if (preg_match('/Drupal (\d\.\d)/', $html, $res)) {
            $out .= "Drupal version: " . $res[1];
        }
        
        $out .= " \n";
        return $out;
        
    }
    
    function get_index($checked_url)
    {
        $out = "no CHANGELOG.txt found \n";
        $out = $this->colorize($out, 'pur');
        
        #if no changelog.txt found, we scan index.php headers
        $html = @file_get_contents($checked_url);
        if (!$html)
            return false;
        foreach ($http_response_header as $item_header) {
            if (stristr($item_header, 'server')) {
                $out .= "$item_header \n";
            }
            if (stristr($item_header, 'x-powered-by')) {
                $out .= "$item_header \n";
            }
            if (stristr($item_header, 'x-drupal-cache')) {
                $out .= "$item_header \n";
            }
            if (stristr($item_header, 'x-generator')) {
                $out .= "$item_header \n";
            }
            if (stristr($item_header, 'x-generator') && (!stristr($item_header, 'Drupal'))) {
                $out .= "no Drupal headers found \n";
            }
        }
        
        #last, the html meta way
        $js = $meta_generator = null;
        if (stristr($item_header, 'x-generator')) {
            foreach ($html as $line) {
                if (stristr($line, 'Drupal')) {
                    $js = 1;
                }
                if (stristr($line, 'meta name="Generator" content="Drupal 7')) {
                    $meta_generator = 1;
                }
            }
            if (!$js && !$meta_generator) {
                $out .= "this site seems not to be Drupal \n";
            } else if ($meta_generator) {
                $out .= "meta generator (html tag): Drupal 7 \n";
            } else {
                $out .= "its Drupal, but Drupal version unknown \n";
            }
        }
        
        
        $out .= " \n";
        return $out;
    }
}

$aura = new Aura();
$url  = $aura->check_args();

print($aura->get_changelog($url)) ? $aura->get_changelog($url) : print $aura->get_index($url);
