<?php
	echo '<select class="form-control" id="loadfile_name">';
	$save_files = scandir('saves/');
	foreach($save_files as $save) {
		if(strpos($save,'.json') !== false) {
			echo '<option value="'.$save.'">'.$save.'</option>';
		}
	}
	echo '</select>';
?>