{include:'{$BACKEND_CORE_PATH}/layout/templates/head.tpl'}
{include:'{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl'}

<div class="pageTitle">
	<h2>{$lblManageModules|ucfirst}: {$lblEdit}</h2>
</div>

{form:edit}
	<div id="tabs" class="tabs">
		<ul>
			<li><a href="#tabModule">{$lblModule|ucfirst}</a></li>
			<li><a href="#tabSettings">{$lblSettings|ucfirst}</a></li>
		</ul>

		<div id="tabModule">
			<div class="subtleBox">
				<div class="options labelWidthLong horizontal">
					<p>
						<label for="name">{$lblName|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
						{$txtName} {$txtNameError}
					</p>
					<p>
						<label for="description">{$lblDescription|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
						{$txtDescription} {$txtDescriptionError}
					</p>
					<ul class="inputList">
						<li>{$chkActive} <label for="active">{$msgHelpActive}</label> {$chkActiveError}</li>
					</ul>
				</div>
			</div>
		</div>

		<div id="tabSettings">
			<div class="subtleBox">
				<div class="options last horizontal">
					<ul id="settings">
						{iteration:settings}
						<li>
							<label for="settings_{$settings.id}">{$lblName|ucfirst} &amp; {$lblValue|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
							<input class="inputText" type="text" name="settings[]" id="settings_{$settings.id}" value="{$settings.name}" /> :
							<input class="inputText" type="text" name="values[]" id="values_{$settings.id}" value="{$settings.value}" />
							<a class="deleteBox" href="#deleteBox">{$lblDeleteSetting|ucfirst}</a>
						</li>
						{/iteration:settings}
					</ul>
					<p>
						<a id="addSettings" href="#addSettings" class="button icon iconAdd" title="{$lblAdd|ucfirst}">
							<span>{$lblAddMoreSettings|ucfirst}</span>
						</a>
					</p>
				</div>
			</div>
		</div>
	</div>

	<div class="fullwidthOptions">
		<div class="buttonHolderRight">
			<input id="editButton" class="button mainButton" type="submit" name="edit" value="{$lblEdit|ucfirst}" />
		</div>
	</div>
{/form:edit}

{include:'{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl'}
{include:'{$BACKEND_CORE_PATH}/layout/templates/footer.tpl'}