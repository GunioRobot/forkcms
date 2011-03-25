<div class="box" id="widgetLinkCheckerClassic">
	<div class="heading">
		<h3>
			{$lblLinkChecker|ucfirst}
		</h3>
	</div>

	<div class="options">
		<div id="tabs" class="tabs">
			<ul>
				<li><a href="#tabAll">{$lblAll|ucfirst} ({$numAll})</a></li>
				<li><a href="#tabInternal">{$lblInternal|ucfirst} ({$numInternal})</a></li>
				<li><a href="#tabExternal">{$lblExternal|ucfirst} ({$numExternal})</a></li>
			</ul>

			<div id="tabAll">
				{* All the links *}
				<div id="datagridAll">
					{option:dgAll}
					<div class="datagridHolder">
						{$dgAll}
					</div>
					{/option:dgAll}
					{option:!dgAll}
					<p>
						{$msgNoLinks|ucfirst}
					</p>
					{/option:!dgAll}
				</div>
			</div>

			<div id="tabInternal">
				{* All the internal links *}
				<div id="datagridInternal">
					{option:dgInternal}
					<div class="datagridHolder" >
						{$dgInternal}
					</div>
					{/option:dgInternal}
					{option:!dgInternal}
					<p>
						{$msgNoLinks|ucfirst}
					</p>
					{/option:!dgInternal}
				</div>
			</div>

			<div id="tabExternal">
				{* All the external links *}
				<div id="datagridExternal">
					{option:dgExternal}
					<div class="datagridHolder">
						{$dgExternal}
					</div>
					{/option:dgExternal}

					{option:!dgExternal}
					<p>
						{$msgNoLinks|ucfirst}
					</p>
					{/option:!dgExternal}
				</div>
			</div>
		</div>
	</div>
	<div class="footer">
		<div class="buttonHolderRight">
			<a href="{$var|geturl:'index':'link_checker'}" class="button"><span>{$msgAllLinks|ucfirst}</span></a>
			<a href="#refresh" id="refreshLinks" class="submitButton button inputButton mainButton iconLink icon iconRefresh"><span></span></a>
		</div>
	</div>
</div>