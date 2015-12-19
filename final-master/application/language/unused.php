<?php

echo "Include all translation files" . PHP_EOL;
$files = scandir('english');
foreach ($files as $file) {
    if ($file != '.' && $file != '..' && $file != 'index.html') {
        $path = join_paths("english", $file);
        include $path;
    }
}
echo 'We\'ve found ' . count($lang) . '  i18n keys' . PHP_EOL;

echo "Iterate through the views of the application..." . PHP_EOL;
$path = realpath(join_paths(dirname(getcwd()), 'views'));
echo $path . PHP_EOL;

$directory = new RecursiveDirectoryIterator ($path);
$iterator = new RecursiveIteratorIterator($directory);
$regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
foreach ($regex as $file) {
    
    $content = file_get_contents($file[0]);
    
    foreach ($lang as $key => $message) {
       
        if (strpos($content, $key) !== false) {
            
            unset($lang[$key]);
        }
    }
}

echo "Iterate through the controllers of the application..." . PHP_EOL;
$path = realpath(join_paths(dirname(getcwd()), 'controllers'));
echo $path . PHP_EOL;

$directory = new RecursiveDirectoryIterator ($path);
$iterator = new RecursiveIteratorIterator($directory);
$regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
foreach ($regex as $file) {
    
    $content = file_get_contents($file[0]);
   
    foreach ($lang as $key => $message) {
        
        if (strpos($content, $key) !== false) {
          
            unset($lang[$key]);
        }
    }
}

echo 'List the ' . count($lang) . ' unused i18n keys' . PHP_EOL;
echo "_______________________________________________" . PHP_EOL;
foreach ($lang as $key => $message) {
    
    echo $key . PHP_EOL;
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
