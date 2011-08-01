{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>{$lblSlideshows|ucfirst}: {$lblAddImage}</h2>
</div>

{form:add}
	<div class="tabs">
		<ul>
			<li><a href="#tabContent">{$lblContent|ucfirst}</a></li>
		</ul>

		<div id="tabContent">
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td id="leftColumn">
						<div class="box">
							<div class="heading">
								<h3>{$lblTitle|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></h3>
							</div>
							<div class="options">
								{$txtTitle} {$txtTitleError}
							</div>
						</div>

						<div class="box">
							<div class="heading">
								<h3>{$lblImage|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></h3>
							</div>
							<div class="options">
								<label for="image">{$lblImage|ucfirst}</label>
								{$fileImage} {$fileImageError}
							</div>
						</div>

						<div class="box">
							<div class="heading">
								<div class="oneLiner">
									<h3>{$lblCaption|ucfirst}</h3>
									<abbr class="help">(?)</abbr>
									<div class="tooltip" style="display: none;">
										<p>{$msgHelpCaption}</p>
									</div>
								</div>
							</div>
							<div class="optionsRTE">
								{$txtCaption} {$txtCaptionError}
							</div>
						</div>
					</td>

					{*
					<td id="sidebar">
						<div class="box">
							<div class="heading">
								<h3>{$lblMeasurements|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></h3>
							</div>
							<div class="options">
								<p>
									<label for="width">{$lblWidth|ucfirst}</label>
									{$txtWidth} {$txtWidthError}
								</p>

								<p>
									<label for="height">{$lblHeight|ucfirst}</label>
									{$txtHeight} {$txtHeightError}
								</p>
							</div>
						</div>
					</td>
					*}
				</tr>
			</table>
		</div>
	</div>

	<div class="fullwidthOptions">
		<a href="{$var|geturl:'images'}&slideshow_id={$slideshow.id}" class="button">
			<span>{$lblBackToOverview|ucfirst}</span>
		</a>
		<div class="buttonHolderRight">
			<input id="editButton" class="inputButton button mainButton" type="submit" title="edit" value="{$lblSave|ucfirst}" />
		</div>
	</div>
{/form:add}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}