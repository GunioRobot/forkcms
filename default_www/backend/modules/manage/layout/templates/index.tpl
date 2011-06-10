{include:'{$BACKEND_CORE_PATH}/layout/templates/head.tpl'}
{include:'{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl'}

<div class="pageTitle">
	<h2>{$lblManageModules|ucfirst}</h2>
	<div class="buttonHolderRight">
		<a class="button icon iconAdd" href="{$var|geturl:'add'}"><span>{$lblAdd|ucfirst}</span></a>
		<a class="button icon iconRefresh" href="{$var|geturl:'reload_locale'}"><span>{$lblReloadLocale|ucfirst}</span></a>
	</div>
</div>
{option:datagrid}
<div class="datagridHolder">
	<form action="{$var|geturl:'mass_action'}" method="get" class="forkForms submitWithLink" id="massManageAction">
	{$datagrid}
	</form>
</div>
{/option:datagrid}

{option:!datagrid}<p>{$msgNoItems|sprintf:{$var|geturl:'add'}}</p>{/option:!datagrid}

{include:'{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl'}
{include:'{$BACKEND_CORE_PATH}/layout/templates/footer.tpl'}