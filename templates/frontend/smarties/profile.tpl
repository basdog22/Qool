<div class="row thumbnails">
	<div class="6u">
	{assign var="user" value="user"|get_array}
	
		<h3>{$user.username}</h3>
		<dl>
		{foreach from=$user.data item="data" key="it"}
		<dt>{$it}</dt>
		<dd>{$data}</dd>
		{/foreach}
		</dl>
	</div>
	{if "formTitle"|isActive}
	<div class="6u">
	{'theForm'|showForm}
		
	</div>
	{/if}
</div>