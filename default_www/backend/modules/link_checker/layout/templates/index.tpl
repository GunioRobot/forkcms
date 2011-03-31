{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>{$lblLinkChecker|ucfirst}</h2>
	<div class="buttonHolderRight">
		<a href="#refresh" id="refreshLinks" class="submitButton button inputButton mainButton icon iconRefresh " title="{$lblRefresh|ucfirst}">
			<span>{$lblRefresh|ucfirst}</span>
		</a>
		<a href="{$var|geturl:'settings'}" class="button icon iconSettings" title="{$lblSettings|ucfirst}">
			<span>{$lblSettings|ucfirst}</span>
		</a>
	</div>
</div>

<div id="tabs" class="tabs">
	<ul>
		<li><a href="#tabAll">{$lblAll|ucfirst} ({$numAll})</a></li>
		<li><a href="#tabInternal">{$lblInternal|ucfirst} ({$numInternal})</a></li>
		<li><a href="#tabExternal">{$lblExternal|ucfirst} ({$numExternal})</a></li>
	</ul>

	<div id="tabAll">
		{option:dgAll}
			<form action="{$var|geturl:'mass_links_action'}" method="get" class="forkForms" id="linksAll">
				<div class="datagridHolder">
					<input type="hidden" name="from" value="all" />
					{$dgAll}
				</div>
			</form>
		{/option:dgAll}
		{option:!dgAll}{$msgNoLinks}{/option:!dgAll}
	</div>

	<div id="tabInternal">
		{option:dgInternal}
			<form action="{$var|geturl:'mass_links_action'}" method="get" class="forkForms" id="linksInternal">
				<div class="datagridHolder">
					<input type="hidden" name="from" value="internal" />
					{$dgInternal}
				</div>
			</form>
		{/option:dgInternal}
		{option:!dgInternal}{$msgNoLinks}{/option:!dgInternal}
	</div>

	<div id="tabExternal">
		{option:dgExternal}
			<form action="{$var|geturl:'mass_links_action'}" method="get" class="forkForms" id="linksExternal">
				<div class="datagridHolder">
					<input type="hidden" name="from" value="external" />
					{$dgExternal}
				</div>
			</form>
		{/option:dgExternal}
		{option:!dgExternal}{$msgNoLinks}{/option:!dgExternal}
	</div>
</div>

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}