<?php

/**
 * PagesEdit
 *
 * This is the edit-action, it will display a form to create a new pages item
 *
 * @package		backend
 * @subpackage	pages
 *
 * @author 		Tijs Verkoyen <tijs@netlash.com>
 * @since		2.0
 */
class PagesEdit extends BackendBaseActionEdit
{
	/**
	 * The blocks
	 *
	 * @var	array
	 */
	private $blocks = array(),
			$blocksContent = array();


	/**
	 * The template data
	 *
	 * @var	array
	 */
	private $templates = array();


	/**
	 * Execute the action
	 *
	 * @return	void
	 */
	public function execute()
	{
		// call parent, this will probably edit some general CSS/JS or other required files
		parent::execute();

		// load record
		$this->loadRecord();

		// get data
		$this->templates = BackendPagesModel::getTemplates();

		// get maximum number of blocks	@todo	update this setting when editing/updating templates
		$maximumNumberOfBlocks = BackendModel::getModuleSetting('core', 'template_max_blocks', 5);

		// build blocks array
		for($i = 0; $i < $maximumNumberOfBlocks; $i++) $this->blocks[$i] = array('index' => $i, 'name' => 'name '. $i,);

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
		// get default template id
		$defaultTemplateId = BackendModel::getModuleSetting('core', 'default_template', 1);

		// init var
		$templatesForDropdown = array();

		// build values
		foreach($this->templates as $templateId => $row)
		{
			// set value
			$templatesForDropdown[$templateId] = $row['label'];

			// set checked
			if($templateId == $this->record['template_id']) $this->templates[$templateId]['checked'] = true;
		}

		// create form
		$this->frm = new BackendForm('edit');

		// assign in template
		$this->tpl->assign('defaultTemplateId', $defaultTemplateId);

		// create elements
		$this->frm->addTextField('title', $this->record['title']);
		$this->frm->addDropDown('template_id', $templatesForDropdown, $this->record['template_id']);
		$this->frm->addRadioButton('hidden', array(array('label' => BL::getLabel('Hidden'), 'value' => 'Y'), array('label' => BL::getLabel('Published'), 'value' => 'N')), $this->record['hidden']);

		// get maximum number of blocks	@todo	update this setting when editing/updating templates
		$maximumNumberOfBlocks = BackendModel::getModuleSetting('core', 'template_max_blocks', 5);

		// build blocks array
		for($i = 0; $i < $maximumNumberOfBlocks; $i++)
		{
			// init var
			$selectedExtra = null;
			$html = null;

			// reset data, if it is available
			if(isset($this->blocksContent[$i]))
			{
				$selectedExtra = $this->blocksContent[$i]['extra_id'];
				$html = $this->blocksContent[$i]['HTML'];
			}

			// create elements
			$this->blocks[$i]['formElements']['ddmExtraId'] = $this->frm->addDropDown('block_extra_id_'. $i, BackendPagesModel::getExtras(), $selectedExtra);
			$this->blocks[$i]['formElements']['txtHTML'] = $this->frm->addEditorField('block_html_'. $i, $html);
		}

		// page info
		$this->frm->addCheckBox('navigation_title_overwrite', ($this->record['navigation_title_overwrite'] == 'Y'));
		$this->frm->addTextField('navigation_title', $this->record['navigation_title']);

		// meta
		$this->meta = new BackendMeta($this->frm, $this->record['meta_id'], 'title', true);

		// edit button
		$this->frm->addButton('submit', ucfirst(BL::getLabel('Edit')), 'submit');
	}


	/**
	 * Load the record
	 *
	 * @return	void
	 */
	private function loadRecord()
	{
		// get record
		$this->id = $this->getParameter('id', 'int');

		// validate id
		if($this->id == 0 || !BackendPagesModel::exists($this->id)) $this->redirect(BackendModel::createURLForAction('index') .'?error=non-existing');

		// get the record
		$this->record = BackendPagesModel::get($this->id);

		// load blocks
		$this->blocksContent = BackendPagesModel::getBlocks($this->id);
	}


	/**
	 * Parse
	 *
	 * @return	void
	 */
	protected function parse()
	{
		// parse some variables
		$this->tpl->assign('templateIconsURL', BACKEND_CORE_URL .'/layout/images/template_icons');
		$this->tpl->assign('templates', $this->templates);
		$this->tpl->assign('blocks', $this->blocks);

		// parse the form
		$this->frm->parse($this->tpl);
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
			// set callback for generating an unique url
			$this->meta->setUrlCallback('BackendPagesModel', 'getUrl', array($this->record['id'], $this->record['parent_id']));

			// cleanup the submitted fields, ignore fields that were edited by hackers
			$this->frm->cleanupFields();

			// validate fields
			$this->frm->getField('title')->isFilled(BL::getError('TitleIsRequired'));

			// validate meta
			$this->meta->validate();

			// no errors?
			if($this->frm->isCorrect())
			{
				// build page record
				$page = array();
				$page['id'] = $this->record['id'];
				$page['user_id'] = BackendAuthentication::getUser()->getUserId();
				$page['parent_id'] = $this->record['parent_id'];
				$page['template_id'] = (int) $this->frm->getField('template_id')->getValue();
				$page['meta_id'] = (int) $this->meta->save();
				$page['language'] = BackendLanguage::getWorkingLanguage();
				$page['type'] = 'page';
				$page['title'] = $this->frm->getField('title')->getValue();
				$page['navigation_title'] = $this->frm->getField('navigation_title')->getValue();
				$page['navigation_title_overwrite'] = ($this->frm->getField('navigation_title_overwrite')->isChecked()) ? 'Y' : 'N';
				$page['hidden'] = $this->frm->getField('hidden')->getValue();
				$page['status'] = 'active';
				$page['publish_on'] = date('Y-m-d H:i:s'); // @todo
				$page['created_on'] = date('Y-m-d H:i:s', $this->record['created_on']);
				$page['edited_on'] = date('Y-m-d H:i:s');
				$page['allow_move'] = $this->record['allow_move'];
				$page['allow_children'] = $this->record['allow_children'];
				$page['allow_content'] = $this->record['allow_content'];
				$page['allow_edit'] = $this->record['allow_edit'];
				$page['allow_delete'] = $this->record['allow_delete'];
				$page['sequence'] = $this->record['sequence'];

				// insert page, store the id, we need it when building the blocks
				$revisionId = BackendPagesModel::update($page);

				// build blocks
				$blocks = array();

				// loop blocks in template
				for($i = 0; $i < $this->templates[$page['template_id']]['number_of_blocks']; $i++)
				{
					// get the extra id
					$extraId = $this->frm->getField('block_extra_id_'. $i)->getValue();

					// init var
					$html = null;

					// extra-type is HTML
					if($extraId == 'html')
					{
						// reset vars
						$extraId = null;
						$html = $this->frm->getField('block_html_'. $i)->getValue();
					}

					// build block
					$block = array();
					$block['id'] = $this->blocksContent[$i]['id'];
					$block['revision_id'] = $revisionId;
					$block['extra_id'] = $extraId;
					$block['HTML'] = $html;
					$block['status'] = 'active';
					$block['created_on'] = date('Y-m-d H:i:s');
					$block['edited_on'] = date('Y-m-d H:i:s');

					// edit block
					$blocks[] = $block;
				}

				// insert the blocks
				BackendPagesModel::updateBlocks($blocks);

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('index') .'?report=edited&var='. urlencode($page['title']) .'&hilight=id-'. $page['id']);
			}
		}
	}
}

?>