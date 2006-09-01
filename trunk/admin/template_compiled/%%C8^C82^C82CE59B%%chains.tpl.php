<?php /* Smarty version 2.6.14, created on 2006-08-31 17:38:46
         compiled from chains.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'chains.tpl', 50, false),array('modifier', 'lower', 'chains.tpl', 81, false),array('modifier', 'capitalize', 'chains.tpl', 101, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'header.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
  if (isset ( $this->_tpl_vars['list_chains'] )): ?>
	<h2><?php echo $this->_tpl_vars['lang']['chains']; ?>
</h2>
	<form action="chains.php" method="post">
		<table>
			<tr class="header"><th><?php echo $this->_tpl_vars['lang']['chain_name']; ?>
</th><th><?php echo $this->_tpl_vars['lang']['chain_description']; ?>
</th></tr>
<?php unset($this->_sections['chain']);
$this->_sections['chain']['name'] = 'chain';
$this->_sections['chain']['loop'] = is_array($_loop=$this->_tpl_vars['chains']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['chain']['show'] = true;
$this->_sections['chain']['max'] = $this->_sections['chain']['loop'];
$this->_sections['chain']['step'] = 1;
$this->_sections['chain']['start'] = $this->_sections['chain']['step'] > 0 ? 0 : $this->_sections['chain']['loop']-1;
if ($this->_sections['chain']['show']) {
    $this->_sections['chain']['total'] = $this->_sections['chain']['loop'];
    if ($this->_sections['chain']['total'] == 0)
        $this->_sections['chain']['show'] = false;
} else
    $this->_sections['chain']['total'] = 0;
if ($this->_sections['chain']['show']):

            for ($this->_sections['chain']['index'] = $this->_sections['chain']['start'], $this->_sections['chain']['iteration'] = 1;
                 $this->_sections['chain']['iteration'] <= $this->_sections['chain']['total'];
                 $this->_sections['chain']['index'] += $this->_sections['chain']['step'], $this->_sections['chain']['iteration']++):
$this->_sections['chain']['rownum'] = $this->_sections['chain']['iteration'];
$this->_sections['chain']['index_prev'] = $this->_sections['chain']['index'] - $this->_sections['chain']['step'];
$this->_sections['chain']['index_next'] = $this->_sections['chain']['index'] + $this->_sections['chain']['step'];
$this->_sections['chain']['first']      = ($this->_sections['chain']['iteration'] == 1);
$this->_sections['chain']['last']       = ($this->_sections['chain']['iteration'] == $this->_sections['chain']['total']);
?>
			<tr><td><a href="chains.php?view=display_chain&chain_id=<?php echo $this->_tpl_vars['chains'][$this->_sections['chain']['index']]->id; ?>
"><?php echo $this->_tpl_vars['chains'][$this->_sections['chain']['index']]->name; ?>
</a></td><td><?php echo $this->_tpl_vars['chains'][$this->_sections['chain']['index']]->description; ?>
</td></tr>
<?php endfor; else: ?>
		<tr><td colspan="2" class="centered"><?php echo $this->_tpl_vars['lang']['no_chains']; ?>
</td></tr>
<?php endif; ?>
		</table>
	</form>
	<a href="chains.php?view=create_chain"><?php echo $this->_tpl_vars['lang']['create_new_chain']; ?>
</a>
<?php endif; ?>

<?php if (isset ( $this->_tpl_vars['display_chain'] )): ?>
	<h2><?php echo $this->_tpl_vars['lang']['view_chain']; ?>
</h2>
	<p><strong><?php echo $this->_tpl_vars['lang']['chain']; ?>
 <?php echo $this->_tpl_vars['chain']->name; ?>
:</strong> <?php echo $this->_tpl_vars['chain']->description; ?>
</p>
	<p><a href="chains.php?view=delete_chain&chain_id=<?php echo $this->_tpl_vars['chain']->id; ?>
"><?php echo $this->_tpl_vars['lang']['delete_chain']; ?>
</a></p>
	<h3><?php echo $this->_tpl_vars['lang']['channels']; ?>
</h3>
	<table>
		<tr class="header"><th><?php echo $this->_tpl_vars['lang']['element_class']; ?>
</th><th><?php echo $this->_tpl_vars['lang']['element_direction']; ?>
</th></tr>
<?php unset($this->_sections['element']);
$this->_sections['element']['name'] = 'element';
$this->_sections['element']['loop'] = is_array($_loop=$this->_tpl_vars['chain']->elements) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['element']['show'] = true;
$this->_sections['element']['max'] = $this->_sections['element']['loop'];
$this->_sections['element']['step'] = 1;
$this->_sections['element']['start'] = $this->_sections['element']['step'] > 0 ? 0 : $this->_sections['element']['loop']-1;
if ($this->_sections['element']['show']) {
    $this->_sections['element']['total'] = $this->_sections['element']['loop'];
    if ($this->_sections['element']['total'] == 0)
        $this->_sections['element']['show'] = false;
} else
    $this->_sections['element']['total'] = 0;
if ($this->_sections['element']['show']):

            for ($this->_sections['element']['index'] = $this->_sections['element']['start'], $this->_sections['element']['iteration'] = 1;
                 $this->_sections['element']['iteration'] <= $this->_sections['element']['total'];
                 $this->_sections['element']['index'] += $this->_sections['element']['step'], $this->_sections['element']['iteration']++):
$this->_sections['element']['rownum'] = $this->_sections['element']['iteration'];
$this->_sections['element']['index_prev'] = $this->_sections['element']['index'] - $this->_sections['element']['step'];
$this->_sections['element']['index_next'] = $this->_sections['element']['index'] + $this->_sections['element']['step'];
$this->_sections['element']['first']      = ($this->_sections['element']['iteration'] == 1);
$this->_sections['element']['last']       = ($this->_sections['element']['iteration'] == $this->_sections['element']['total']);
 $this->assign('class', $this->_tpl_vars['chain']->elements[$this->_sections['element']['index']]->class); ?>
		<tr><td><a href="chains.php?view=view_chain_element&chain_id=<?php echo $this->_tpl_vars['chain']->id; ?>
&element_number=<?php echo $this->_sections['element']['index']; ?>
"><?php echo $this->_tpl_vars['lang'][$this->_tpl_vars['class']]['channel_name']; ?>
</a></td><td><?php if ($this->_tpl_vars['chain']->elements[$this->_sections['element']['index']]->direction == 'in'):  echo $this->_tpl_vars['lang']['channel_in'];  else:  echo $this->_tpl_vars['lang']['channel_out'];  endif; ?></td></tr>
<?php endfor; else: ?>
		<tr><td colspan="2" class="centered"><?php echo $this->_tpl_vars['lang']['no_elements']; ?>
</td></tr>
<?php endif; ?>
	</table>
	<a href="chains.php?view=add_chain_channel&chain_id=<?php echo $this->_tpl_vars['chain']->id; ?>
"><?php echo $this->_tpl_vars['lang']['add_chain_channel']; ?>
</a>
<?php endif; ?>

<?php if (isset ( $this->_tpl_vars['delete_chain'] )): ?>
	<h2><?php echo $this->_tpl_vars['lang']['delete_chain']; ?>
</h2>
	<p><strong><?php echo $this->_tpl_vars['lang']['chain']; ?>
 <?php echo $this->_tpl_vars['chain']->name; ?>
:</strong> <?php echo $this->_tpl_vars['chain']->description; ?>
</p>
	<p>Are you sure you want to delete this chain?</p>
	<form action="chains.php" method="post">
		<input type="hidden" name="chain_id" value="<?php echo $this->_tpl_vars['chain']->id; ?>
" />
		<input type="submit" name="action" value="<?php echo $this->_tpl_vars['lang']['delete_chain']; ?>
" />
	</form>
<?php endif; ?>


<?php if (isset ( $this->_tpl_vars['add_chain_channel'] )): ?>
	<h2><?php echo $this->_tpl_vars['lang']['add_chain_channel']; ?>
</h2>
	<form action="chains.php" method="get">
		<input type="hidden" name="view" value="configure_chain_channel" />
		<input type="hidden" name="chain_id" value="<?php echo $this->_tpl_vars['chain_id']; ?>
" />
		<p><?php echo $this->_tpl_vars['lang']['channel']; ?>
: <?php echo smarty_function_html_options(array('name' => 'channel_id','options' => $this->_tpl_vars['channels']), $this);?>
</p>
		<input type="submit" name="action" value="<?php echo $this->_tpl_vars['lang']['add_chain_channel']; ?>
" />
	</form>
<?php endif; ?>

<?php if (isset ( $this->_tpl_vars['view_chain_element'] )):  endif; ?>

<?php if (isset ( $this->_tpl_vars['configure_chain_channel_direction'] )):  $this->assign('channel_class', $this->_tpl_vars['channel']->class); ?>
	<h2><?php echo $this->_tpl_vars['lang'][$this->_tpl_vars['channel_class']]['channel_name']; ?>
: <?php echo $this->_tpl_vars['lang']['configure_channel_direction']; ?>
</h2>
	<form action="chains.php" method="post">
		<input type="hidden" name="chain_id" value="<?php echo $this->_tpl_vars['chain_id']; ?>
" />
		<input type="hidden" name="channel_id" value="<?php echo $this->_tpl_vars['channel']->id; ?>
" />
		<table>
			<tr>
				<th>
					<?php echo $this->_tpl_vars['lang']['channel_direction']; ?>

					<span class="field_descr"><?php echo $this->_tpl_vars['lang']['channel_type_desc']; ?>
</span>
				</th>
				<td>
					<?php echo $this->_tpl_vars['lang']['channel_in']; ?>
: <input type="radio" name="direction" value="in" />&nbsp;&nbsp;
					<?php echo $this->_tpl_vars['lang']['channel_out']; ?>
: <input type="radio" name="direction" value="out" />
				</td>
			</tr>
		</table>
		<input type="submit" name="action" value="<?php echo $this->_tpl_vars['lang']['save_chain_channel_direction']; ?>
" />
	</form>
<?php endif; ?>

<?php if (isset ( $this->_tpl_vars['configure_chain_channel_fields'] )): ?>
	<?php $this->assign('channel_class', ((is_array($_tmp=$this->_tpl_vars['channel']->class)) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp))); ?>
	<h2><?php echo $this->_tpl_vars['lang'][$this->_tpl_vars['channel_class']]['channel_name']; ?>
: <?php echo $this->_tpl_vars['lang']['configure_channel_fields']; ?>
</h2>
	<form action="chains.php" method="post">
		<input type="hidden" name="chain_id" value="<?php echo $this->_tpl_vars['chain_id']; ?>
" />
		<input type="hidden" name="channel_class" value="<?php echo $this->_tpl_vars['channel_class']; ?>
" />
		<input type="hidden" name="channel_id" value="<?php echo $this->_tpl_vars['channel']->id; ?>
" />
		<input type="hidden" name="direction" value="<?php echo $this->_tpl_vars['direction']; ?>
" />
		<table>
<?php unset($this->_sections['field']);
$this->_sections['field']['name'] = 'field';
$this->_sections['field']['loop'] = is_array($_loop=$this->_tpl_vars['channel']->channel_fields) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['field']['show'] = true;
$this->_sections['field']['max'] = $this->_sections['field']['loop'];
$this->_sections['field']['step'] = 1;
$this->_sections['field']['start'] = $this->_sections['field']['step'] > 0 ? 0 : $this->_sections['field']['loop']-1;
if ($this->_sections['field']['show']) {
    $this->_sections['field']['total'] = $this->_sections['field']['loop'];
    if ($this->_sections['field']['total'] == 0)
        $this->_sections['field']['show'] = false;
} else
    $this->_sections['field']['total'] = 0;
if ($this->_sections['field']['show']):

            for ($this->_sections['field']['index'] = $this->_sections['field']['start'], $this->_sections['field']['iteration'] = 1;
                 $this->_sections['field']['iteration'] <= $this->_sections['field']['total'];
                 $this->_sections['field']['index'] += $this->_sections['field']['step'], $this->_sections['field']['iteration']++):
$this->_sections['field']['rownum'] = $this->_sections['field']['iteration'];
$this->_sections['field']['index_prev'] = $this->_sections['field']['index'] - $this->_sections['field']['step'];
$this->_sections['field']['index_next'] = $this->_sections['field']['index'] + $this->_sections['field']['step'];
$this->_sections['field']['first']      = ($this->_sections['field']['iteration'] == 1);
$this->_sections['field']['last']       = ($this->_sections['field']['iteration'] == $this->_sections['field']['total']);
?>
			<?php echo '';  $this->assign('field', $this->_tpl_vars['channel']->channel_fields[$this->_sections['field']['index']]['name']);  echo '';  $this->assign('fieldname', "field_".($this->_tpl_vars['field']));  echo '';  $this->assign('fielddescr', "field_descr_".($this->_tpl_vars['field']));  echo '';  $this->assign('size', $this->_tpl_vars['channel']->channel_fields[$this->_sections['field']['index']]['size']);  echo '<tr><th>';  echo $this->_tpl_vars['lang'][$this->_tpl_vars['channel_class']][$this->_tpl_vars['fieldname']];  echo '<span class="field_descr">';  echo $this->_tpl_vars['lang'][$this->_tpl_vars['channel_class']][$this->_tpl_vars['fielddescr']];  echo '</span></th><td>';  if ($this->_tpl_vars['channel']->channel_fields[$this->_sections['field']['index']]['type'] == 'boolean'):  echo '';  echo ((is_array($_tmp=$this->_tpl_vars['lang']['yes'])) ? $this->_run_mod_handler('capitalize', true, $_tmp) : smarty_modifier_capitalize($_tmp));  echo ': <input type="radio" name="';  echo $this->_tpl_vars['channel']->channel_fields[$this->_sections['field']['index']]['name'];  echo '" value="1" ';  if ($this->_tpl_vars['channel']->channel_fields[$this->_sections['field']['index']]['value'] == '1'):  echo ' checked';  endif;  echo ' />&nbsp; &nbsp;';  echo ((is_array($_tmp=$this->_tpl_vars['lang']['no'])) ? $this->_run_mod_handler('capitalize', true, $_tmp) : smarty_modifier_capitalize($_tmp));  echo ': <input type="radio" name="';  echo $this->_tpl_vars['channel']->channel_fields[$this->_sections['field']['index']]['name'];  echo '" value="0" ';  if ($this->_tpl_vars['channel']->channel_fields[$this->_sections['field']['index']]['value'] === '0'):  echo ' checked';  endif;  echo ' />';  endif;  echo '';  if ($this->_tpl_vars['channel']->channel_fields[$this->_sections['field']['index']]['type'] == 'integer' || $this->_tpl_vars['channel']->channel_fields[$this->_sections['field']['index']]['type'] == 'float'):  echo '<input type="text" size="';  if (( $this->_tpl_vars['size'] == 0 || $this->_tpl_vars['size'] > 5 )):  echo '5';  else:  echo '';  echo $this->_tpl_vars['size'];  echo '';  endif;  echo '" name="';  echo $this->_tpl_vars['channel']->channel_fields[$this->_sections['field']['index']]['name'];  echo '"';  if ($this->_tpl_vars['size'] != 0):  echo ' maxlength="';  echo $this->_tpl_vars['size'];  echo '"';  endif;  echo '';  if ($this->_tpl_vars['channel']->channel_fields[$this->_sections['field']['index']]['value']):  echo ' value="';  echo $this->_tpl_vars['channel']->channel_fields[$this->_sections['field']['index']]['value'];  echo '"';  endif;  echo ' />';  endif;  echo '';  if ($this->_tpl_vars['channel']->channel_fields[$this->_sections['field']['index']]['type'] == 'string'):  echo '<input type="text" size="';  if (( $this->_tpl_vars['size'] == 0 || $this->_tpl_vars['size'] > 30 )):  echo '30';  else:  echo '';  echo $this->_tpl_vars['size'];  echo '';  endif;  echo '" name="';  echo $this->_tpl_vars['channel']->channel_fields[$this->_sections['field']['index']]['name'];  echo '"';  if ($this->_tpl_vars['size'] != 0):  echo ' maxlength="';  echo $this->_tpl_vars['size'];  echo '"';  endif;  echo '';  if ($this->_tpl_vars['channel']->channel_fields[$this->_sections['field']['index']]['value']):  echo ' value="';  echo $this->_tpl_vars['channel']->channel_fields[$this->_sections['field']['index']]['value'];  echo '"';  endif;  echo ' />';  endif;  echo '</td></tr>'; ?>

<?php endfor; else: ?>
		<tr><td colspan="2" class="centered"><?php echo $this->_tpl_vars['lang']['no_fields']; ?>
</td></tr>
<?php endif; ?>
		</table>
		<input type="submit" name="action" value="<?php echo $this->_tpl_vars['lang']['save_chain_channel']; ?>
" />
	</form>
<?php endif; ?>

<?php if (isset ( $this->_tpl_vars['new_chain'] )): ?>
	<h2><?php echo $this->_tpl_vars['lang']['create_new_chain']; ?>
</h2>
	<form action="chains.php" method="post">
		<table>
			<tr><td><?php echo $this->_tpl_vars['lang']['chain_name']; ?>
</td><td><input name="chain_name" size="20" maxlength="50" value="<?php echo $this->_tpl_vars['chain_name']; ?>
" /></td></tr>
			<tr><td><?php echo $this->_tpl_vars['lang']['chain_description']; ?>
</td><td><input name="chain_description" size="20" maxlength="255" value="<?php echo $this->_tpl_vars['chain_description']; ?>
" /></td></tr>
		</table>
		<input type="submit" name="action" value="<?php echo $this->_tpl_vars['lang']['create_chain']; ?>
" />
	</form>
<?php endif;  $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'footer.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>