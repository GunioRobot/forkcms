/**
 * Backend{$module.name|camelcase}Add
 * This is the index-action (default), it will display a form to add a {$module.name} record
 *
 * @package		backend
 * @subpackage	{$module.name}

 *
 * @author		{$user.name} <{$user,email}>
 * @since		{$module,fork_version}

 */
class Backend{$module.name|camelcase}Add extends BackendBaseActionAdd
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

		// create formfields
{iteration:fieldsLeft}
{option:fieldsLeft.radiobutton}
{iteration:fieldsLeft.values}
		${$fieldsLeft.var_name}Values[] = array('label' => BL::getLabel('{$values.label}'), 'value' => '{$values.value}');
{/iteration:fieldsLeft.values}
{/option:fieldsLeft.radiobutton}
		$this->frm->{$fieldsLeft.method}('{$fieldsLeft.name}'{option:fieldsLeft.radiobutton}, ${$fieldsLeft.var_name}Values{/option:fieldsLeft.radiobutton});
{/iteration:fieldsLeft}
{iteration:fieldsRight}
{option:fieldsRight.radiobutton}
{iteration:fieldsRight.values}
		${$fieldsRight.var_name}Values[] = array('label' => BL::getLabel('{$values.label}'), 'value' => '{$values.value}');
{/iteration:fieldsRight.values}
{/option:fieldsRight.radiobutton}
		$this->frm->{$fieldsRight.method}('{$fieldsRight.name}'{option:fieldsRight.radiobutton}, ${$fieldsRight.var_name}Values{/option:fieldsRight.radiobutton});
{/iteration:fieldsRight}
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

			/* @todo name field should have an additional check
			// name checks
			if($txtName->isFilled(BL::getError('FieldIsRequired')))
			{
				// this name already exists in this language
				if(Backend{$module.name|camelcase}Model::existsByName($txtName->getValue()))
				{
					$txtName->setError(BL::getError('AlreadyExists'));
				}
			}
			*/

			// no errors?
			if($this->frm->isCorrect())
			{
				// build record
				$record = array();
				$record['language'] = BL::getWorkingLanguage();
{iteration:fieldsLeft}
				$record['{$fieldsLeft.name}'] = $this->frm->getField('{$fieldsLeft.name}')->getValue();
{/iteration:fieldsLeft}
{iteration:fieldsRight}
				$record['{$fieldsRight.name}'] = $this->frm->getField('{$fieldsRight.name}')->getValue();
{/iteration:fieldsRight}
				$record['created_on'] = BackendModel::getUTCDate();
				$record['edited_on'] = BackendModel::getUTCDate();

				// update item
				Backend{$module.name|camelcase}Model::insert($record);

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('index') .'&report=added&var='. urlencode($record['name']));
			}
		}
	}
}