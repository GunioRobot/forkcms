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
					<p>
						{$msgAll|ucfirst}
					</p>
				</div>
			</div>

			<div id="tabInternal">
				{* All the internal links *}
				<div id="datagridInternal">
					<p>
						{$msgInternal|ucfirst}
					</p>
				</div>
			</div>

			<div id="tabExternal">
				{* All the external links *}
				<div id="datagridExternal">
					<p>
						{$msgExternal|ucfirst}
					</p>
				</div>
			</div>
		</div>
	</div>
	<div class="footer">
		<div class="buttonHolderRight">
			<a href="{$var|geturl:'index':'link_checker'}" class="button"><span>{$msgAllLinks|ucfirst}</span></a>
			<!-- <a href="#refresh" id="refreshLinks" class="submitButton button inputButton mainButton iconLink icon iconRefresh"><span></span></a>  -->
		</div>
	</div>
</div>