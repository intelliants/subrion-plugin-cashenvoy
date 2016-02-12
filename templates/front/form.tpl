<form action="{if $core.config.cashenvoy_demo}https://www.cashenvoy.com/sandbox/?cmd=cepay{else}https://www.cashenvoy.com/webservice/?cmd=cepay{/if}" method="post" style="display:none">
	{foreach $formValues as $key => $value}
		<input type="hidden" name="{$key}" value="{$value}">
	{/foreach}
	<input type="hidden" name="ce_window" value="parent"/><!-- self or parent -->
</form>