if(!jsBackend) { var jsBackend = new Object(); }


/**
 * Interaction for the slideshows module
 *
 * @author	Dave Lens <dave@netlash.com>
 */
jsBackend.slideshows = 
{
	// init, something like a constructor
	init: function() 
	{
		var module = $('#module');

		jsBackend.slideshows.toggleMethods(module);

		if(module.length > 0) 
		{
			module.live('change', function()
			{
				jsBackend.slideshows.toggleMethods(module);
				
				jsBackend.slideshows.loadMethods($(this).val());
			});
		}
	},
	
	
	loadMethods: function(module)
	{
		$.ajax(
		{
			url: '/backend/ajax.php?module='+ jsBackend.current.module +'&action=get_methods&language={$LANGUAGE}',
			data: 'm=' + module,
			success: function(json, textStatus)
			{
				if(json.code != 200)
				{
					// show error if needed
					if(jsBackend.debug) alert(textStatus);
				}
				else
				{
					$('#methods').html(json.data);
				}
			}
		});
	},

	
	toggleMethods: function(module)
	{
		if(module.val() == '0' || module.val() == undefined)
		{
			$('#methods').hide();
			$('#measurements').show();
		}
		
		else
		{
			$('#methods').show();
			$('#measurements').hide();
		}
	},
	
	
	eoo: true
}


$(document).ready(jsBackend.slideshows.init);