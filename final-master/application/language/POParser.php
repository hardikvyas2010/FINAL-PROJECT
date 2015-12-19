<?php

class POParser
{
    private $_filename;


    protected function _dequote($str)
    {
        return substr($str, 1, -1);
    }

    public function parse($filename)
    {
   
        if (!is_file($filename)) {
            throw new Exception('The specified file does not exist.');
        }
        if (substr($filename, strrpos($filename, '.')) !== '.po') {
            throw new Exception('The specified file is not a PO file.');
        }

        $lines = file($filename, FILE_IGNORE_NEW_LINES);

      
        $entries = array(

        );

   
        $headers = array(
            'Project-Id-Version'            => '',
            'Report-Msgid-Bugs-To'          => '',
            'POT-Creation-Date'             => '',
            'PO-Revision-Date'              => '',
            'Last-Translator'               => '',
            'Language-Team'                 => '',
            'Content-Type'                  => '',
            'Content-Transfer-Encoding'     => '',
            'Plural-Forms'                  => '',
        );
        $i = 2;
        while ($line = $lines[$i++]) {
            $line = $this->_dequote($line);
            $colonIndex = strpos($line, ':');
            if ($colonIndex === false) {
                continue;
            }
            $headerName = substr($line, 0, $colonIndex);
            if (!isset($headers[$headerName])) {
                continue;
            }
            $headers[$headerName] = substr($line, $colonIndex + 1, -2);
        }

        $entry = array();
        for ($n = count($lines); $i < $n; $i++) {
            $line = $lines[$i];
            if ($line === '') {
                $entries[] = $entry;
                $entry = array();
                continue;
            }
            if ($line[0] == '#') {
                $comment = substr($line, 3);
                switch ($line[1]) {
              
                    case ' ': {
                        if (!isset($entry['translator-comments'])) {
                            $entry['translator-comments'] = $comment;
                        }
                        else {
                            $entry['translator-comments'] .= "\n" . $comment;
                        }
                        break;
                    }
            
                    case '.': {
                        if (!isset($entry['extracted-comments'])) {
                            $entry['extracted-comments'] = $comment;
                        }
                        else {
                            $entry['extracted-comments'] .= "\n" . $comment;
                        }
                        break;
                    }
             
                    case ':': {
                        if (!isset($entry['references'])) {
                            $entry['references'] = array();
                        }
                        $entry['references'][] = $comment;
                        break;
                    }
              
                    case ',': {
                        if (!isset($entry['flags'])) {
                            $entry['flags'] = array();
                        }
                        $entry['flags'][] = $comment;
                        break;
                    }
                 
                    case '|': {
                      
                        if ($comment[4] == 'd') {
                            $entry['previous-msgid'] = $this->_dequote(substr($comment, 6));
                        }
                  
                        else {
                            $entry['previous-msgctxt'] = $this->_dequote(substr($comment, 8));
                        }
                        break;
                    }
                }
            }
            else if (strpos($line, 'msgid') === 0) {
                if ($line[5] === ' ') {
                    $entry['msgid'] = $this->_dequote(substr($line, 6));
                }
        
                else {
                    $entry['msgid_plural'] = $this->_dequote(substr($line, 13));
                }
            }
            else if (strpos($line, 'msgstr') === 0) {
           
                if ($line[6] === ' ') {
                    $entry['msgstr'] = $this->_dequote(substr($line, 7));
                }
          
                else {
                    if (!isset($entry['msgstr'])) {
                        $entry['msgstr'] = array();
                    }
                    $entry['msgstr'][] = $this->_dequote(substr($line, strpos($line, ' ') + 1));
                }
            }
            else if ($line[0] === '"' && isset($entry['msgstr'])) {
                $line = "\n" . preg_replace('/([^\\\\])\\\\n$/', "\$1\n", $this->_dequote($line));
                if (!is_array($entry['msgstr'])) {
                    $entry['msgstr'] .= $line;
                }
                else {
                    $entry['msgstr'][count($entry['msgstr']) - 1] .= $line;
                }
            }
        }
        return array($headers, $entries);
    }
}