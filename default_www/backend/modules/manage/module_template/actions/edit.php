/**
 * Backend{$module.name|camelcase}Edit
 * This is the index-action (default), it will display a form to edit a {$module.name} record
 *
 * @package		backend
 * @subpackage	{$module.name}

 *
 * @author		{$user.name} <{$user.email}>
 * @since		{$module.fork_version}

 */
class Backend{$module.name|camelcase}Edit extends BackendBaseActionEdit
{
	/**
	 * Execute the action
	 *
	 * @return	void
	 */
	public function execute()
	{
		// get parameters
		$this->id = $this->getParameter('id', 'int');

		// does the item exists
		if($this->id !== null && Backend{$module.name|camelcase}Model::exists($this->id))
		{
			// call parent, this will probably add some general CSS/JS or other required files
			parent::execute();

			// get all data for the item we want to edit
			$this->getData();

			// load the form
			$this->loadForm();

			// validate the form
			$this->validateForm();

			// parse the datagrid
			$this->parse();

			// display the page
			$this->display();
		}

		// no item found, throw an exceptions, because somebody is fucking with our URL
		else $this->redirect(BackendModel::createURLForAction('index') .'&error=non-existing');
	}


	/**
	 * Get the data
	 *
	 * @return	void
	 */
	private function getData()
	{
		$this->record = Backend{$module.name|camelcase}Model::get($this->id);
	}


	/**
	 * Load the form
	 *
	 * @return	void
	 */
	private function loadForm()
	{
		// create form
		$this->frm = new BackendForm('edit');

		// create and add elements
{iteration:fieldsLeft}
{option:fieldsLeft.radiobutton}
{iteration:fieldsLeft.values}
		${$fieldsLeft.var_name}Values[] = array('label' => BL::getLabel('{$values.label}'), 'value' => '{$values.value}');
{/iteration:fieldsLeft.values}
{/option:fieldsLeft.radiobutton}
		$this->frm->{$fieldsLeft.method}('{$fieldsLeft.name}'{option:fieldsLeft.radiobutton}, ${$fieldsLeft.var_name}Values{/option:fieldsLeft.radiobutton}, $this->record['{$fieldsLeft.name}']);
{/iteration:fieldsLeft}
{iteration:fieldsRight}
{option:fieldsRight.radiobutton}
{iteration:fieldsRight.values}
		${$fieldsRight.var_name}Values[] = array('label' => BL::getLabel('{$values.label}'), 'value' => '{$values.value}');
{/iteration:fieldsRight.values}
{/option:fieldsRight.radiobutton}
		$this->frm->{$fieldsRight.method}('{$fieldsRight.name}'{option:fieldsRight.radiobutton}, ${$fieldsRight.var_name}Values{/option:fieldsRight.radiobutton}, $this->record['{$fieldsRight.name}']);
{/iteration:fieldsRight}
	}


	/**
	 * Parse the form
	 *
	 * @return	void
	 */
	protected function parse()
	{
		// call parent
		parent::parse();

		// assign id and record
		$this->tpl->assign('id', $this->record['id']);
		$this->tpl->assign('record', $this->record);
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
			// cleanup the submitted fields, ignore fields that were added by hackers
			$this->frm->cleanupFields();

			// validation
{iteration:fieldsLeft}
			{option:fieldsLeft.mandatory}$this->frm->getField('{$fieldsLeft.name}')->isFilled(BL::getError('FieldIsRequired'));
{/option:fieldsLeft.mandatory}
{/iteration:fieldsLeft}
{iteration:fieldsRight}
			{option:fieldsRight.mandatory}$this->frm->getField('{$fieldsRight.name}')->isFilled(BL::getError('FieldIsRequired'));
{/option:fieldsRight.mandatory}
{/iteration:fieldsRight}

			// no errors?
			if($this->frm->isCorrect())
			{
				// build item
				$record = array();
				$record['id'] = $this->id;
{iteration:fieldsLeft}
				$record['{$fieldsLeft.name}'] = $this->frm->getField('{$fieldsLeft.name}')->getValue();
{/iteration:fieldsLeft}
{iteration:fieldsRight}
				$record['{$fieldsRight.name}'] = $this->frm->getField('{$fieldsRight.name}')->getValue();
{/iteration:fieldsRight}
				$record['edited_on'] = BackendModel::getUTCDate();

				// update item
				Backend{$module.name|camelcase}Model::update($record);

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('index') .'&report=edited&var='. urlencode($record['name']));
			}
		}
	}
}