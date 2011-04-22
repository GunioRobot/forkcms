if(!jsBackend) { jsBackend = new Object; }

jsBackend.link_checkerSettings =
{
	init: function()
	{
		// @todo	jeroen: Not really noticable for small bits of JS code, but it's performance-friendly to cache frequently recurring jQuery objects by storing them in a var. Poke me if I'm not clear enough. ^^
		
		// disable the numConnections input field when the multicall checkbox is unchecked
		
		$('#numConnections').attr("disabled", "");
		
		if(!$('#multiCall').attr("checked"))
		{
			// disable input field
			$('#numConnections').attr("disabled", "disabled");
		}
		
		$('#multiCall').change(function ()
		{			
			if(!$(this).attr("checked"))
			{
				// disable input field
				$('#numConnections').attr("disabled", "disabled");
			}
			else
			{
				// enable input field
				$('#numConnections').attr("disabled", "");
			}
		});	
		
		
		// disable the cacheTime input field when the cacheDeadLinks checkbox is unchecked
		
		$('#cacheTime').attr("disabled", "");
		
		if(!$('#cacheLinks').attr("checked"))
		{
			// disable input field
			$('#cacheTime').attr("disabled", "disabled");
		}
		
		$('#cacheLinks').change(function ()
		{			
			if(!$(this).attr("checked"))
			{
				// disable input field
				$('#cacheTime').attr("disabled", "disabled");
			}
			else
			{
				// enable input field
				$('#cacheTime').attr("disabled", "");
			}
		});	
		
	},


	// end
	eoo: true
}

$(document).ready(jsBackend.link_checkerSettings.init);