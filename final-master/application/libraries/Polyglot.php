<?php


if (!defined('BASEPATH')) { exit('No direct script access allowed'); }


class Polyglot {
    
    public function __construct() {
    }

 
    public function languages($languages_list) {
        $languages = array();
        $lang_codes = explode(",", $languages_list);
        foreach($lang_codes as $lang_code) {
            $languages[$lang_code] =  $this->code2language($lang_code) ;
        }
        return $languages;
    }
 
 
    public function nativelanguages($languages_list) {
        $languages = array();
        $lang_codes = explode(",", $languages_list);
        foreach($lang_codes as $lang_code) {
            $languages[$lang_code] =  $this->code2nativelanguage($lang_code) ;
        }
        return $languages;
    }
    
   
    public function code2language($code) {
        switch (strtolower($code)) {
          
            case 'zh' : return 'chinese'; break;
          
            case 'nl' : return 'dutch'; break;
            case 'en' : return 'english'; break;
      
            case 'fr' : return 'french'; break;
        
            case 'de' : return 'german'; break;
            case 'el' : return 'greek'; break;
      
            case 'hi' : return 'hindi'; break;

            case 'it' : return 'italian'; break;

            case 'km' : return 'khmer'; break;

            case 'pl' : return 'polish'; break;
        
            case 'ru' : return 'russian'; break;
        
            case 'es' : return 'spanish'; break;

            case 'uk' : return 'ukrainian'; break;

            default: return 'english'; break;
        }
    }

   
    public function language2code($language) {
        switch (strtolower($language)) {

            case 'chinese' : return 'zh'; break;

            case 'dutch' : return 'nl'; break;
            case 'english' : return 'en'; break;

            case 'french' : return 'fr'; break;

            case 'german' : return 'de'; break;
            case 'greek' : return 'el'; break;

            case 'italian' : return 'it'; break;

            case 'khmer' : return 'km'; break;

            case 'polish' : return 'pl'; break;

            case 'russian' : return 'ru'; break;

            case 'spanish' : return 'es'; break;

            case 'ukrainian' : return 'uk'; break;

            default: return 'en'; break;
        }
    }

    public function code2nativelanguage($code) {
        switch (strtolower($code)) {

            case 'zh' : return '中文'; break;

            case 'nl' : return 'Nederlands'; break;
            case 'en' : return 'English'; break;

            case 'fr' : return 'Français'; break;

            case 'de' : return 'Deutsch'; break;
            case 'el' : return 'Ελληνικά'; break;

            case 'it' : return 'Italiano'; break;

            case 'km' : return 'ភាសាខ្មែរ'; break;

            case 'pl' : return 'Polski'; break;

            case 'ru' : return 'Pусский язык'; break;

            case 'es' : return 'Español'; break;

            case 'uk' : return 'українська'; break;

            default: return 'English'; break;
        }
    }

  
    public function nativelanguage2code($language) {
        switch (strtolower($language)) {

            case '中文' : return 'zh'; break;

            case 'Nederlands' : return 'nl'; break;
            case 'English' : return 'en'; break;

            case 'français' : return 'fr'; break;

            case 'Deutsch' : return 'de'; break;
            case 'Ελληνικά' : return 'el'; break;

            case 'Italiano' : return 'it'; break;

            case 'ភាសាខ្មែរ' : return 'km'; break;

            case 'polski' : return 'pl'; break;

            case 'Pусский язык' : return 'ru'; break;

            case 'español' : return 'es'; break;

            case 'українська' : return 'uk'; break;

            default: return 'en'; break;
        }
    }
}
