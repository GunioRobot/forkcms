<?php

require_once 'external/redbean.php';

class Add extends BackendBaseActionAdd
{
	private $config;


	public function __construct()
	{
		// call parent, this will probably add some general CSS/JS or other required files
		parent::execute();

		$this->loadConfig();
		$this->loadForm();
		$this->validateForm();
		$this->parse();
	}


	private function loadConfig()
	{
		$this->config = array(
			'title' => array(
				'type' => 'text',
				'is_required' => true,
			),
			'text' => array(
				'type' => 'textarea',
			)
		);
	}


	public function loadForm()
	{
		$this->frm = new BackendForm('add');

		// set hidden values
		$rbtHiddenValues[] = array('label' => BL::lbl('Hidden', $this->URL->getModule()), 'value' => 'Y');
		$rbtHiddenValues[] = array('label' => BL::lbl('Published'), 'value' => 'N');

		// get categories
		$categories = BackendBlogModel::getCategories();
		$categories['new_category'] = ucfirst(BL::getLabel('AddCategory'));

		// create elements
		$this->frm->addText('title', null, null, 'inputText title', 'inputTextError title');
		$this->frm->addEditor('text');
		$this->frm->addEditor('introduction');
		$this->frm->addRadiobutton('hidden', $rbtHiddenValues, 'N');
		$this->frm->addCheckbox('allow_comments', BackendModel::getModuleSetting($this->getModule(), 'allow_comments', false));
		$this->frm->addDropdown('category_id', $categories, SpoonFilter::getGetValue('category', null, null, 'int'));
		if(count($categories) != 2) $this->frm->getField('category_id')->setDefaultElement('');
		$this->frm->addDropdown('user_id', BackendUsersModel::getUsers(), BackendAuthentication::getUser()->getUserId());
		$this->frm->addText('tags', null, null, 'inputText tagBox', 'inputTextError tagBox');
		$this->frm->addDate('publish_on_date');
		$this->frm->addTime('publish_on_time');

		// meta
		$this->meta = new BackendMeta($this->frm, null, 'title', true);
	}
}