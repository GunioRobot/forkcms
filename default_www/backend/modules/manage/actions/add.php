<?php

/**
 * BackendManageAdd
 * This is the index-action (default), it will display a form to add a module
 *
 * @package		backend
 * @subpackage	manage
 *
 * @author 		Dave Lens <dave@netlash.com>
 * @since		2.0
 */
class BackendManageAdd extends BackendBaseActionAdd
{
	/**
	 * Execute the action
	 *
	 * @return	void
	 */
	public function execute()
	{
		// call parent, this will probably add some general CSS/JS or other required files
		parent::execute();

		// load the form
		$this->loadForm();

		// validate the form
		$this->validateForm();

		// parse the datagrid
		$this->parse();

		// display the page
		$this->display();
	}


	/**
	 * Load the form
	 *
	 * @return	void
	 */
	private function loadForm()
	{
		// create form
		$this->frm = new BackendForm('add');

		// create elements
		$this->frm->addText('name', null, 255);
		$this->frm->addTextarea('description', null, 255);
		$this->frm->addCheckbox('active', true);
	}


	/**
	 * Validate the form
	 *
	 * @return	void
	 */
	private function validateForm()
	{
		// is the form submitted?
		if($this->frm->isSubmitted())
		{
			// required fields
			$this->frm->getField('name')->isFilled(BL::getError('NameIsRequired'));

			// build settings-array
			$settings = isset($_POST['settings']) ? $_POST['settings'] : array();

			// build locations-array
			$locations = isset($_POST['locations']) ? $_POST['locations'] : array();

			// build datafields-arrays
			$types = isset($_POST['types']) ? $_POST['types'] : array();
			$names = isset($_POST['names']) ? $_POST['names'] : array();

			// no errors?
			if($this->frm->isCorrect())
			{
				// build module-array
				$module['name'] = str_replace('-', '_', SpoonFilter::urlise($this->frm->getField('name')->getValue()));
				$module['description'] = $this->frm->getField('description')->getValue(true);
				$module['active'] = $this->frm->getField('active')->getChecked() ? 'Y' : 'N';

				// loop the added settings
				foreach($settings as $key => $value)
				{
					// unset the initial key
					unset($settings[$key]);

					// do not add if the key is empty
					if($value == '') continue;

					// store the component data
					$settings[$value] = empty($_POST['values'][$key]) ? false : $_POST['values'][$key];
				}

				// reserve a new fields stack
				$fields = array();
				$fields['left'] = array();
				$fields['right'] = array();

				// loop the names
				foreach($names as $key => $value)
				{
					// set the location
					$location = empty($locations[$key]) ? false : $locations[$key];

					// set the type
					$fields[$location][$key]['type'] = empty($types[$key]) ? false : $types[$key];

					// hungarian notation prefix
					$prefix = BackendManageHelper::getHungarianNotationByInputType($fields[$location][$key]['type']);

					// map the name to the appropriate type in the fields array
					$fields[$location][$key]['name'] = $value;
					$fields[$location][$key]['label'] = '{$lbl'. SpoonFilter::toCamelCase($value) .'|ucfirst}';
					$fields[$location][$key]['sql_type'] = BackendManageHelper::getSQLTypeByInputType($types[$key]);
					$fields[$location][$key]['method'] = BackendManageHelper::getMethodByInputType($types[$key]);
					$fields[$location][$key]['var_name'] = $prefix . SpoonFilter::toCamelCase($value);
					$fields[$location][$key]['var_full'] = '{$'. $fields[$location][$key]['var_name'] .'}';
					$fields[$location][$key]['error_var'] = '{$'. $prefix . SpoonFilter::toCamelCase($value) .'Error}';
					$fields[$location][$key]['mandatory'] = true;

					// check what input type we have
					switch($fields[$location][$key]['type'])
					{
						case 'checkbox':
							$fields[$location][$key]['checkbox'] = true;

							// set hidden values
							$fields[$location][$key]['values'][] = array('label' => BL::getLabel('Yes'), 'value' => 'Y');
							$fields[$location][$key]['values'][] = array('label' => BL::getLabel('No'), 'value' => 'N');
						break;

						case 'radiobutton':
							$fields[$location][$key]['radiobutton'] = true;

							// set hidden values
							$fields[$location][$key]['values'][] = array('label' => BL::getLabel('Yes'), 'value' => 'Y');
							$fields[$location][$key]['values'][] = array('label' => BL::getLabel('No'), 'value' => 'N');
						break;

						default:
							$fields[$location][$key]['textfield'] = true;
					}
				}

				// save changes
				BackendManageModel::insertModule($module, $settings, $fields);

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('index') .'&report=added&var='. $module['name'] .'&highlight=module-'. $module['name']);
			}
		}
	}
}

?>