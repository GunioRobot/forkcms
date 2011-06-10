if(!jsBackend) { var jsBackend = new Object(); }

jsBackend.{$module.name} =
{
	// init, something like a constructor
	init: function()
	{
		
	},

	// end of object
	eoo: true
}

$(document).ready(function() { jsBackend.{$module.name}.init(); });