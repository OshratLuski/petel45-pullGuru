require(['jquery', 'jqueryui'], function($) {
	if($(".formulation .ablock input[name*='other']").length > 0)
	{
		//init other status
		if($("input[name*='other']").is(':checked')) {
			$("textarea[name*='other']").show();
		} else {
			$("textarea[name*='other']").hide();
		}

		//radio event
		$("input[type=radio][name*='other'],input[type=radio][name*='answer']").change(function() {
			$(this).parent().parent().find("input[type=radio][name*='other'],input[type=radio][name*='answer']").not(this).prop('checked', false);

			if($(this).is("input[name*='other']:checked")) {
				$("textarea[name*='other']").show();
			} else {
				$("textarea[name*='other']").hide();
			}
		});

		$("input[type=checkbox][name*='other']").change(function () {
			if($(this).is(':checked')) {
				$("textarea[name*='other']").show();
			} else {
				$("textarea[name*='other']").hide();
			}
		});
	}
});