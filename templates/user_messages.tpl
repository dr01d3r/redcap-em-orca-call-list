{* Smarty *}

{foreach from=$user_messages["messages"] item=message}
    <div class="alert alert-info alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <b>Info:</b> {$message}
    </div>
{/foreach}

{foreach from=$user_messages["warnings"] item=warning}
    <div class="alert alert-warning alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <b>Warning:</b> {$warning}
    </div>
{/foreach}

{foreach from=$user_messages["errors"] item=error}
    <div class="alert alert-danger alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <b>Error:</b> {$error}
    </div>
{/foreach}