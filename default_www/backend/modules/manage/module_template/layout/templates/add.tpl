[include:'[$BACKEND_CORE_PATH]/layout/templates/head.tpl']
[include:'[$BACKEND_CORE_PATH]/layout/templates/structure_start_module.tpl']

<div class="pageTitle">
	<h2>[$lbl{$module.name|camelcase}|ucfirst]: [$lblAdd]</h2>
</div>

[form:add]
	<div class="tabs">
		<ul>
			<li><a href="#tabContent">[$lblContent|ucfirst]</a></li>
		</ul>

		<div id="tabContent">
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td id="leftColumn">
						<div class="box">
							<div class="heading">
								<h3>[$lblGeneral|ucfirst]</h3>
							</div>
							<div class="options">
								<div class="horizontal">
{iteration:fieldsLeft}
{option:fieldsLeft.textfield}
									<p>
										<label for="{$fieldsLeft.name}">{$fieldsLeft.label}{option:fieldsLeft.mandatory}<abbr title="{$lblRequiredField}">*</abbr>{/option:fieldsLeft.mandatory}</label>
										{$fieldsLeft.var_full} {$fieldsLeft.error_var}

									</p>
{/option:fieldsLeft.textfield}
{option:fieldsLeft.radiobutton}
									<ul class="inputList">
										[iteration:{$fieldsLeft.name}]
										<li>
											[${$fieldsLeft.name}.{$fieldsLeft.var_name}]

											<label for="[${$fieldsLeft.name}.id]">[${$fieldsLeft.name}.value]</label>
										</li>
										[/iteration:{$fieldsLeft.name}]
									</ul>
{/option:fieldsLeft.radiobutton}
{option:fieldsLeft.checkbox}
									<p>
										{$fieldsLeft.var_full} <label for="{$fields.name}">[$lbl{$fieldsLeft.name|camelcase}|ucfirst]</label>
									</p>
{/option:fieldsLeft.checkbox}
{/iteration:fieldsLeft}

								</div>
							</div>
						</div>
					</td>
					<td id="sidebar">
						<div class="box">
							<div class="heading">
								<h3>[$lblMeta|ucfirst]</h3>
							</div>
							<div class="options">
								<div class="horizontal">
{iteration:fieldsRight}
{option:fieldsRight.textfield}
									<p>
										<label for="{$fieldsRight.name}">{$fieldsRight.label}{option:fieldsRight.mandatory}<abbr title="{$lblRequiredField}">*</abbr>{/option:fieldsRight.mandatory}</label>
										{$fieldsRight.var_full} {$fieldsRight.error_var}

									</p>
{/option:fieldsRight.textfield}
{option:fieldsRight.radiobutton}
									<ul class="inputList">
										[iteration:{$fieldsRight.name}]
										<li>
											[${$fieldsRight.name}.{$fieldsRight.var_name}]

											<label for="[${$fields.name}.id]">[${$fieldsRight.name}.value]</label>
										</li>
										[/iteration:{$fieldsRight.name}]
									</ul>
{/option:fieldsRight.radiobutton}
{option:fieldsRight.checkbox}
									<ul class="inputList">
										<li>{$fieldsRight.var_full} <label for="{$fieldsRight.name}">[$lbl{$fieldsRight.name|camelcase}|ucfirst]</label></li>
									</ul>
{/option:fieldsRight.checkbox}
{/iteration:fieldsRight}
								</div>
							</div>
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<div class="fullwidthOptions">
		<div class="buttonHolderRight">
			<input id="addButton" class="inputButton button mainButton" type="submit" name="add" value="[$lblAdd|ucfirst]" />
		</div>
	</div>
[/form:add]

[include:'[$BACKEND_CORE_PATH]/layout/templates/structure_end_module.tpl']
[include:'[$BACKEND_CORE_PATH]/layout/templates/footer.tpl']