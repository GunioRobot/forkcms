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
				
				jsBackend.slideshows.loadDataSetMethods($(this).val(), $('#methods'));
			});
		}
		
		// load the settings
		if(jsBackend.slideshows.settings)
		{
			jsBackend.slideshows.settings.init();
		}
	},
	
	
	buildDatagrid: function(module)
	{
		$.ajax(
		{
			url: '/backend/ajax.php?module='+ jsBackend.current.module +'&action=get_dataset_methods&language={$LANGUAGE}',
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
					// clear the datagrid bodyo first
					var dataGridBody = $('#datagrid-'+ module).find('tbody').html('');
					
					for(var key in json.data)
					{
						var item = json.data[key];
						var rowHTML = '<tr>'+
							'<td class="method">'+ item.method +'</td>'+
							'<td class="label" data-id="{id:\''+ item.id +'\', m:\''+ module +'\', method:\''+ item.method +'\'}">'+ 
								item.label +
							'</td>'+
						'</tr>';
						
						// add the HTML for the new row to the datagrid
						dataGridBody.append(rowHTML);
					}

					// reload the inline edits for all datagrids
					jsBackend.slideshows.reloadDataGridInlineEdits(module);
					
					// toggle the datagrid
					jsBackend.slideshows.toggleDataGrid(module);
				}
			}
		});
	},
	
	
	loadDataSetMethods: function(module, select)
	{
		$.ajax(
		{
			url: '/backend/ajax.php?module='+ jsBackend.current.module +'&action=get_dataset_methods_as_pairs&language={$LANGUAGE}',
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
					// clear the select HTML first
					var dataGridBody = select.html('');

					for(var key in json.data)
					{
						var label = json.data[key];
						var optionHTML = '<option value="'+ key +'">'+
								label +
						'</option>';
						
						// add the HTML for the new row to the datagrid
						select.append(optionHTML);
					}
				}
			}
		});
	},
	
	
	loadMethods: function(module, select)
	{
		$.ajax(
		{
			url: '/backend/ajax.php?module='+ jsBackend.current.module +'&action=get_supported_methods&language={$LANGUAGE}',
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
					select.html(json.data);
				}
			}
		});
	},
	
	
	reloadDataGridInlineEdits: function(module)
	{
		var label = $('#datagrid-'+ module +' td.label');
		
		if(label.length > 0)
		{
			// buil ajax-url
			var url = '/backend/ajax.php?module='+ jsBackend.current.module +'&action=update_dataset_method&language='+ jsBackend.current.language;

			// bind
			label.inlineTextEdit( { saveUrl: url, tooltip: '{$msgClickToEdit}' });
		}
	},
	
	
	saveMethod: function(module, method, label)
	{
		$.ajax(
		{
			url: '/backend/ajax.php?module='+ jsBackend.current.module +'&action=insert_dataset_method&language={$LANGUAGE}',
			data: 'm=' + module + '&method=' + method + '&label=' + label,
			success: function(json, textStatus)
			{
				if(json.code != 200)
				{
					// show error if needed
					if(jsBackend.debug) alert(textStatus);
				}
				else
				{
					// rebuild the datagrid for this module
					jsBackend.slideshows.buildDatagrid(module);
					
					// show a message stating the label was mapped
					jsBackend.messages.add('success', 'The selected method is now mapped to the label "'+ label +'"');
				}
			}
		});
	},
	
	
	toggleDataGrid: function(module)
	{
		// check if we should show/hide the datagrid
		var dataGrid = $('#datagrid-'+ module);
		var rows = dataGrid.find('tbody tr');

		if(rows.length > 0)
		{
			dataGrid.show();
		}
		else
		{
			dataGrid.hide();
		}
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