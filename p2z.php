<?php

$ignore=[
	'.idea',
	'.git',
	'p2z.php',
    '/assets/node_modules/'
];
$version='1.0.0';
$source=__DIR__; //false for parent directory
$destination=__DIR__;//false for up directory destination


//not need to do anything only use up variable
function zipData($ignore=array(),$version=false,$source=__DIR__, $destination=__DIR__.'.zip') {
	if (extension_loaded('zip')) {
		if (file_exists($source)) {
			$zip = new ZipArchive();
			$destination=(strpos($destination,'.zip')===false)? $destination.'-'.$version.'-'.time().'.zip':$destination;

			if ($zip->open($destination, ZIPARCHIVE::CREATE)) {
				$source = realpath($source);
				$source=str_replace('\\','/',$source);
				if (is_dir($source)) {
					$iterator = new RecursiveDirectoryIterator($source);
					// skip dot files while iterating
					$iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
					$files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
					$ignore_array=[];
					foreach ($ignore as $item){
						$ignore_array[]=$source.'/'.trim($item,'/\\');
					}
					$source=dirname($source,1);
					foreach ($files as $file) {
						$file = realpath($file);
						$file=str_replace('\\','/',$file);
						$continue=false;
						foreach ( $ignore_array as $i ) {
							if ($continue===false)
							if (strpos($file,$i)!==false) $continue=true;
						}
						if ($continue) continue;

						if (is_dir($file)) {
							$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
						} else if (is_file($file)) {
							$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
						}
					}
				} else if (is_file($source)) {
					$zip->addFromString(basename($source), file_get_contents($source));
				}
			}
			return $zip->close();
		}
	}
	return false;
}
zipData($ignore,$version,$source,$destination);