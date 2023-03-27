{* Smarty *}

<div class="card-header" id="filters_root">
    <form class="" action="" method="POST">
        <div class="row">
            <div class="col">
                <h5>{$filter_data["display_title"]}</h5>
            </div>
            {if !empty($filter_data["filter_field"])}
                <div class="col-auto">
                    <p class="my-1 font-weight-bold">{$filter_data["filter_field"]["field_label"]}</p>
                    <select id="ddlfilter" name="extra_filter_field_value" class="form-control form-select" style="width: 300px">
                        <option value="">--</option>
                        {foreach from=$filter_data["filter_field"]["field_values"] key=k item=v}
                            <option value="{$k}">{$v}</option>
                        {/foreach}
                    </select>
                </div>
            {/if}
        </div>

        <hr class="my-1 mt-2" />

        <!-- Hidden fields used for extra data for the self-submit -->
        <input type="hidden" name="show_entries_number" value="{$config['show_entries_number']}">
        <input type="hidden" name="requested_page" value="{$config['requested_page']}">
        <input type="hidden" name="extra_filter_field_name" value="{$config['filter_field']['field_name']}">
        <input type="hidden" name="search_input" value="">

        <!-- End of hidden fields -->
        <div class="row">
            <div class="mb-0 mt-1 col-12">
                <h5>Filters</h5>
            </div>
        </div>
        <div class="row">
            <div class="form-check col-3 mb-1 ml-3">
                <input class="form-check-input" type="checkbox" name="selected_filter_field_ids[]" id="filter_field_id_no_contact" value="no_contact">
                <label class="form-check-label" for="filter_field_id_no_contact">No Contact Result</label>
            </div>

            {*Setting up check boxes to select multiple statuses*}
            {foreach from=$contact_result_metadata key=k item=v}
                <div class="form-check col-3 mb-1 ml-3">
                    <input class="form-check-input" type="checkbox" name="selected_filter_field_ids[]" id="filter_field_id_{$v["key"]}" value="{$v["key"]}">
                    <label class="form-check-label" for="filter_field_id_{$v["key"]}">{$v["label"]}</label>
                </div>
            {/foreach}
        </div>
        <hr class="my-1 mt-2" />
        <div class="row mt-1">
            <div class="col-lg-6">
                <div class="row">
                    <div class="mb-0 mt-1 col-12">
                        <h5>Legend</h5>
                    </div>
                </div>
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

        {if array_key_exists("call_back_date_time", $filter_data["display_fields"])}
            <div class="row">
                <div class="mb-0 col-6">
                    <i class="fas fa-exclamation-triangle text-danger"></i>&nbsp;<span>Call Back Date/Time has been exceeded</span>
                </div>
            </div>
        {/if}

        <hr class="my-1" />
        <div class="row">
            <div class="col-12">
                <button class="btn btn-secondary mb-0 mt-2 float-right" type="submit">Apply Filters</button>
            </div>
        </div>
    </form>
</div>