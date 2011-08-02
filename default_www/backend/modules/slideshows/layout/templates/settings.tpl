{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>{$lblModuleSettings|ucfirst}: {$lblSlideshows|ucfirst}</h2>
</div>

{form:settings}
	<div class="box">
		<div class="heading">
			<h3>{$lblModules|ucfirst}</h3>
		</div>
		<div class="options labelWidthLong horizontal">
			<ul id="moduleList" class="inputList">
				{iteration:modules}
					<li>
						{$modules.chkModules} <label for="{$modules.id}">{$modules.label}</label>
					</li>
				{/iteration:modules}
			</ul>
		</div>
	</div>

	<div class="fullwidthOptions">
		<div class="buttonHolderRight">
			<input id="save" class="inputButton button mainButton" type="submit" name="save" value="{$lblSave|ucfirst}" />
		</div>
	</div>
{/form:settings}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}