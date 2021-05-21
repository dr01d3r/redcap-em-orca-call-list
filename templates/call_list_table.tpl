{* Smarty *}
{* This template expects 2 vars: "data" with dsatra the table uses and "config", with config data (obviously) *}

<div class="card-body table-responsive">
    <div id="cl-table-ph">
        Loading data. Please wait...
    </div>
    {*Data table full of fields*}
    <table id="cl-table" class="table table-bordered table-condensed table-hover mb-0">
        <thead>
        <tr>
            {*Getting the table headers from all of the provided field names*}
            {foreach from=$config["display_fields"] key=field_name item=field_info}
                {if $field_info["display"] === true}
                    <th class="header">
                        {$field_info["label"]}
                        {if $field_name === "contact_attempts"}
                            <span class='cl-tooltip text-primary' data-toggle='popover'
                                  data-content='<b>Contact Attempts (24-hour format)</b><ul>{foreach from=$config["contact_attempts"]["ranges"] key=k item=v}<li><b>{$k}</b>: {$v["label"]}</li>{/foreach}</ul>'>
                                  <i class='fas fa-info-circle' style='display: inline;'></i>
                            </span>
                        {/if}
                    </th>
                {/if}
            {/foreach}
            {*Manually coding in headers for navigational buttons*}
            <th class="header">Record Home</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$data key=record_id item=record}
            <tr class="{$record["contact_result"]["status"]}" data-contact-result="{$record["contact_result"]["raw"]}" data-dropdown-filter="{$record[$config["filter_field"]["field_name"]]["raw"]}">
                {foreach from=$config["display_fields"] key=field_name item=field_info}
                    {if $field_info["display"] === true}
                        <td{if !empty($record[$field_name]["__SORT__"])} data-sort="{$record[$field_name]["__SORT__"]}"{/if}>
                            {if $record[$field_name]["alert"] === true}<i class="fas fa-exclamation-triangle text-danger"></i>&nbsp;{/if}
                            {if is_array($record[$field_name]["value"])}
                                {if count($record[$field_name]["value"]) > 0}
                                    <ul>
                                        {foreach from=$record[$field_name]["value"] key=sub_index item=sub_value}
                                            <li>{$sub_value}</li>
                                        {/foreach}
                                    </ul>
                                {/if}
                            {else}
                                {if $field_info["element_validation_type"] === "email"}
                                    {if !empty($record[$field_name]["value"])}
                                        <a class="btn-email d-block text-center" href="mailto:{$record[$field_name]["value"]}"><i class="far fa-envelope"></i></a>
                                    {/if}
                                {else}
                                    {$record[$field_name]["value"]}
                                {/if}
                            {/if}
                        </td>
                    {/if}
                {/foreach}
                <td>
                    <a href="{$record["dashboard_url"]}" class="btn btn-defaultrc btn-xs" role="button">
                        <i class="fas fa-edit"></i>&nbsp;Open
                    </a>
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>