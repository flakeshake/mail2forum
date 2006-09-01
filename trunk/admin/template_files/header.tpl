<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
	<title>{$lang.title}</title>
	<link rel="stylesheet" href="css/main.css" type="text/css" media="all" />
{literal}

	<script language="Javascript" type="text/javascript">

		var all_checked = false;
		function checkAll(checkWhat) {
			var inputs = document.getElementsByTagName("input");
			all_checked = !all_checked;
			for (index = 0; index < inputs.length; index++) {
				if (inputs[index].name == checkWhat) {
					inputs[index].checked = all_checked;
				}
			}
		}        
		
	</script>
{/literal}
</head>
<body>

{if !isset($hide_menu)}{include file='menubar.tpl'}{/if}
	<h1>{$lang.title}</h1>
{if isset($message)}
	<div class="message"><p>{$message}</p></div>
{/if}
{if isset($errors)}
	<div class="error"><span class="lable">{$lang.error}:</span>
{section name=errors loop=$errors}
		<p class="{$errors[errors].type}">{$errors[errors].message}</p>
{/section}
	</div>
{/if}