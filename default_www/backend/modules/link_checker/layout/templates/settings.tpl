{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>{$lblModuleSettings|ucfirst}: {$lblLinkChecker}</h2>
</div>

{form:settings}
	<div class="box horizontal">
		<div class="heading">
			<h3>{$lblCurl}</h3>
		</div>
		<div class="options">
			<ul class="inputList pb0">
				<li><label for="multiCall">{$chkMultiCall} {$lblMultiCall|ucfirst}</label></li>
			</ul>
			<div id="setConnections">
				<label for="numConnections">{$lblNumConnections|ucfirst}</label>
				{$txtNumConnections} {$txtNumConnectionsError}
			</div>
		</div>
	</div>

	<div class="box horizontal">
		<div class="heading">
			<h3>{$lblCaching|ucfirst}</h3>
		</div>
		<div class="options">
			<ul class="inputList pb0">
				<li><label for="cacheLinks">{$chkCacheLinks} {$lblCacheLinks|ucfirst}</label></li>
			</ul>
			<div id="setCache">
				<label for="cacheTime">{$lblCacheTime|ucfirst}</label>
				{$txtCacheTime} {$txtCacheTimeError}
				<span class="helpTxt">{$msgHelpCacheTime}</span>

				<br />
				<div class="buttonHolder">
					<a href="#clear" class="submitButton button inputButton" id="clearLinkCache">
						<span>{$msgClearLinkCache|ucfirst}</span>
					</a>
					<span class="helpTxt">{$numCache} items in cache</span>
				</div>
			</div>
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