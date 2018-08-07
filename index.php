<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>
<div class='container-fluid'>
<?php
	error_reporting(0);
	ini_set('display_errors', 0);
	require 'ics-parser/class.iCalReader.php';

	class student {
		var $netid;
		var $tl;
		var $event_start;
		var $event_end;
		var $event_rec;
		var $event_loc;
		var $curr_loc;
	}

	$students = array();
	$files = scandir('cals/');
	$tl_files = scandir('tl_cals/');
	$n = 0;
	echo '<table class="table table-bordered"><tr style="text-align: center; vertical-align: middle">
	<td style="width:7%"><button type="button" id="clear_but" class="menu_buts btn btn-primary">Clear All</button></td>
	<td style="width:7%">Consultant:</td><td style="width:14%"><select class="form-control" id="con_high"><option>None</option><optgroup label="Team Leads">';
	$list = '<option value="None">None</option>';
	foreach($tl_files as $file) {
		$students[$n] = new student;
		$ical = new ICal('tl_cals/'.$file);
		$temp = explode('.',$file);
		if($temp[0] == NULL) continue;
		$students[$n]->netid = $temp[0];
		$students[$n]->tl = 1;
		$list .= '<option >'.$temp[0].'</option>';
		echo '<option value="'.$temp[0].'">'.$temp[0].'</option>';
		$events = $ical->events();
		for($i = 0; $i < 6; $i++) $students[$n]->curr_loc[$i] = '';
		$i = 0;
		foreach ($events as $event) {
		    $students[$n]->event_start[$i] = $ical->iCalDateToUnixTimestamp($event['DTSTART']);
		    $students[$n]->event_end[$i] =  $ical->iCalDateToUnixTimestamp($event['DTEND']);
		    $temp = explode('BYDAY=',$event['RRULE']);
		    $students[$n]->event_rec[$i] = $temp[1];
		    $temp = explode('Building: ',$event['LOCATION']); 
		    $students[$n]->event_loc[$i] = $temp[1];
		    $i++;
		}
		$n++;
	}
	echo '</optgroup><optgroup label="Consultants">';
	foreach($files as $file) {
		$students[$n] = new student;
		$ical = new ICal('cals/'.$file);
		$temp = explode('.',$file);
		if($temp[0] == NULL) continue;
		$students[$n]->netid = $temp[0];
		$students[$n]->tl = 0;
		$list .= '<option>'.$temp[0].'</option>';
		echo '<option>'.$temp[0].'</option>';
		$events = $ical->events();
		for($i = 0; $i < 6; $i++) $students[$n]->curr_loc[$i] = '';
		$i = 0;
		foreach ($events as $event) {
		    //echo $students[$n]->netid."<br/>";
		    //echo "SUMMARY: ".$event['SUMMARY']."<br/>";
		    //echo "DTSTART: ".$event['DTSTART']." - UNIX-Time: ".$ical->iCalDateToUnixTimestamp($event['DTSTART'])."<br/>";
		    //echo "DTEND: ".$event['DTEND']."<br/>";
		    //echo "DTSTAMP: ".$event['DTSTAMP']."<br/>";
		    //echo "UID: ".$event['UID']."<br/>";
		    //echo "RRULE: ".$event['RRULE']."</br>";
		    //echo "DESCRIPTION: ".$event['DESCRIPTION']."<br/>";
		    //echo "LOCATION: ".$event['LOCATION']."<br/>";
		    //echo "<hr/>";
		    $students[$n]->event_start[$i] = $ical->iCalDateToUnixTimestamp($event['DTSTART']);
		    $students[$n]->event_end[$i] =  $ical->iCalDateToUnixTimestamp($event['DTEND']);
		    $temp = explode('BYDAY=',$event['RRULE']);
		    $students[$n]->event_rec[$i] = $temp[1];
		    $temp = explode('Building: ',$event['LOCATION']); 
		    $students[$n]->event_loc[$i] = $temp[1];
		    $i++;
		}
		$n++;
	}
	echo '</optgroup></select></td><td style="width:14%">Hours Available:</td><td style="width:7%" id="hours_av">0</td><td style="width:14%">Hours Assigned:</td>
	<td style="width:7%" id="hours_as">0</td>
	<td style="width:14%">
		<button type="button" id="grid_but" class="menu_buts btn btn-primary active">Grid</button>
		<button type="button" id="list_but" class="menu_buts btn btn-primary">List</button>
		<button type="button" id="view_but" class="menu_buts btn btn-primary">View</button>
	</td><td style="width:14%">
		<button type="button" id="save_but" class="menu_buts btn btn-primary">Save</button>
		<button type="button" id="load_but" class="menu_buts btn btn-primary">Load</button>
		<button type="button" id="help_but" class="menu_buts btn btn-primary">Help</button>
	</td></tr></table>';
?>
<table id="schedule" class="table table-bordered">
<?php
for($l=0;$l<7;$l++) {
	$start = 8;
	switch($l) {
		case 0: $dow = 'Mon';
			$hid = ' schedule';
			break;
		case 1: $dow = 'Tue';
			$hid = ' hidden schedule';
			break;
		case 2: $dow = 'Wed';
			$hid = ' hidden schedule';
			break;
		case 3: $dow = 'Thu';
			$hid = ' hidden schedule';
			break;
		case 4: $dow = 'Fri';
			$hid = ' hidden schedule';
			break;
		case 5: $dow = 'Sat';
			$hid = ' hidden schedule';
			break;
		case 6: $dow = 'Sun';
			$hid = ' hidden schedule';
			break;
	}
	$end = 9;
	echo '<tr class="'.$dow.$hid.'"><th style="width:7%" class="day_sel">'.$dow.'</th>';
	for($m=0;$m<13;$m++) {
		if($start > 12) $start = $start-12;
		if($end > 12) $end = $end-12;
		echo '<th style="width:7%" class="hour" id="hour_'.$l.'_'.$m.'">'.$start.' - '.$end.'</th>';
		$start++;
		$end++;
	}
	echo '</tr><tr class="'.$dow.$hid.'"><td></td><th colspan="13" class="text-center">Bevier</th></tr>';
	for($j=0;$j<18;$j++) {
		switch($j) {
			case 0: $role = 'Lead';
				break;
			case 1: $role = 'Phone1';
				break;
			case 2: $role = 'Phone2';
				break;
			case 3: $role = 'Phone3';
				break;
			case 4: $role = 'Phone4';
				break;
			case 5: $role = 'Room1';
				break;
			case 6: $role = 'Room2';
				break;
			case 7: echo '<tr class="'.$dow.$hid.'"><td></td><th colspan="13" class="text-center">DCL</th><tr>';
				$role = 'Lead1';
				break;
			case 8: $role = 'Lead2';
				break;
			case 9: $role = 'Phone1';
				break;
			case 10: $role = 'Phone2';
				break;
			case 11: $role = 'Phone3';
				break;
			case 12: $role = 'Phone4';
				break;
			case 13: $role = 'Phone5';
				break;
			case 14: $role = 'Phone6';
				break;
			case 15: $role = 'Phone7';
				break;
			case 16: $role = 'Phone8';
				break;
			case 17: $role = 'Phone9';
				break;
		}
		echo '<tr class="'.$dow.$hid.'"><th>'.$role.'</th>';
		for($i=0;$i<13;$i++) {
			if(($l == 5 || $l == 6) && ($i < 4 || $i > 8 || $j < 7 || $j == 8 || $j > 12)) echo '<td></td>';
			else if((($j == 5 || $j == 6) && ($i < 8 || $i == 12)) || (($j == 8 || $j > 12) && ($i > 8))) echo '<td></td>';
			else echo '<td id="output_'.$j.'-'.$l.'_'.$i.'" class="slot">None</td>';
		}
		echo "</tr>";
	}
}
?>
</table>

<table id="hours_table" class="table table-bordered hidden table-striped">
<?php
	foreach($students as $student) {
		if($student->tl) $con_tl = 'Team Lead';
		else $con_tl = 'Consultant';
		echo '<tr><td style="width:14%">'.$con_tl.'</td><td style="width:63%">'.$student->netid.'</td><td style="width:21%" id="hours_'.$student->netid.'">0</td></tr>';
	}
?>
</table>

<table id="data_table" class="table table-bordered hidden">
<tr><th></th><th>M</th><th>T</th><th>W</th><th>R</th><th>F</th><th>S</th><th>Su</th></tr>
<?php
$start = 8;
$end = 9;
$set = array();
for($l=0;$l<13;$l++) {
	$start_time = $start;
	$end_time = $end;
	if($start > 12) $start_time = $start-12;
	if($end > 12) $end_time = $end-12;
	echo "<tr><th>".$start_time." - ".$end_time."</th>";
	for($j=0;$j < 7;$j++) {
		if(($j == 5 || $j == 6) && ($l < 4 || $l > 8)) {
			echo '<td id="data_'.$j.'_'.$l.'"></td>';
		} else {
			echo '<td id="data_'.$j.'_'.$l.'">';
			for($i = 0;$i < $n;$i++) {
				$set[$i] = 0;
				for($k = 0; $k < sizeof($students[$i]->event_start); $k++) {
					$days = explode(',',$students[$i]->event_rec[$k]);
					$valid = 0;
					foreach($days as $day) {
						switch($j) {
							case 0: if($day == 'MO') $valid = 1;
								break;
							case 1: if($day == 'TU') $valid = 1;
								break;
							case 2: if($day == 'WE') $valid = 1;
								break;
							case 3: if($day == 'TH') $valid = 1;
								break;
							case 4: if($day == 'FR') $valid = 1;
								break;
							case 5: if($day == 'SA') $valid = 1;
								break;
							case 6: if($day == 'SU') $valid = 1;
								break;
						}
					}
					if($valid) {
						if((date("G",$students[$i]->event_start[$k]) >= $start && date("G",$students[$i]->event_start[$k]) < $end) ||  
							(date("G",$students[$i]->event_end[$k]) >= $start && date("G",$students[$i]->event_end[$k]) < $end) ||
							(date("G",$students[$i]->event_start[$k]) <= $start && date("G",$students[$i]->event_end[$k]) >= $end)) {
							$set[$i] = 1;
							$students[$i]->curr_loc[$j] = $students[$i]->event_loc[$k];
						}
					}
				}
				if($set[$i] == 0 && $students[$i]->netid != NULL) {
					echo $students[$i]->netid.',';
				}
			}
			echo "</td>";
		}
	}
	echo "</tr>";
	$start++;
	$end++;
}
?>
</table>

<table id="view_table" class="table table-bordered hidden">
<tr><th style="width:7%"></th><th style="width:14%">Monday</th><th style="width:14%">Tuesday</th><th style="width:14%">Wednesday</th><th style="width:14%">Thursday</th><th style="width:14%">Friday</th><th style="width:7%">Saturday</th><th style="width:7%">Sunday</th></tr>
<?php
	for($i=8;$i<21;$i++) {
		$i_hour = $i;
		$i_hour_1 = $i+1;
		if($i_hour > 12) $i_hour -= 12;
		if($i_hour_1 > 12) $i_hour_1 -= 12;
		echo '<tr><th>'.$i_hour.' - '.$i_hour_1.'</th>';
		for($j=0;$j<7;$j++) {
			echo '<td class="view_slot" id="view_'.$j.'_'.($i-8).'"></td>';
		}
		echo '</tr>';
	}
?>
</table>

<div id="consultants" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Select Consultant</h4>
      </div>
      <div class="modal-body">
	<table class="table table-bordered" id="consult_table">
	</table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<div id="load_dialog" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Load File</h4>
      </div>
      <div class="modal-body">
		<form class="form-inline">
		  <div class="form-group">
			<label class="sr-only" for="loadfile_name">Filename</label>
			<div class="input-group">
			  <div id="load_replace">
				<select class="form-control" id="loadfile_name">
			  <?php
				$save_files = scandir('saves/');
				foreach($save_files as $save) {
					if(strpos($save,'.json') !== false) {
						echo '<option value="'.$save.'">'.$save.'</option>';
					}
				}
			  ?>
			  </select>
			</div>
			</div>
		  </div>
		  <button type="button" class="btn btn-primary" id="loadfile_but">Load</button>
		</form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<div id="save_dialog" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Save File</h4>
      </div>
      <div class="modal-body">
		<form class="form-inline">
		  <div class="form-group">
			<label class="sr-only" for="save_filename">Filename</label>
			<div class="input-group">
			  <input type="text" class="form-control" id="save_filename" placeholder="filename">
			  <div class="input-group-addon">.json</div>
			</div>
		  </div>
		  <button type="button" class="btn btn-primary" id="savefile_but">Save</button>
		</form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<div id="days" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Select Day</h4>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
		<tr><td class="days_sel">Monday</td></tr>
		<tr><td class="days_sel">Tuesday</td></tr>
		<tr><td class="days_sel">Wednesday</td></tr>
		<tr><td class="days_sel">Thursday</td></tr>
		<tr><td class="days_sel">Friday</td></tr>
		<tr><td class="days_sel">Saturday</td></tr>
		<tr><td class="days_sel">Sunday</td></tr>
	</table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<div id="help_dialog" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Site Help</h4>
      </div>
      <div class="modal-body">
		<div class="row">
			<div class="col-md-12"><button type="button" class="menu_buts btn btn-default">Esc</button> : Close a dialog</div>
		</div>
		<br>
		<div class="row">
			<div class="col-md-12"><button type="button" class="menu_buts btn btn-default">Left Click</button> : Add consultant to spot</div>
		</div>
		<br>
		<div class="row">
			<div class="col-md-12"><button type="button" class="menu_buts btn btn-default">Right click</button> : Open list of available consultants for spot</div>
		</div>
		<br>
		<div class="row">
			<div class="col-md-12"><button type="button" class="menu_buts btn btn-default">Ctrl + C</button> : Select the consultant under cursor</div>
		</div>
		<br>
		<div class="row">
			<div class="col-md-12"><button type="button" class="menu_buts btn btn-default">Ctrl + V</button> : Add consultant to spot under cursor</div>
		</div>
		<br>
		<div class="row">
			<div class="col-md-12"><button type="button" class="menu_buts btn btn-default">Ctrl + X</button> : Remove consultant from spot under cursor</div>
		</div>
		<br>
		<div class="row">
			<div class="col-md-12"><button type="button" class="menu_buts btn btn-default">Tab</button> : Select next consultant in group</div>
		</div>
		<br>
		<div class="row">
			<div class="col-md-12"><button type="button" class="menu_buts btn btn-default">Shift + Tab</button> : Select previous consultant in group</div>
		</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

</div>
<script>
	<?php
		echo 'var students_array = '.json_encode($students).';';
	?>
	
	var clipboard;
	
	$(document).ready(function() {
		var consultants;
		var row_col;
		var row;
		var col;
		$(".hour").each(function() {
			row_col = $(this).attr("id").split('r_')[1];
			consultants = $("#data_"+row_col).text().split(',');
			$(this).text($(this).text() + '  (' + (parseInt(consultants.length)-1) + ')');
		});
	});

	$(".slot").hover(
		function() {
			$(this).addClass("active");
			$(this).focus();
		},
		function() {
			$(this).removeClass("active");
		}
	);

	$('html').bind({
		copy : function() {
			clipboard = $('.slot.active').text();
			$('#con_high').val(clipboard).trigger('change');
		},
		paste : function() {
			paste_con();
		},
		cut : function() {
			var old_con = $('.slot.active').text();
			if(old_con != 'None') {
				$('#hours_'+old_con).text(parseInt($('#hours_'+old_con).text())-1);
				if($('#con_high').val() == old_con) $('#hours_as').text($('#hours_'+old_con).text());
				var num_hours = $('#hours_as').text();
				$('#hours_as').removeClass('warning success danger');
				if(num_hours < 10) {
					$('#hours_as').addClass('warning');
				} else if(num_hours >= 10 && num_hours <= 20) {
					$('#hours_as').addClass('success');
				} else if(num_hours > 20) {
					$('#hours_as').addClass('danger');
				}
			}
			$('.slot.active').text('None');
			$('.slot.active').removeClass('info warning danger');
		}
	});

	$("html").on('keydown', function(e) {
		var keyCode = e.keyCode || e.which;
		if (keyCode == 9) {
			if(e.shiftKey) {
				e.preventDefault();
				if($('#con_high option:selected').prev().val() != undefined)
					$('#con_high').val($('#con_high option:selected').prev().val()).trigger('change');
			} else {
				e.preventDefault();
				if($('#con_high option:selected').next().val() != undefined)
					$('#con_high').val($('#con_high option:selected').next().val()).trigger('change');
			}
	    	}
	});
	
	$(document).keyup(function(e) {
		 if (e.keyCode == 27) {
			$('#consultants').modal('hide');
			$('#help_dialog').modal('hide');
			$('#save_dialog').modal('hide');
			$('#load_dialog').modal('hide');
			return false;
		}
	});

	function paste_con() {
		var old_con = $('.slot.active').text();
		var role = $('.slot.active').attr('id').split('-')[0].split('_')[1];
		var con = clipboard;
		var tl;
		var row_col = $('.slot.active').attr("id").split('-')[1];
		if(con == 'None') {
			$('.slot.active').removeClass('info warning');
			$('.slot.active').text(clipboard);
			if(old_con != 'None') $('#hours_'+old_con).text(parseInt($('#hours_'+old_con).text())-1);
		} else {
			if($('#hour_'+row_col).hasClass('success')) {
				for(var j = 0; j < 18; j++) {
					if($("#output_"+j+"-"+row_col).text() == con) return;
				}
				for(var i = 0; i< students_array.length; i++) {
					if(students_array[i].netid == con) tl = students_array[i].tl;
				}
				$('.slot.active').removeClass('info warning');
				if(tl) {
					$('.slot.active').text(clipboard);
					$('#hours_'+con).text(parseInt($('#hours_'+con).text())+1);
					if($('#con_high').val() == con) $('#hours_as').text($('#hours_'+con).text());
					var num_hours = $('#hours_as').text();
					$('#hours_as').removeClass('warning success danger');
					if(num_hours < 10) {
						$('#hours_as').addClass('warning');
					} else if(num_hours >= 10 && num_hours <= 20) {
						$('#hours_as').addClass('success');
					} else if(num_hours > 20) {
						$('#hours_as').addClass('danger');
					}
					$('.slot.active').addClass('info');
					if(old_con != 'None') $('#hours_'+old_con).text(parseInt($('#hours_'+old_con).text())-1);
					$('.slot.active').addClass('danger');
				} else {
					if(role != 0 && role != 7 && role != 8) {
						$('.slot.active').text(clipboard);
						$('#hours_'+con).text(parseInt($('#hours_'+con).text())+1);
						if($('#con_high').val() == con) $('#hours_as').text($('#hours_'+con).text());
						var num_hours = $('#hours_as').text();
						$('#hours_as').removeClass('warning success danger');
						if(num_hours < 10) {
							$('#hours_as').addClass('warning');
						} else if(num_hours >= 10 && num_hours <= 20) {
							$('#hours_as').addClass('success');
						} else if(num_hours > 20) {
							$('#hours_as').addClass('danger');
						}
						$('.slot.active').addClass('warning');
						if(old_con != 'None') $('#hours_'+old_con).text(parseInt($('#hours_'+old_con).text())-1);
						$('.slot.active').addClass('danger');
					}
				}
			}
		}
	}
	
	$(".slot").click(function() {
		paste_con();
	});

	$(".slot").contextmenu(
		function() {
			var len = $(this).attr("id").length;
			var old_con = $(this).text();
			var check = 0;
			var in_col = 1;
			var tl;
			var row_col_out = $(this).attr("id");
			var row_col = row_col_out.split('-')[1];
			var role = row_col_out.split('-')[0].split('_')[1];
			var content = '<table class="table table-bordered"><tr><td class="con_sel">None</td>';
			var consultants = $("#data_"+row_col).text().split(',');
			for(var i = 0; consultants[i] != ''; i++) {
				check = 0;
				for(var j = 0; j < 18; j++) {
					if($("#output_"+j+"-"+row_col).text() == consultants[i]) check = 1;
				}
				if(!check) {
					if(in_col%4 == 0) content += "<tr>";
					for(var k = 0; k< students_array.length; k++) {
						if(students_array[k].netid == consultants[i]) tl = students_array[k].tl;
					}
					if(tl) {
						content += "<td class='con_sel info'>"+ consultants[i] +"</td>";
					} else {
						if(role != 0 && role != 7 && role != 8) {
							content += "<td class='con_sel warning'>"+ consultants[i] +"</td>";
						}
					}
					if(in_col%4 == 3) content += "</tr>";
					in_col++;
				}
			}
			content += "</table>";
			$('#consult_table').html(content);
			$('#consultants').modal();

			$(".con_sel").hover(
				function() {
					$(this).addClass("active");
				},
				function() {
					$(this).removeClass("active");
				}
			);

			
			$(".con_sel").click(
				function() {
					var con = $(this).text();
					var tl;
					clipboard = con;
					$('#hours_'+old_con).text(parseInt($('#hours_'+old_con).text())-1);
					if(con == 'None') {
						$('#'+row_col_out).text(con);	
						$('#'+row_col_out).removeClass('info warning');
						$('#consultants').modal('hide');
						$('#con_high').val(con).trigger('change');
					} else {
						$('#'+row_col_out).text(con);
						$('#hours_'+con).text(parseInt($('#hours_'+con).text())+1);
						if($('#con_high').val() == con) $('#hours_as').text($('#hours_'+con).text());
						var num_hours = $('#hours_as').text();
						$('#hours_as').removeClass('warning success danger');
						if(num_hours < 10) {
							$('#hours_as').addClass('warning');
						} else if(num_hours >= 10 && num_hours <= 20) {
							$('#hours_as').addClass('success');
						} else if(num_hours > 20) {
							$('#hours_as').addClass('danger');
						}
						$('#consultants').modal('hide');
						for(var i = 0; i< students_array.length; i++) {
							if(students_array[i].netid == con) tl = students_array[i].tl;
						}
						$('#'+row_col_out).removeClass('info warning');
						if(tl) {
							$('#'+row_col_out).addClass('info');
						} else {
							$('#'+row_col_out).addClass('warning');
						}
						$('#con_high').val(con).trigger('change');
					}
				}
			);
			return false;
		}
	);
	
	$("#con_high").change(
		function() {
			var con = $(this).val();
			clipboard = con;
			var row_col = '';
			var total = 0;
			var loc = '';
			var i;
			var date_hour_start, date_hour_end;
			var days;
			var num_hours;
			console.log(students_array);
			$('.view_slot').removeClass('danger').text('');
			if(con == 'None') {
				$('.hour').removeClass('success');
				$('#hours_as').text('0');
				$('#hours_av').text('0');
				$('#hours_as').removeClass('warning success danger');
				for(var j = 0; j < 7;j++) {
					for(var i = 0; i < 13;i++) {
						$('#hour_'+j+'_'+i).html($('#hour_'+j+'_'+i).text());
					}
				}
			} else {
				row_col = '';
				$('.hour').removeClass('success');
				$('#schedule').find("td").each(function() {
					$(this).removeClass('danger');
					if($(this).text() == con) {
						$(this).addClass('danger');
					}	
				});
				$("#data_table").find("td").each(function() {
					if($(this).text().indexOf(con) != -1) {
						row_col = $(this).attr("id").substr(5,10);
						$("#hour_"+row_col).html($("#hour_"+row_col).text());
						$("#hour_"+row_col).addClass("success");
						total++;
					} else {
						row_col = $(this).attr("id").split('a_')[1];
						row = row_col.split('_')[0];
						col = row_col.split('_')[1];
						console.log(row+' '+col);
						for(i = 0; i < students_array.length; i++) {
							if(students_array[i].netid == con) break;
						}
						for(var j = 0; j < students_array[i].event_start.length; j++) {
							date_hour_start = new Date(1000*(parseInt(students_array[i].event_start[j])));
							date_hour_end = new Date(1000*(parseInt(students_array[i].event_end[j])));
							date_hour_s = date_hour_start.setHours(date_hour_start.getHours());
							date_hour_e = date_hour_end.setHours(date_hour_end.getHours());
							date_s = new Date(date_hour_s);
							date_e = new Date(date_hour_e);
							days = students_array[i].event_rec[j].split(',');
							var valid = 0;
							switch(row) {
								case '0': if(days.indexOf('MO') != -1) valid = 1;
									break;
								case '1': if(days.indexOf('TU') != -1) valid = 1;
									break;
								case '2': if(days.indexOf('WE') != -1) valid = 1;
									break;
								case '3': if(days.indexOf('TH') != -1) valid = 1;
									break;
								case '4': if(days.indexOf('FR') != -1) valid = 1;
									break;
								case '5': if(days.indexOf('SA') != -1) valid = 1;
									break;
								case '6': if(days.indexOf('SU') != -1) valid = 1;
									break;
							}
							if(valid &&
								(((date_s.getHours() >= (parseInt(col)+8)) && (date_s.getHours() < (parseInt(col)+9))) ||
								((date_e.getHours() >= (parseInt(col)+8) && date_e.getHours()  < (parseInt(col)+9))) ||
								((date_s.getHours()  <= (parseInt(col)+8) && date_e.getHours()  >= (parseInt(col)+9))))) {
								loc = students_array[i].event_loc[j];
								break;
							} else {
								loc = 'home';
							}
						}
						if(loc != 'home') $('#view_'+row+'_'+col).addClass('danger').text(loc);
						$('#hour_'+row_col).html('<a href="#" data-toggle="tooltip" data-placement="top" data-container="body" title="'+loc+'">'+$('#hour_'+row_col).text()+'</a>');	
					}
					$('#hours_av').text(total);
					$('#hours_as').text($('#hours_'+con).text());
					var num_hours = $('#hours_as').text();
					$('#hours_as').removeClass('warning success danger');
					if(num_hours < 10) {
						$('#hours_as').addClass('warning');
					} else if(num_hours >= 10 && num_hours <= 20) {
						$('#hours_as').addClass('success');
					} else if(num_hours > 20) {
						$('#hours_as').addClass('danger');
					}
				});
			}
			$('[data-toggle="tooltip"]').tooltip();
		}
	);

	$(".days_sel").hover(
		function() {
			$(this).addClass("active");
		},
		function() {
			$(this).removeClass("active");
		}
	);

	$(".days_sel").click(
		function() {
			var day = $(this).text();
			switch(day) {
				case 'Monday': $(".schedule").addClass("hidden");
					$(".Mon").removeClass("hidden");
					break;
				case 'Tuesday': $(".schedule").addClass("hidden");
					$(".Tue").removeClass("hidden");
					break;
				case 'Wednesday': $(".schedule").addClass("hidden");
					$(".Wed").removeClass("hidden");
					break;
				case 'Thursday': $(".schedule").addClass("hidden");
					$(".Thu").removeClass("hidden");
					break;
				case 'Friday': $(".schedule").addClass("hidden");
					$(".Fri").removeClass("hidden");
					break;
				case 'Saturday': $(".schedule").addClass("hidden");
					$(".Sat").removeClass("hidden");
					break;
				case 'Sunday': $(".schedule").addClass("hidden");
					$(".Sun").removeClass("hidden");
					break;
			}
			$('#days').modal('hide');
		}
	);

	$(".day_sel").hover(
		function() {
			$(this).addClass("active");
		},
		function() {
			$(this).removeClass("active");
		}
	);

	$(".day_sel").click(function() {
			$('#days').modal();
	});
	
	$("#list_but").click(function() {
		$('.menu_buts').removeClass('active');
		$('#list_but').addClass('active');
		$('#schedule').addClass('hidden');
		$('#hours_table').removeClass('hidden');
		$('#view_table').addClass('hidden');
		$('#hours_table').find('tr').each(function() {
				var num_hours = parseInt($(this).find('td').eq(2).text());
				$(this).find('td').eq(2).removeClass('warning success danger');
				if(num_hours < 10) {
					$(this).find('td').eq(2).addClass('warning');
				} else if(num_hours >= 10 && num_hours <= 20) {
					$(this).find('td').eq(2).addClass('success');
				} else if(num_hours > 20) {
					$(this).find('td').eq(2).addClass('danger');
				}
		});
	});

	$("#grid_but").click(function() {
		$('.menu_buts').removeClass('active');
		$('#grid_but').addClass('active');
		$('#schedule').removeClass('hidden');
		$('#hours_table').addClass('hidden');
		$('#view_table').addClass('hidden');
	});

	$("#view_but").click(function() {
		$('.menu_buts').removeClass('active');
		$('#view_but').addClass('active');
		$('#schedule').addClass('hidden');
		$('#hours_table').addClass('hidden');
		$('#view_table').removeClass('hidden');
	});
		
	
	$("#clear_but").click(function() {
		$('#schedule').find('.slot').each(function() {
			$(this).text('None');
			$(this).removeClass('warning info danger');
		});
		$('#hours_table').find('tr').each(function() {
				$(this).find('td').eq(2).text('0');
				$(this).find('td').eq(2).removeClass('warning success danger');
		});
		$('#con_high').val('None').trigger('change');
	});
	
	$("#help_but").click(function() {
		$('#help_dialog').modal();
	});
	$("#save_but").click(function() {
		$('#save_dialog').modal();
	});
	
	var isValid=(function(){
		var rg1=/^[^\\/:\*\?"<>\|]+$/; // forbidden characters \ / : * ? " < > |
		var rg2=/^\./; // cannot start with dot (.)
		var rg3=/^(nul|prn|con|lpt[0-9]|com[0-9])(\.|$)/i; // forbidden file names
		return function isValid(fname){
			return rg1.test(fname)&&!rg2.test(fname)&&!rg3.test(fname);
		}
	})();
	
	$("#savefile_but").click(function() {
		var save_table = [];
		var filename = $("#save_filename").val();
		if(filename == '') {
			alert('Name cannot be empty');
			return false;
		}
		if(!isValid(filename)) {
			alert('Name cannot contain special characters');
			return false;
		}
		$('.slot').each(function() {
			save_table.push({
				row_col : this.id,
				con : $(this).text()
			});
		});
		$.ajax({
			type: "POST",
			dataType: 'json',
			url: 'save_json.php',
			data: { filename : filename,
				data: JSON.stringify(save_table)
			},		
		});
		$("#save_dialog").modal("hide");
	});
	
	$("#load_but").click(function() {
		$('select').children('option:not(:first)').remove();
		$.ajax({
			url: "refresh_list.php", 
			success: function(result){
				$("#load_replace").html(result);
			}
		});
		$('#load_dialog').modal();
	});
	
	$("#loadfile_but").click(function() {
		var load_table;
		var tl;
		var con;
		var filename = $("#loadfile_name").val();
		$('#hours_as').removeClass('warning success danger');
		$('#hours_as').text('0');
		$('#hours_table').find('tr').each(function() {
			$(this).find('td').eq(2).text('0');
			$(this).find('td').eq(2).removeClass('warning success danger');
		});
		load_table = $.getJSON("saves/"+filename, function(data) {
			$.each(data, function(index,val) {
				$("#"+val.row_col).removeClass("info danger warning");
				$("#"+val.row_col).text(val.con);
				
				if(val.con != 'None') {
					$('#hours_'+val.con).text(parseInt($('#hours_'+val.con).text())+1);
					for(i = 0; i < students_array.length; i++) {
						if(students_array[i].netid == val.con) tl = students_array[i].tl;
					}
					if(tl) {
						$("#"+val.row_col).addClass("info");
					} else {
						$("#"+val.row_col).addClass("warning");
					}
					if($("#con_high").val() == val.con) $("#"+val.row_col).addClass("danger");
				}
			});
		});
		
		$('#con_high').val('None').trigger('change');
		$('.menu_buts').removeClass('active');
		$('#grid_but').addClass('active');
		$('#schedule').removeClass('hidden');
		$('#hours_table').addClass('hidden');
		$('#load_dialog').modal('hide');
	});
</script>

<style>

.slot {
	cursor: pointer;
}

.con_sel {
	cursor: pointer;
}

.day_sel {
	cursor: pointer;
}

.days_sel {
	cursor: pointer;
}
a, a:active, a:focus {
    text-decoration: none;
    outline: none;
}

.menu {
	cursor: pointer;
	text-align: center;
	vertical-align: middle;
}

body {
	background-color: #13294b;
}

table {
	background-color: #ffffff;
}
</style>
</body>
</html>
