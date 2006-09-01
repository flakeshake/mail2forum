{include file='header.tpl'}

{if isset($display_options)}
	<h2>Schedule</h2>
	<form action="schedule.php" method="post">
		<input type="submit" name="action" value="{$lang.run_all_chains}" />
	</form>
{/if}

{if isset($results)}
	<p>Worked!</p>
{/if}

{include file='footer.tpl'}