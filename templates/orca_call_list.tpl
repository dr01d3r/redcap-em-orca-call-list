{* Smarty *}
<span id="callListRoot">
    {include 'user_messages.tpl' user_messages=$config}

    <div class="card">
        {include 'call_list_filters.tpl' filter_data=$config contact_result_metadata=$contact_result_metadata}

        {include 'call_list_table.tpl' table_data=$data config=$config }
    </div>
</span>

<script type="text/javascript">
    $(function () {
        var table;
        var root = $('#callListRoot');
        var currentSaveTimerID = null;
        var pageState = {json_encode($page_state)};

        function attachHandlers(){
            root.on('change', 'input:checkbox,#ddlfilter', function(){
                if(currentSaveTimerID != null) {
                    clearTimeout(currentSaveTimerID);
                }
                currentSaveTimerID = setTimeout(rememberSelection, 200);
            });

            root.on('click', '#cl-table_paginate .paginate_button:not(.current):not(.disabled)', function(){
                var wantedPage = table.page();
                console.log('Clicked DataTables page: '+wantedPage);
            });
        }

        function rememberSelection() {
            let call_list_selectors = $('input:checkbox,#ddlfilter');
            let checkboxSelections = { };
            let checkboxes = $("#center :checkbox");

            checkboxes.each(function(){
                checkboxSelections[this.value] = this.checked;
            });
            let dropdownSelections = { };
            dropdownSelections[$('#ddlfilter').attr('id')] = $('#ddlfilter').find(":selected").val();
            let callListSelections = {
                'checkboxSelections': checkboxSelections,
                'dropdownSelections': dropdownSelections
            };

            pageState["call_list_selections"] = callListSelections;

            $.ajax({
                type: 'POST',
                url: '{$save_page_state_url}',
                data: JSON.stringify(pageState),
                contentType: 'application/json; charset=utf-8',
                success:  function(data){ },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    console.error(errorThrown);
                },
                complete: function(){
                    currentSaveTimerID = null;
                }
            });
        }
        function initDataTable() {
            table = $("#cl-table").DataTable({
                initComplete: function () {
                    $("#cl-table").css('width', '100%').show();
                    $("#cl-table-ph").hide();
                },
                columnDefs: [
                    {
                        // TODO why is this defaulted to be non-searchable
                        "searchable": false,
                        "orderable": false,
                        targets: {count($filter_data["display_fields"])}
                    },
                    {foreach from=$config["columnDefs"] item=def_info}
                    {
                        {if !$def_info["searchable"]}
                        "searchable": false,
                        {/if}
                        {if !$def_info["orderable"]}
                        "orderable": false,
                        {/if}
                        "targets": {$def_info["targets"]}
                    },
                    {/foreach}

                ],
                order: {json_encode($call_list_field_sorting)},
                lengthMenu: {json_encode($config['show_entries_options'])},
                iDisplayLength: {$config["show_entries_number"]}

            });
        }

        function restorePageState(){
            var selections = pageState["call_list_selections"];
            let checkboxes = $('input:checkbox');
            let dropdown = $('#ddlfilter');

            var ddlfilterSelectedOption = selections['dropdownSelections']['ddlfilter'];
            if(typeof ddlfilterSelectedOption !== 'undefined' && ddlfilterSelectedOption !== "") {
                dropdown.val(ddlfilterSelectedOption);
            }

            checkboxes.each(function(idx, el){
                var $this = $(el);
                var toBeSelected = selections['checkboxSelections'][$this.val()];

                if(toBeSelected === true) {
                    $this.prop("checked", true);
                }
            });
        }

        $('.cl-tooltip').popover({
            container: 'body',
            html: true,
            trigger: 'hover'
        });
        initDataTable();

        attachHandlers();
        restorePageState();
    });
</script>