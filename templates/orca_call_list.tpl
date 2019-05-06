{* Smarty *}

{foreach from=$config["messages"] item=message}
    <div class="alert alert-info alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <b>Info:</b> {$message}
    </div>
{/foreach}

{foreach from=$config["warnings"] item=warning}
    <div class="alert alert-warning alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <b>Warning:</b> {$warning}
    </div>
{/foreach}

{foreach from=$config["errors"] item=error}
    <div class="alert alert-danger alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <b>Error:</b> {$error}
    </div>
{/foreach}

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col">
                <h5>{$config["display_title"]}</h5>
            </div>
            {if !empty($config["selected_display_filter"])}
                <div class="col-auto">
                    <select id="ddlfilter" class="form-control" style="width: 300px">
                        <option value="">Select Filter</option>
                        {foreach from=$filter_dropdown_options key=k item=v}
                            <option value="{$k}">{$v}</option>
                        {/foreach}
                    </select>
                </div>
            {/if}
        </div>

        <hr class="my-1" />
        <div class="row">
            {*Setting up check boxes to select multiple statuses*}
            {foreach from=$contact_result_metadata key=k item=v}
                <label class="col-3 mb-1">
                    <input class="contact-result align-middle" type="checkbox" value="{$v["key"]}">
                    {$v["label"]}
                </label>
            {/foreach}
        </div>
        <hr class="my-1" />
        <div class="row mt-1">
            <div class="col-lg-6">
                <div class="row">
                    {foreach from=$contact_result_metadata key=k item=v}
                        {if !empty($v["status"])}
                            <div class="mb-0 col-6">
                                <div class="cl-filter-square align-middle border border-dark {$v["status"]}"></div>
                                {$v["label"]}
                            </div>
                        {/if}
                    {/foreach}
                </div>
            </div>
        </div>

        {if array_key_exists("call_back_date_time", $config["display_fields"])}
            <hr class="my-1" />
            <div class="row">
                <div class="col-auto">
                    <i class="fas fa-exclamation-triangle text-danger"></i>&nbsp;<span>Call Back Date/Time has been exceeded</span>
                </div>
            </div>
        {/if}
    </div>
    <div class="card-body table-responsive">
        <div id="cl-table-ph">
            Loading data. Please wait...
        </div>
        {*Data table full of fields defined in index.php*}
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
                <tr class="{$record["contact_result"]["status"]}" data-contact-result="{$record["contact_result"]["raw"]}" data-dropdown-filter="{$record[{$filter_dropdown_field}]["raw"]}">
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
</div>

<script type="text/javascript">
    $(function () {

        var table;
        $.fn.dataTableExt.afnFiltering.push(
            function (oSettings, aData, iDataIndex) {
                var row = oSettings.aoData[iDataIndex].nTr;
                var row_status_contact_result = $(row).data("contact-result") + "";
                var row_status_dropdown_filter = $(row).data("dropdown-filter") + "";
                var selected_status_contact_result = $("input.contact-result:checked").map(function () {
                    return $(this).val() + "";
                }).get();
                var selected_status_dropdown_list = $("#ddlfilter :selected").map(function () {
                    if($(this).val()!=""){
                        return $(this).val() + "";
                    }
                }).get();

                return (selected_status_contact_result.length === 0 || selected_status_contact_result.indexOf( row_status_contact_result) >= 0) && (selected_status_dropdown_list.length === 0 || selected_status_dropdown_list.indexOf(row_status_dropdown_filter) >= 0 || row_status_dropdown_filter.includes(selected_status_dropdown_list[0])) ;
            }
        );

        function loadCheckBoxSelection() {

            let checkboxselections = {$call_list_selections};
            $.each(checkboxselections, function(key, value) {
                if(key!='ddlfilter') {
                    value = (!!parseInt(value) ? true : false)
                    $("input[type=checkbox][value=" + key + "]").prop("checked", value);
                }
                else
                    $('select option[value="' + value +'"]').prop("selected", true);
            });
            table.draw();
        }

        function rememberSelection() {
            let call_list_selectors = $('input:checkbox,#ddlfilter')
            let checkboxSelections = { };
            let checkboxes = $("#center :checkbox");
            call_list_selectors.on("change", function(){
                 checkboxes.each(function(){
                    checkboxSelections[this.value] = this.checked;
                });
                let dropdownSelections = { };
                dropdownSelections[$('#ddlfilter').attr('id')] = $('#ddlfilter').find(":selected").val();
                let callListSelections = { checkboxSelections,dropdownSelections };
                $.ajax({
                    type: 'POST',
                    url: '{$ajax_url}',
                    data: JSON.stringify(callListSelections),
                    contentType: 'application/json; charset=utf-8',
                    success:  function(data) {

                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        console.error(errorThrown);
                    }
                });
            });
        }
        function initDataTable() {
            table = $("#cl-table").DataTable({
                initComplete: function () {
                    $("#cl-table").css('width', '100%').show();
                    $("#cl-table-ph").hide();
                }
            });

            $('input[type="checkbox"]').on('change', function () {
                table.draw();
            });
            $("#ddlfilter").on('change', function(){
                table.draw();
            })
        }
        $('.cl-tooltip').popover({
            container: 'body',
            html: true,
            trigger: 'hover'
        });
        initDataTable();
        loadCheckBoxSelection();
        rememberSelection();
    });
</script>