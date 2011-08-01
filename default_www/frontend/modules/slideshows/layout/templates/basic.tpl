{*
	The CSS classes "basicSlides" and "basicSlidesContainer" should be kept to keep the JS from breaking.
*}

<div class="mod">
	<div class="inner">
		<div class="bd">
			<div class="basicSlides">
				<div class="basicSlidesContainer col col-6">
					{iteration:items}
					<div id="basic-{$items.index}" class="slide">
						<img src="{$items.image_url}" alt="{$items.title}" />

						{option:items.caption}
						<div class="caption">
							{$items.caption}
						</div>
						{/option:items.caption}
					</div>
					{/iteration:items}
				</div>
			</div>
		</div>
	</div>
</div>