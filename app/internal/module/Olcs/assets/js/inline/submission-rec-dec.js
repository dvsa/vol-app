OLCS.ready(function() {
	
	function trimOptions()
	{
		jsSubLegislation = $('.js-sub-legislation');
	}
	
	
	$('.js-sub_st_rec').change(function() {
		
		var value = $(this).val()
		
		if ('sub_st_rec_pi' == value) {
		
			trimOptions();
		}
		
	});
});