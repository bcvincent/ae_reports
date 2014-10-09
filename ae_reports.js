/*
jQuery( document ).ready(function(){
	$( "#start" ).datepicker();
	$( "#end" ).datepicker();
});
*/

jQuery( document ).ready(function() {
  $( "#tot_start" ).datepicker({
    changeMonth: true,
    changeYear: true,
    minDate: null
  });
  $( "#tot_end" ).datepicker({
    changeMonth: true,
    changeYear: true,
    minDate: null
  });
  $( "#teacher_start" ).datepicker({
    changeMonth: true,
    changeYear: true,
    minDate: null
  });
  $( "#teacher_end" ).datepicker({
    changeMonth: true,
    changeYear: true,
    minDate: null
  });
  $( "#avg_start" ).datepicker({
    changeMonth: true,
    changeYear: true,
    minDate: null
  });
  $( "#avg_end" ).datepicker({
    changeMonth: true,
    changeYear: true,
    minDate: null
  });
});	