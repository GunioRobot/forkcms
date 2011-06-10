{include:'{$BACKEND_CORE_PATH}/layout/templates/head.tpl'}
{include:'{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl'}

<div class="pageTitle">
	<h2>{$lblManageModules|ucfirst}: {$lblAdd}</h2>
</div>

{form:add}
	<div id="tabs" class="tabs">
		<ul>
			<li><a href="#tabModule">{$lblModule|ucfirst}</a></li>
			<li><a href="#tabSettings">{$lblSettings|ucfirst}</a></li>
			<li><a href="#tabDataFields">{$lblDataFields|ucfirst}</a></li>
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
						<li>
							<label for="settings_1">{$lblName|ucfirst} &amp; {$lblValue|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
							<input class="inputText" type="text" maxlength="255" name="settings[]" id="settings_1" value="" /> :
							<input class="inputText" type="text" maxlength="255" name="values[]" id="values_1" value="" />
							<a class="deleteBox" href="#deleteBox">{$lblDeleteSetting|ucfirst}</a>
						</li>
					</ul>
					<p>
						<a id="addSettings" href="#settings" class="button icon iconAdd addBox" title="{$lblAdd|ucfirst}">
							<span>{$lblAddMoreSettings|ucfirst}</span>
						</a>
					</p>
				</div>
			</div>
		</div>
		
		<div id="tabDataFields">
			<div class="subtleBox">
				<div class="options labelWidthLong horizontal">
					<ul id="dataFields">
						<li>
							<input class="inputText" type="text" maxlength="255" name="names[]" id="names_1" value="" />
							<select class="inputDropdown" id="types_1" name="types[]">
								<option value="0">-{$lblSelectType|ucfirst}-</option>
								<option value="checkbox">{$lblFormInputCheckbox|ucfirst}</option>
								<option value="radiobutton">{$lblFormInputRadiobutton|ucfirst}</option>
								<option value="textfield">{$lblFormInputTextfield|ucfirst}</option>
								<option value="textarea">{$lblFormInputTextarea|ucfirst}</option>
							</select>
							<select class="inputDropdown" id="locations_1" name="locations[]">
								<option value="0">-{$lblSelectLocation|ucfirst}-</option>
								<option value="left">{$lblLeft|ucfirst}</option>
								<option value="right">{$lblRight|ucfirst}</option>
							</select>
							<a class="deleteBox" href="#deleteBox">{$lblDeleteField|ucfirst}</a>
						</li>
					</ul>
					<p>
						<a id="addDataFields" href="#dataFields" class="button icon iconAdd addBox" title="{$lblAdd|ucfirst}">
							<span>{$lblAddMoreFields|ucfirst}</span>
						</a>
					</p>
				</div>
			</div>
		</div>
	</div>

	<div class="fullwidthOptions">
		<div class="buttonHolderRight">
			<input id="addButton" class="button mainButton" type="submit" name="add" value="{$lblAdd|ucfirst}" />
		</div>
	</div>
{/form:add}

{include:'{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl'}
{include:'{$BACKEND_CORE_PATH}/layout/templates/footer.tpl'}