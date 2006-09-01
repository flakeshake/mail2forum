{include file='header.tpl'}
{if isset($list_uninstalled_channels)}
	<h2>{$lang.install_channels}</h2>
	<form action="channels.php" name="channels" method="post">
{if isset($chain_id)}
		<input type="hidden" name="chain_id" value="{$chain_id}" />
{/if}
		<table>
			<tr class="header">{if $uninstalled_channels}<th class="toggle"><a href="javascript:void(null);" onclick="checkAll('channel_path[]')">&#8595;</a></th>{/if}<th>{$lang.channel}</th><th>{$lang.channel_path}</th></tr>
{section name=uninstalled loop=$uninstalled_channels}
			<tr><td><input type="checkbox" name="channel_path[]" value="{$uninstalled_channels[uninstalled].path}" /></td><td>{$uninstalled_channels[uninstalled].name}</td><td>{$uninstalled_channels[uninstalled].path}</td></tr>
{sectionelse}
			<tr><td colspan="2" class="centered">{$lang.no_uninstalled_channels}</td></tr>
{/section}
		</table>
	{if $uninstalled_channels}
	<input type="submit" name="action" value="{$lang.install_selected_channels}" />
	{/if}
	</form>
{/if}

{if isset($list_installed_channels)}
	<h2>{$lang.channels}</h2>
	<form action="channels.php" method="post">
		<table>
			<tr class="header"><th>{$lang.channel}</th><th>{$lang.channel_path}</th></tr>
{section name=installed loop=$installed_channels}
			<tr><th>{$installed_channels[installed]->name}</th><td>{$installed_channels[installed]->path}</td></tr>
{/section}
		</table>
	</form>
	{if isset($show_install_channel_link)}<a href="channels.php?view=uninstalled_channels">{$lang.install_channels}</a>{/if}
{/if}
{include file='footer.tpl'}