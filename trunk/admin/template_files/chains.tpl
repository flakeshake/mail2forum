{include file='header.tpl'}
{if isset($list_chains)}
	<h2>{$lang.chains}</h2>
	<form action="chains.php" method="post">
		<table>
			<tr class="header"><th>{$lang.chain_name}</th><th>{$lang.chain_description}</th></tr>
{section name=chain loop=$chains}
			<tr><td><a href="chains.php?view=display_chain&chain_id={$chains[chain]->id}">{$chains[chain]->name}</a></td><td>{$chains[chain]->description}</td></tr>
{sectionelse}
		<tr><td colspan="2" class="centered">{$lang.no_chains}</td></tr>
{/section}
		</table>
	</form>
	<a href="chains.php?view=create_chain">{$lang.create_new_chain}</a>
{/if}

{if isset($display_chain)}
	<h2>{$lang.view_chain}</h2>
	<p><strong>{$lang.chain} {$chain->name}:</strong> {$chain->description}</p>
	<p><a href="chains.php?view=delete_chain&chain_id={$chain->id}">{$lang.delete_chain}</a></p>
	<h3>{$lang.channels}</h3>
	<table>
		<tr class="header"><th>{$lang.element_class}</th><th>{$lang.element_direction}</th></tr>
{section name=element loop=$chain->elements}
{assign var="class" value=$chain->elements[element]->class}
		<tr><td><a href="chains.php?view=view_chain_element&chain_id={$chain->id}&element_number={$smarty.section.element.index}">{$lang.$class.channel_name}</a></td><td>{if $chain->elements[element]->direction == 'in'}{$lang.channel_in}{else}{$lang.channel_out}{/if}</td></tr>
{sectionelse}
		<tr><td colspan="2" class="centered">{$lang.no_elements}</td></tr>
{/section}
	</table>
	<a href="chains.php?view=add_chain_channel&chain_id={$chain->id}">{$lang.add_chain_channel}</a>
{/if}

{if isset($delete_chain)}
	<h2>{$lang.delete_chain}</h2>
	<p><strong>{$lang.chain} {$chain->name}:</strong> {$chain->description}</p>
	<p>Are you sure you want to delete this chain?</p>
	<form action="chains.php" method="post">
		<input type="hidden" name="chain_id" value="{$chain->id}" />
		<input type="submit" name="action" value="{$lang.delete_chain}" />
	</form>
{/if}


{if isset($add_chain_channel)}
	<h2>{$lang.add_chain_channel}</h2>
	<form action="chains.php" method="get">
		<input type="hidden" name="view" value="configure_chain_channel" />
		<input type="hidden" name="chain_id" value="{$chain_id}" />
		<p>{$lang.channel}: {html_options name=channel_id options=$channels}</p>
		<input type="submit" name="action" value="{$lang.add_chain_channel}" />
	</form>
{/if}

{if isset($view_chain_element)}
{/if}

{if isset($configure_chain_channel_direction)}
{assign var="channel_class" value=$channel->class}
	<h2>{$lang.$channel_class.channel_name}: {$lang.configure_channel_direction}</h2>
	<form action="chains.php" method="post">
		<input type="hidden" name="chain_id" value="{$chain_id}" />
		<input type="hidden" name="channel_id" value="{$channel->id}" />
		<table>
			<tr>
				<th>
					{$lang.channel_direction}
					<span class="field_descr">{$lang.channel_type_desc}</span>
				</th>
				<td>
					{$lang.channel_in}: <input type="radio" name="direction" value="in" />&nbsp;&nbsp;
					{$lang.channel_out}: <input type="radio" name="direction" value="out" />
				</td>
			</tr>
		</table>
		<input type="submit" name="action" value="{$lang.save_chain_channel_direction}" />
	</form>
{/if}

{if isset($configure_chain_channel_fields)}
	{assign var="channel_class" value=$channel->class|lower}
	<h2>{$lang.$channel_class.channel_name}: {$lang.configure_channel_fields}</h2>
	<form action="chains.php" method="post">
		<input type="hidden" name="chain_id" value="{$chain_id}" />
		<input type="hidden" name="channel_class" value="{$channel_class}" />
		<input type="hidden" name="channel_id" value="{$channel->id}" />
		<input type="hidden" name="direction" value="{$direction}" />
		<table>
{section name=field loop=$channel->channel_fields}
			{strip}
			{assign var="field" value=$channel->channel_fields[field].name}
			{assign var="fieldname" value="field_`$field`"}
			{assign var="fielddescr" value="field_descr_`$field`"}
			{assign var="size" value=$channel->channel_fields[field].size}
			<tr>
				<th>
					{$lang.$channel_class.$fieldname}
					<span class="field_descr">{$lang.$channel_class.$fielddescr}</span>
				</th>
				<td>
					{if $channel->channel_fields[field].type == 'boolean'}{$lang.yes|capitalize}: <input type="radio" name="{$channel->channel_fields[field].name}" value="1" {if $channel->channel_fields[field].value=='1'} checked{/if} />&nbsp; &nbsp;{$lang.no|capitalize}: <input type="radio" name="{$channel->channel_fields[field].name}" value="0" {if $channel->channel_fields[field].value==='0'} checked{/if} />{/if}
					{if $channel->channel_fields[field].type == 'integer' || $channel->channel_fields[field].type == 'float'}<input type="text" size="{if ($size == 0 || $size > 5)}5{else}{$size}{/if}" name="{$channel->channel_fields[field].name}"{if $size != 0} maxlength="{$size}"{/if}{if $channel->channel_fields[field].value} value="{$channel->channel_fields[field].value}"{/if} />{/if}
					{if $channel->channel_fields[field].type == 'string'}<input type="text" size="{if ($size == 0 || $size > 30)}30{else}{$size}{/if}" name="{$channel->channel_fields[field].name}"{if $size != 0} maxlength="{$size}"{/if}{if $channel->channel_fields[field].value} value="{$channel->channel_fields[field].value}"{/if} />{/if}
				</td>
			</tr>{/strip}
{sectionelse}
		<tr><td colspan="2" class="centered">{$lang.no_fields}</td></tr>
{/section}
		</table>
		<input type="submit" name="action" value="{$lang.save_chain_channel}" />
	</form>
{/if}

{if isset($new_chain)}
	<h2>{$lang.create_new_chain}</h2>
	<form action="chains.php" method="post">
		<table>
			<tr><td>{$lang.chain_name}</td><td><input name="chain_name" size="20" maxlength="50" value="{$chain_name}" /></td></tr>
			<tr><td>{$lang.chain_description}</td><td><input name="chain_description" size="20" maxlength="255" value="{$chain_description}" /></td></tr>
		</table>
		<input type="submit" name="action" value="{$lang.create_chain}" />
	</form>
{/if}
{include file='footer.tpl'}