<div class="box" id="widgetLinkCheckerClassic">
	<div class="heading">
		<h3>
			<a href="{$var|geturl:'index':'link_checker'}">{$lblLinkChecker|ucfirst}: {$lblDeadLinks|ucfirst}</a>
		</h3>
	</div>

	{option:numDeadLinksFound}
	<div class="moderate">
		<div class="oneLiner">
			<p>{$msgDeadLinksToModerate|sprintf:{$numDeadLinksFound}}</p>
			<div class="buttonHolderRight">
				<a href="{$var|geturl:'index':'link_checker'}" class="button"><span>{$msgAllLinks|ucfirst}</span></a>
			</div>
		</div>
	</div>
	{/option:numDeadLinksFound}

	{option:dgAll}
	<div id="datagridAll">
		<div class="options content">
			<div class="datagridHolder">
				{$dgAll}
			</div>
		</div>
	</div>
	{/option:dgAll}

	{option:!dgAll}
	<div class="options content">
		<p>{$msgNoLinks|ucfirst}</p>
	</div>
	{/option:!dgAll}
</div>
