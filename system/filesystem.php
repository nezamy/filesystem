<?php
namespace System;
use \SplFileInfo;
class FileSystem
{

	public function all()
	{
		pre($this);
	}

	public function get($f){
		return new SplFileInfo($f);
	}

	public function makeFile($f, $override=false)
	{
		$fs = $this->get($f);
		if($override === false && $fs->isFile())
		{
			return $fs;
		}
		else
		{
			$this->makeDir( $fs->getPath() );
			return $fs->openFile('w');
		}
	}

	public function makeDir($dir, $mode=0755){
		$return =  (is_dir($dir) || mkdir($dir, $mode, true)) ? $dir : false;
		if($return) chmod($return, $mode);
		return $return;
	}

	public function copyDir($source, $dest, $permissions = 0755)
	{
		// If source is not a directory stop processing
	    if(!is_dir($source)) return false;

	    // If the destination directory does not exist create it
	    if(!is_dir($dest)) {
	        if(!mkdir($dest)) {
	            // If the destination directory could not be created stop processing
	            return false;
	        }
	    }

	    // Open the source directory to read in files
	    $i = new \DirectoryIterator($source);
	    foreach($i as $f) {
	        if($f->isFile()) {
	            copy($f->getRealPath(), "$dest/" . $f->getFilename());
	        } else if(!$f->isDot() && $f->isDir()) {
	            $this->copyDir($f->getRealPath(), "$dest/$f");
	        }
	    }

	    return true;
	}




	public function deleteDir($dir)
	{
		if(file_exists($dir)){
		   	$files = array_diff(scandir($dir), array('.','..'));
		    foreach ($files as $file) {
		    	(is_dir("$dir/$file")) ? $this->deleteDir("$dir/$file") : unlink("$dir/$file");
		    }
		    return rmdir($dir);
		} return false;
	}

	public function emptyDir($path){
		try{
			$iterator = new DirectoryIterator($path);
			foreach ( $iterator as $fileinfo )
			{
				if($fileinfo->isDot())continue;

				if($fileinfo->isDir()){
					if($this->emptyDir($fileinfo->getPathname()))
					rmdir($fileinfo->getPathname());
				}

				if($fileinfo->isFile()){
					unlink($fileinfo->getPathname());
				}
			}

			return true;
		} catch ( Exception $e ){
			return false;
		}
    }

}
