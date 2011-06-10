if(!jsBackend) { var jsBackend = new Object(); }

jsBackend.manage =
{
	// init, something like a constructor
	init: function()
	{
		$('ul#settings').cloneControl({
			addButtonID : 'addSettings',
			onAdd : function(index, clone)
			{
				// alter the index in all necessary attributes for this field
				clone.find('#values_'+ (index - 1))
					.attr('id', 'values_'+ index)
					.attr('value', '');
				
				// alter the index in all necessary attributes for this field
				clone.find('#settings_'+ (index - 1))
					.attr('id', 'settings_'+ index)
					.attr('value', '')
					.siblings('label[for="settings_'+ (index - 1) +'"]')
					.attr('for', 'settings_' + index);
			}
		});
		
		$('ul#dataFields').cloneControl({
			addButtonID : 'addDataFields',
			onAdd : function(index, clone)
			{
				// alter the index in all necessary attributes for this field
				clone.find('#types_'+ (index - 1))
					.attr('id', 'types_'+ index)
					.siblings('label[for="types_'+ (index - 1) +'"]')
					.attr('for', 'types_' + index);
				
				// alter the index in all necessary attributes for this field
				clone.find('#locations_'+ (index - 1))
					.attr('id', 'locations_'+ index);

				// alter the index in all necessary attributes for this field
				clone.find('#names_'+ (index - 1))
					.attr('id', 'names_'+ index)
					.attr('value', '');
			}
		});
	},

	// end
	eoo: true
}

$(document).ready(function() { jsBackend.manage.init(); });




/**
 * Clones a paragraph along with a delete action. Use the onAdd callback to do funky shizzle
 * 
 * Example:
 * 
 * 	JS:
 * 	$('#settings').cloneControl();
 * 	
 * 	HTML:
 * 	
 *	<div id="settings">
 *		<p>
 *			<span>Do funky shizzle here!</span> <a class="deleteBox" href="#deleteBox">{$lblDeleteSetting|ucfirst}</a>
 *		</p>
 *	</div>
 *	<p>
 *		<a id="addBox" href="#addBox" class="button icon iconAdd" title="{$lblAdd|ucfirst}">
 *			<span>Add more boxes</span>
 *		</a>
 *	</p>
 */
(function($){
	$.fn.cloneControl = function(options)
	{
		// default settings
		var settings = {
			'addButtonID'			: 'addBox',					// the ID of the add button
			'deleteButtonClass'		: 'deleteBox',				// the class of the delete buttons
			'listElement'			: 'li',						// the wrapper element to be cloned 
			'onAdd'					: function(index, clone){}	// the callback function that is executed after a clone action
	    };
		
		// apply to all selectors
		return this.each(function()
		{        
			// options exist
		    if(options)
		    {
		    	// lets merge them with our default settings
		    	$.extend(settings, options);
		    }
		    
		    // cache objects
		    var self = this;
		    var addButton = $('#'+ settings.addButtonID);
		    var deleteButton = $('.'+ settings.deleteButtonClass);
		    
		    // hide the first delete button
			deleteButton.hide();

			// the user wants to add a new field
			addButton.live('click', function(e)
			{
				// clone the first box, append it to the stack and change its ID index to the total length of the stack
				var clone = $('#'+ self.id +' '+ settings.listElement +':last').removeClass('lastChild').clone().appendTo('#'+ self.id).addClass('lastChild');
				
				// check how many blocks are present
				var index = $('#'+ self.id +' '+ settings.listElement).length;
				
				// execute callback
				settings.onAdd(index, clone);
				
				// show the delete button by default
				$('#'+ self.id +' '+ settings.listElement +':last .'+ settings.deleteButtonClass).show();
				
				// @todo make the last cloned inputfield selectable, focus() and select() don't seem to work with clone.find('#settings_'+ (index - 1) for some reason
			});
			
			// the user wants to delete a custom field
			deleteButton.live('click', function(e)
			{
				// remove the field(s) if we have more than 1 box present
				if($('#'+ self.id +' '+ settings.listElement).length != 1) $(this).parents(settings.listElement).remove();
				
				// hide the delete button
				else $(this).hide();
			});
	    });
	};
})(jQuery);
