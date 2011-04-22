if(!jsBackend) { jsBackend = new Object; }

jsBackend.link_checkerSettings =
{
	init: function()
	{
		// @todo  jeroen: Not really noticable for small bits of JS code, but it's performance-friendly to cache frequently recurring jQuery objects by storing them in a var. Poke me if I'm not clear enough. ^^
				
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