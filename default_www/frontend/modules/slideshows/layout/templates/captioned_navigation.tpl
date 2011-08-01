{*
	The CSS classes "cpSlides" and "cpSlidesContainer" should be kept to keep the JS from breaking.
*}

<div class="mod">
	<div class="inner">
		<div class="bd">
			<div class="cpSlides">
				<div class="cpSlidesContainer col col-9">
					{iteration:items}
					<div id="cp-{$items.index}" class="slide">
						<img src="{$items.image_url}" alt="{$items.title}" />
					</div>
					{/iteration:items}
				</div>

				{* The slideshow's pagination *}
				<div class="col col-3 lastCol">
					<ul class="slidesPagination">
						{iteration:items}

						<li rel="#{$items.index}">
							{$items.title}

							{*
								You can't use any other anchor elements then the one below, because
								the anchor is the pagination trigger. The JS will go bananas if you
								use multiples ones.
								If you need more links, you can put the class
								"followLink" on a span that has a rel with the target URL.
							*}
							<span class="followLink" rel="http://www.google.be">test</span>

							<a class="hidden" href="#"></a>
						</li>
						{/iteration:items}
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>