<div class="box" id="widgetCrawlerClassic">
	<div class="heading">
		<h3>
			{$lblCrawler|ucfirst}
		</h3>
	</div>

	<div class="options">
		<div id="tabs" class="tabs">
			<ul>
				<li><a href="#tabCrawlerAll">{$lblAll|ucfirst}</a></li>
				<li><a href="#tabCrawlerInternal">{$lblInternal|ucfirst}</a></li>
				<li><a href="#tabCrawlerExternal">{$lblExternal|ucfirst}</a></li>
			</ul>

			<div id="tabCrawlerAll">
				{* All the links *}
				<div id="datagridAll">
					{option:dgCrawlerAll}
					<div class="datagridHolder">
						{$dgCrawlerAll}
					</div>
					{/option:dgCrawlerAll}
					{option:!dgCrawlerAll}
					<p>
						{$msgNoLinks|ucfirst}
					</p>
					{/option:!dgCrawlerAll}
				</div>
			</div>

			<div id="tabCrawlerInternal">
				{* All the internal links *}
				<div id="datagridInternal">
					{option:dgCrawlerInternal}
					<div class="datagridHolder" >
						{$dgCrawlerInternal}
					</div>
					{/option:dgCrawlerInternal}
					{option:!dgCrawlerInternal}
					<p>
						{$msgNoLinks|ucfirst}
					</p>
					{/option:!dgCrawlerInternal}
				</div>
			</div>

			<div id="tabCrawlerExternal">
				{* All the external links *}
				<div id="datagridExternal">
					{option:dgCrawlerExternal}
					<div class="datagridHolder">
						{$dgCrawlerExternal}
					</div>
					{/option:dgCrawlerExternal}

					{option:!dgCrawlerExternal}
					<p>
						{$msgNoLinks|ucfirst}
					</p>
					{/option:!dgCrawlerExternal}
				</div>
			</div>
		</div>
	</div>
	<div class="footer">
		<div class="buttonHolderRight">
			<a href="{$var|geturl:'index':'link_checker'}" class="button"><span>{$msgAllLinks|ucfirst}</span></a>
<<<<<<< HEAD
			<a href="#refresh" id="refreshLinks" class="submitButton button inputButton mainButton iconLink icon iconRefresh"><span></span></a>
=======
>>>>>>> f9831f389bbd4c8cead389f203324848446efd60
		</div>
	</div>
</div>