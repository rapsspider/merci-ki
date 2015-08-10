<?php

if (!function_exists('get_real_class')) {
	
	function get_real_class($obj) {
	    $classname = get_class($obj);

	    if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
	        $classname = $matches[1];
	    }

	    return $classname;
	}

}

?>