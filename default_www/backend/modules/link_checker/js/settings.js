if(!jsBackend) { jsBackend = new Object; }

jsBackend.link_checkerSettings =
{
	init: function()
	{
		// disable and hide the numConnections input field when the multicall checkbox is unchecked
		
		$('#numConnections').attr("disabled", "");
		
		if(!$('#multiCall').attr("checked"))
		{
			// disable input field
			$('#numConnections').attr("disabled", "disabled");
			
			// hide the input field
			$('#setConnections').hide();
		}
		
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
		
		$('#cacheTime').attr("disabled", "");
		
		if(!$('#cacheLinks').attr("checked"))
		{
			// disable input field
			$('#cacheTime').attr("disabled", "disabled");
			
			// hide the input field
			$('#setCache').hide();
		}
		
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