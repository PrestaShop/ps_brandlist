<div class="brands-sort dropdown">
  <button
    class="btn-unstyle select-title"
    rel="nofollow"
    data-toggle="dropdown"
    aria-haspopup="true"
    aria-expanded="false">
    {l s='All brands' d='Modules.Brandlist.Shop'}
    <i class="material-icons float-xs-right">arrow_drop_down</i>
  </button>
  <div class="dropdown-menu">
    {foreach from=$brands item=brand}
      <a
        rel="nofollow"
        href="{$brand['link']}"
        class="select-list dropdown-item"
      >
        {$brand['name']}
      </a>
    {/foreach}
  </div>
</div>
