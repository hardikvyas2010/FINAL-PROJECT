<?php


require("POParser.php");
$target = "ukrainian";

$copyright = "<?php
/**
 * Translation file
 * @copyright  Copyright (c) 2014-2015 Benjamin BALET
 * @license     http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link          https://github.com/bbalet/jorani
 * @since       0.1.0
 */\n\n";


$parser = new POParser;
$messages = $parser->parse($target . '.po');
$lenPO = count($messages[1]);


$files = scandir($target);
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        $path = join_paths($target, $file);
        $ci18n = file_get_contents($path);

    
        $pattern = "\$lang\['(.*)'\] = '(.*)';$";
        $out = array();
        preg_match_all($pattern, $ci18n, $out, PREG_PATTERN_ORDER);
        $lenI18N = count($out[0]);
        for ($jj = 0; $jj < $lenI18N; $jj++) {
            for ($ii = 0; $ii < $lenPO; $ii++) {
                $po2ci = str_replace('\"', '"', $messages[1][$ii]['msgid']);
				$po2ci = str_replace("'", '\'', $po2ci);
                if ($out[2][$jj] != '') {
                    if (strcmp($po2ci, $out[2][$jj]) == 0) {
					    $po2ci = str_replace('\"', '"', $messages[1][$ii]['msgstr']);
						$po2ci = str_replace("'", '\'', $po2ci);
                        if ($messages[1][$ii]['msgstr'] != '') {
                            $out[2][$jj] = $po2ci;
                        }
                    }
                }
            }
        }

  
        $output = $copyright;
        for ($jj = 0; $jj < $lenI18N; $jj++) {
            $output .= '$lang[\'' . $out[1][$jj] . "'] = '" . $out[2][$jj] . "';" . PHP_EOL;
        }
        file_put_contents($path, $output);
    }
}

	
function join_paths() {
    $paths = array();
    foreach (func_get_args() as $arg) {
        if ($arg !== '') {
            $paths[] = $arg;
        }
    }
    return preg_replace('#/+#', '/', join(DIRECTORY_SEPARATOR, $paths));
}
