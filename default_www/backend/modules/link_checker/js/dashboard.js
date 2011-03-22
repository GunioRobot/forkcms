if(!jsBackend) { jsBackend = new Object; }

jsBackend.link_checkerDashboard =
{
	init: function()
	{
		$('#refreshLinks').bind('click', function ()
		{
			// disable button
			$('#refreshLinks').addClass('disabledButton');

			// make the call to check the status
			$.ajax(
			{
				cache: false,
				type: 'POST',
				timeout: 50000000,
				url: '/backend/ajax.php?module=link_checker&action=refresh_links&language=' + jsBackend.current.language,
				success: function(data, textStatus)
				{
					if(data.code == 200)
					{
						// show new data
						//$('#datagridAll').html(data.data.all);
						//$('#datagridInternal').html(data.data.internal);
						//$('#datagridExternal').html(data.data.external);

						// show message
						jsBackend.messages.add('success', data.data.message);
					}
					else
					{
						// show message
						jsBackend.messages.add('error', textStatus);
					}

					// enable button
					$('#refreshLinks').removeClass('disabledButton');

					// alert the user
					if(data.code != 200 && jsBackend.debug) { alert(data.message); }
				},
				error: function(XMLHttpRequest, textStatus, errorThrown)
				{
					// enable button
					$('#refreshLinks').removeClass('disabledButton');

					// alert the user
					if(jsBackend.debug) alert(textStatus);
				}
			});
		});
	},


	// end
	eoo: true
}


$(document).ready(jsBackend.link_checkerDashboard.init);