<?php 
if (php_sapi_name() == 'cli') {
    define( 'LR', "\n");
} else {
	define( 'LR', "<br/>\n");
}

?>