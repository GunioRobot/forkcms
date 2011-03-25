if(!jsBackend) { jsBackend = new Object; }

jsBackend.link_checkerSettings =
{
	init: function()
	{
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
		
	},


	// end
	eoo: true
}

$(document).ready(jsBackend.link_checkerSettings.init);