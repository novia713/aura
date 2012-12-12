#!/usr/bin/php
<?php
/*********

@author Leandro <leandro@leandro.org>

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


Usage
=====

php aura.php drupal.org
php aura.php groups.drupal.org
etc ...

**********/
class Aura
{

		public $url = null;
		
		
    /**
     * Colorizes strings
     * @param string $str text to colorize
     * @param string $color color with colorize
     * @return string
     */
    public function colorize($str, $color)
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
    
    /**
     * Checks sanity of url as argument
     * @return canonical url or exits the program
     */
    public function __construct()
    {
        global $argv, $argc;
        
        if (!@$argv[1] || !strstr(@$argv[1], ".")) {
            print "provide an URL as argument. Example: ruby aura.rb http://drupal.org \n";
            print "use http, don't use https \n";
            exit();
        }
        
        if (preg_match('/^https:/', @$argv[1], $res)) {
            print "use http, don't use https \n";
            exit();
        }
        
        if (!preg_match('/^http:/', @$argv[1])) {
            $this->url = "http://" . $argv[1];
        }
        
        if (!$this->url) $this->url = $argv[1];
        
        print $this->colorize("scanning $this->url ... \n", 'orange');
        return $this->url;
    }
    
    #first we try changelog.txt
    public function get_changelog()
    {
        $out  = "";
        $html = @file_get_contents($this->url . "/CHANGELOG.txt");
        if (!$html)
            return false;
        
        foreach ($http_response_header as $item_header) {
            //print_r($item_header);
            if (preg_match('/Content-Type(.*)/', $item_header, $res)) {
                if (strstr($res[1], 'text/plain')) {
                    $out = $this->colorize("changelog found \n", 'orange');
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
            $out .= $this->colorize("Drupal version: " . $res[1], 'blue');
        }
        
        $out .= " \n";
        return $out;
        
    }
    
    public function get_index()
    {
        $out       = "";
        $generator = null;
        $out       = $this->colorize("no CHANGELOG.txt found \n", 'pur');
        
        #if no changelog.txt found, we scan index.php headers
        $html = @file_get_contents($this->url);
        //print_r($http_response_header);
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
                $generator = 1;
            }
            if (stristr($item_header, 'x-generator') && (!stristr($item_header, 'Drupal'))) {
                $out .= "no Drupal headers found \n";
            }
            
        }
        
        #last, the html meta way
        if (!$generator) {
        
            $js = $meta_generator = null;
            if (stristr($html, 'Drupal')) {
                $js = 1;
            }
            if (stristr($html, 'meta name="Generator" content="Drupal 7')) {
                $meta_generator = 1;
            }
            
            
            if (!$js && !$meta_generator) {
                $out .= "this site seems not to be Drupal \n";
            } else if ($meta_generator) {
                $out .= "meta generator (html tag): Drupal 7 \n";
            } else {
                $out .= "its Drupal, but Drupal version unknown \n";
            }
            
        }
        $out .= "\n";
        return $out;
    }
}

$aura = new Aura();
print($aura->get_changelog()) ? $aura->get_changelog() : print $aura->get_index();
