$(document).bind('pageinit', function() {
	$('select#isTeam').parent().find('.ui-slider-switch').css('width', '7em');
	$('select#isLan').parent().find('.ui-slider-switch').css('width', '7em');
	$('select#isScheduled').parent().find('.ui-slider-switch').css('width', '10em');
	$('#competitions-filter').prev().css('margin-top', '30px');
	
	$('select#isTeam').change(function() {
		var allowedClass = $(this).val() == 1 ? 'team-compliant' : 'solo-compliant';
		$('select#title option').prop('disabled', false).not('.'+allowedClass).prop('disabled', true);
		if(!$('select#title option:selected').is('.'+allowedClass))
			$('select#title').val($('select#title option.'+allowedClass).first().val())
		$('select#title').selectmenu('refresh').trigger('change');
		if($(this).val() == 1)
			$('select#isScheduled').slider('disable').val(1).trigger('change');
		else
			$('select#isScheduled').slider('enable');
	}).trigger('change');
	
	$('select#title').change(function() {
		if($(':selected', this).hasClass('remote-compliant'))
			$('select#useRemote').slider('enable').closest('li:jqmData(role="fieldcontain")').show();
		else
			$('select#useRemote').slider('disable').closest('li:jqmData(role="fieldcontain")').hide();
	}).trigger('change');
	
	$('select#isScheduled, select#title, select#isTeam').change(function() {
		if($('select#isScheduled').val() == 1 && $('select#title option:selected').hasClass('openqualifiers-compliant') && $('select#isTeam').val() == 0)
			$('select#hasOpenQualifiers').slider('enable').closest('li:jqmData(role="fieldcontain")').show();
		else
			$('select#hasOpenQualifiers').slider('disable').closest('li:jqmData(role="fieldcontain")').hide();
	}).trigger('change');
	
	$('select#gamemode').change(function() {
		var selected = $(this).children(':selected');
		$('fieldset[id|=settings]').hide().find('input').prop('disabled', true);
		$('fieldset#settings-'+selected.jqmData('mode-id')).show().find('input').prop('disabled', false);
		if(selected.jqmData('slots-limit'))
			$('input.conditional-readonly').prop('readonly', true).val(selected.jqmData('slots-limit')).trigger('change');
		else
			$('input.conditional-readonly').prop('readonly', false);
	}).trigger('change');
});