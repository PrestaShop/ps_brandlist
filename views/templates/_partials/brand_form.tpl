<form>
  <select>
    <option value="0">{l s='All brands' d='Modules.Brandlist.Shop'}</option>
    {foreach from=$brands item=brand}
      <option value="{$brand['link']}">{$brand['name']}</option>
    {/foreach}
  </select>
</form>
