/*
jQuery( document ).ready(function(){
	$( "#start" ).datepicker();
	$( "#end" ).datepicker();
});
*/

jQuery( document ).ready(function() {
  $( "#start" ).datepicker({
    changeMonth: true,
    changeYear: true,
    minDate: null
  });
  $( "#end" ).datepicker({
    changeMonth: true,
    changeYear: true,
    minDate: null
  });
});	