if(!jsBackend) { jsBackend = new Object; }

jsBackend.link_checkerSettings =
{
	init: function()
	{
		$('#clearLinkCache').bind('click', function ()
		{
			// disable button
			$('#clearLinkCache').addClass('disabledButton');

			// make the call to check the status
			$.ajax(
			{
				url: '/backend/ajax.php?module=link_checker&action=clear_cache&language=' + jsBackend.current.language,
				success: function(data, textStatus)
				{
					if(data.code == 200)
					{
						// show message
						jsBackend.messages.add('success', data.data.message);						
					}
					else
					{
						// show message
						jsBackend.messages.add('error', textStatus);
					}

					// enable button
					$('#clearLinkCache').removeClass('disabledButton');

					// alert the user
					if(data.code != 200 && jsBackend.debug) { alert(data.message); }
				},
				error: function(XMLHttpRequest, textStatus, errorThrown)
				{
					// enable button
					$('#clearLinkCache').removeClass('disabledButton');

					// alert the user
					if(jsBackend.debug) alert(textStatus);
				}				
			});			
		});
		
		
		// disable and hide the numConnections input field when the multicall checkbox is unchecked
		
		// add the disabled attribute
		$('#numConnections').attr("disabled", "");
		
		if(!$('#multiCall').attr("checked"))
		{
			// disable input field
			$('#numConnections').attr("disabled", "disabled");
			
			// hide the input field
			$('#setConnections').hide();
		}
		
		// checkbox onchange
		$('#multiCall').change(function ()
		{			
			if(!$(this).attr("checked"))
			{
				// disable input field
				$('#numConnections').attr("disabled", "disabled");
				
				// hide the input field
				$('#setConnections').hide();
			}
			else
			{
				// enable input field
				$('#numConnections').attr("disabled", "");
				
				// show the input field
				$('#setConnections').show();
			}
		});	
		
		
		// disable and hide the cacheTime input field when the cacheDeadLinks checkbox is unchecked
		
		// add the disabled attribute
		$('#cacheTime').attr("disabled", "");
		
		if(!$('#cacheLinks').attr("checked"))
		{
			// disable input field
			$('#cacheTime').attr("disabled", "disabled");
			
			// hide the input field
			$('#setCache').hide();
		}
		
		// checkbox onchange
		$('#cacheLinks').change(function ()
		{			
			if(!$(this).attr("checked"))
			{
				// disable input field
				$('#cacheTime').attr("disabled", "disabled");
				
				// hide the input field
				$('#setCache').hide();
			}
			else
			{
				// enable input field
				$('#cacheTime').attr("disabled", "");
				
				// show the input field
				$('#setCache').show();
			}
		});	
		
	},


	// end
	eoo: true
}

$(document).ready(jsBackend.link_checkerSettings.init);