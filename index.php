<?php

/** @var \ORCA\OrcaCallList\OrcaCallList $module */
require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';

$module->initializeSmarty();
$module->addTime('init');

$config = [
    "user_dag" => null,
    "exportDataAccessGroups" => false,
    "has_repeating_forms" => $Proj->hasRepeatingForms(),
    "display_title" => $module->getProjectSetting("display_title"),
    "show_entries_number" => $module->getProjectSetting("display_entries_number"),
    "contact_attempts" => [
        "display" => $module->getProjectSetting("display_contact_attempts"),
        "field_name" => "call_date",
        "ranges" => [
            "AM" => [ "begin" => "00:00:00", "end" => "12:00:00", "label" => "00:00 - 11:59" ],
            "PM" => [ "begin" => "12:00:00", "end" => "18:00:00", "label" => "12:00 - 17:59" ],
            "EVE" => [ "begin" => "18:00:00", "end" => "24:00:00", "label" => "18:00 - 23:59" ]
        ]
    ],
    "display_fields" => [],
];

$data_fields = [
    "call_date",
    "contact_result"
];

$metadata = [
    "fields" => [],
    "forms" => [],
    "form_statuses" => [
        0 => "Incomplete",
        1 => "Unverified",
        2 => "Complete"
    ],
    "date_field_formats" => [
        "date_mdy" => "m/d/Y",
        "datetime_mdy" => "m/d/Y G:i"
    ],
    "unstructured_field_types" => [
        "text",
        "textarea"
    ],
    "custom_dictionary_values" => [
        "yesno" => [
            "1" => "Yes",
            "0" => "No"
        ],
        "truefalse" => [
            "1" => "True",
            "0" => "False"
        ]
    ]
];

//used to store sorting info for columns based on
// $config["display_fields"][display_field_sort_direction / display_field_sort_priority / display_field_sort_on_field]
$fieldSortingInfo = [];

if (empty($config["display_title"])) {
    $config["display_title"] = "Orca Call List";
}
if (empty($config["show_entries_number"])) {
    $config["show_entries_number"] = 10;
}

// add some handling for DAGs, if the project uses them
if (count($Proj->getGroups()) > 0) {
    $config["groups"] = array_combine($Proj->getUniqueGroupNames(), $Proj->getGroups());
    $user_dag = \REDCap::getUserRights(USERID)[USERID]["group_id"];
    if (!empty($user_dag)) {
        $config["user_dag"] = \REDCAP::getGroupNames(true, $user_dag);
        $config["exportDataAccessGroups"] = true;
//        $config["messages"] = "Displaying records for the '" . $config["groups"][$config["user_dag"]] . "' Data Access Group";
    }
}

// status color mapping
// [1] => Appointment Scheduled
// [2] => Busy
// [3] => Call back with date
// [4] => Call back without date
// [5] => Left Message
// [6] => Will call us

$dd_contact_result = $module->getDictionaryValuesFor("contact_result");
$contact_result_colors = [
    "1" => "cl-green",
    "2" => "cl-blue",
    "3" => "cl-red",
    "4" => "cl-purple",
    "5" => "cl-yellow",
    "6" => "cl-orange"
];
$contact_result_metadata = [];

foreach ($dd_contact_result as $ck => $cv) {
    $contact_color = null;
    if (array_key_exists($ck, $contact_result_colors)) {
        $contact_color = $contact_result_colors[$ck];
    }
    $contact_result_metadata[$ck] = [
        "key" => $ck,
        "label" => $cv,
        "status" => $contact_color
    ];
}

/*
* Build the Form/Field Metadata
* This is necessary for knowing where to find record
* values (i.e. repeating/non-repeating forms)
*/
foreach ($Proj->forms as $form_name => $form_data) {
    $metadata["forms"][$form_name] = [
        "event_info"=>[]
    ];
    foreach ($form_data["fields"] as $field_name => $field_info) {
        $metadata["fields"][$field_name] = [
            "form" => $form_name,
            "form_fields"=>$form_data["fields"]
        ];
    }

}

foreach ($Proj->eventsForms as $event_id => $event_forms) {
    foreach ($event_forms as $form_index => $form_name) {
        $metadata["forms"][$form_name]["event_info"][$event_id]["event_id"] = $event_id;
        $metadata["forms"][$form_name]["event_info"][$event_id]["repeating"] = false;
    }
}

if ($config["has_repeating_forms"]) {
    foreach ($Proj->getRepeatingFormsEvents() as $event_id => $event_forms) {
        if($event_forms == "WHOLE") { // when event is repeating it returns string "WHOLE"
            foreach ($Proj->eventsForms as $ev_id => $ev_forms) {
                foreach ($ev_forms as $form_index => $form_name) {
                    if($ev_id==$event_id) {
                        $metadata["forms"][$form_name]["event_info"][$ev_id]["repeating"] = true;
                    }
                }
            }
        }
        else
        {
            foreach ($event_forms as $form_name => $value) {
                $metadata["forms"][$form_name]["event_info"][$event_id]["repeating"] = true;
            }
        }
    }
}

$filter_field = $module->getProjectSetting("display_filter_fields");
if (!empty($filter_field)) {
    $filter_field_values = [];
    // set structured values for display in search options
    switch ($Proj->metadata[$filter_field]["element_type"]) {
        case "select":
        case "radio":
        case "checkbox":
            $filter_field_values = $module->getDictionaryValuesFor($filter_field);
            break;
        case "yesno":
        case "truefalse":
            $filter_field_values = $metadata["custom_dictionary_values"][$Proj->metadata[$filter_field]["element_type"]];
            break;
        default: break;
    }
    $config["filter_field"] = [
        "field_name" => $filter_field,
        "field_label" => $module->getDictionaryLabelFor($filter_field),
        "field_values" => $filter_field_values,

        "form_name" => $metadata["fields"][$filter_field]["form"],
        "form_events" => [],
    ];
    $config["filter_field"]["form_events"] = array_reverse($metadata["forms"][$config["filter_field"]["form_name"]]["event_info"],true);

    $data_fields[] = $filter_field;
    $data_fields[] = $config["filter_field"]["form_name"] . "_complete";
}

//used to keep track of the zero-based index of each column (because the table displays columns from $config["display_fields"] in the order they appear in here)
$fieldIndex = 0;
foreach ($module->getSubSettings("display_fields") as $display_field) {
    if (empty($display_field["display_field_name"])) continue;

    $field_name = $display_field["display_field_name"];
    $form_name = $metadata["fields"][$field_name]["form"];
    $data_fields[] = $field_name;

    if ($Proj->isFormStatus($field_name)) {
        $config["display_fields"][$field_name] = [
            "display" => true,
            "is_form_status" => true,
            "element_validation_type" => $Proj->metadata[$field_name]["element_validation_type"],
            "label" => $Proj->forms[$form_name]["menu"] . " Status",
            "dictionary_values" => $metadata["form_statuses"]
        ];
    } else {
        $config["display_fields"][$field_name] = [
            "display" => true,
            "element_validation_type" => $Proj->metadata[$field_name]["element_validation_type"],
            "label" => $module->getDictionaryLabelFor($field_name),
            "form_name" => $form_name
        ];
        $data_fields[] = $form_name . "_complete";
    }

    //skip sorting if any of the fields for sorting are empty, to ensure everything is filled out
    $sortOnField = $display_field['display_field_sort_on_field'] === true;
    $emptySortFields = empty($display_field['display_field_sort_direction']) || empty($display_field['display_field_sort_priority']);
    //report incorrect configuration of sorting
    if($sortOnField && $emptySortFields) {
        $config["errors"][] = "All sort values needed for a field \"{$field_name}\" are not configured. Either deselect it for sorting or fill in missing values.";
    }

    //if no fields are empty (the priority has to be 1 or greater) AND sort direction isn't set to "NONE", then we can include this field in sorting
    if($sortOnField && !$emptySortFields && (!$display_field['display_field_sort_direction'] !== "NONE")) {
        //this field should be added to the sorting list
        $fieldSortingInfo[] = ["field_index" => $fieldIndex, "direction" => $display_field['display_field_sort_direction'], "priority" => $display_field['display_field_sort_priority']];
    }

    //increment this after all logic dealing with the field
    $fieldIndex++;
}

//sort the array, by reference, according to priority (lower priorities first, as people order things starting at 1)
usort($fieldSortingInfo, function($a, $b) {
    if($a['priority'] == $b['priority']) {
        return 0;
    }
    return $a['priority'] < $b['priority'] ? -1 : 1;
});
//convert array for DataTables format: [columnIndex, asc/desc]
$fieldSortingInfo = array_map(function($fieldInfo){
    return [$fieldInfo['field_index'], $fieldInfo['direction']];
}, $fieldSortingInfo);


if ($config["contact_attempts"]["display"] === true) {
    $config["display_fields"]["contact_attempts"] = [
        "display" => true,
        "ignore" => true,
        "label" => "Contact Attempts"
    ];
}

if (empty($config["display_fields"])) {
    $config["errors"][] = "Display fields not yet been configured.  Please go to the <b>" . $lang["global_142"] . "</b> area in the project sidebar to configure them.";
}

if ($config["contact_attempts"]["display"] === true) {
    $field_name = $config["contact_attempts"]["field_name"];
    $field_form_name = $metadata["fields"][$field_name]["form"];
    $field_form_event_ids = $metadata["forms"][$field_form_name]["event_info"];
    $repeatable_count=0;
    foreach ($field_form_event_ids as $ev_id => $event_info) {
        if ($field_form_event_ids[$ev_id]["repeating"] == 1) {
            $repeatable_count++;
        }
    }
    if ($repeatable_count < 1) {
        $config["display_fields"]["contact_attempts"]["display"] = false;
        $config["errors"][] = "Unable to aggregate contact attempts ('$field_name') because the '$field_form_name' form is not repeating";
    } else if ($Proj->metadata[$field_name]["element_validation_type"] !== "datetime_mdy") {
        $config["display_fields"]["contact_attempts"]["display"] = false;
        $config["errors"][] = "Unable to aggregate contact attempts because the '$field_name' is not of the right validation type ('datetime_mdy')";
    }
}

$module->addTime('before getData');

$data_fields = array_unique($data_fields);
$data = \REDCap::getData([
    "return_format" => "array",
    "fields" => $data_fields,
    "groups" => $config["user_dag"],
    "exportDataAccessGroups" => $config["exportDataAccessGroups"],
]);

$module->addTime('after getData');

$results = [];

$contact_result_form = $metadata["fields"]["contact_result"]["form"];
$contact_result_form_events = array_reverse($metadata["forms"][$contact_result_form]["event_info"],true);

foreach ($data as $record_id => $record) {
    $dashboard_url = APP_PATH_WEBROOT . "DataEntry/record_home.php?" . http_build_query([
            "pid" => $module->getPid(),
            "id" => $record_id
        ]);
    $record_info = [
        "record_id" => [
            "value" => $record_id
        ],
        "dashboard_url" => $dashboard_url
    ];
    // manually process filter variable, in case it isn't displayed
    if (!empty($config["filter_field"]["field_name"])) {
        foreach ($config["filter_field"]["form_events"] as $ev_id => $event_info) {
            if ($event_info["repeating"] == 1) {
                if (array_key_exists($config["filter_field"]["form_name"], $record["repeat_instances"][$ev_id])) {
                    $filter_result = end($record["repeat_instances"][$ev_id][$config["filter_field"]["form_name"]])[$config["filter_field"]["field_name"]];
                    $record_info[$config["filter_field"]["field_name"]] = [
                        "raw" => $filter_result
                    ];
                    break;
                } else {
                    $latest_data = end($record["repeat_instances"][$ev_id][null]);
                    $common_form_fields = array_intersect_key($latest_data,$metadata["fields"][$field_name]["form_fields"]);
                    $temp_array_filter_array = array_filter($common_form_fields, function($v) {
                        return $v !== null && $v != '';
                    });
                    if (count($temp_array_filter_array) < 2) {
                        continue;
                    } else {
                        $filter_result = end($record["repeat_instances"][$ev_id][null])[$config["filter_field"]["field_name"]];
                        $record_info[$config["filter_field"]["field_name"]] = [
                            "raw" => $filter_result
                        ];
                        break;
                    }
                }
            } else {
                $complete_field = $config["filter_field"]["form_name"] . "_complete";
                if ($record[$ev_id][$complete_field] == '') {
                    continue;
                } else {
                    $filter_result = $record[$ev_id][$config["filter_field"]["field_name"]];
                    if (is_array($filter_result)) {
                        $filter_result = array_filter($filter_result, function($v) {
                            return $v === "1";
                        });
                        $filter_result = implode(",", array_keys($filter_result));
                    }
                    $record_info[$config["filter_field"]["field_name"]] = [
                        "raw" => $filter_result
                    ];
                    break;
                }
            }
        }
    }
    // manually process contact_result, in case it isn't displayed,
    foreach ($contact_result_form_events as $ev_id => $event_info) {
        if ($contact_result_form_events[$ev_id]["repeating"] == 1) {
            if (array_key_exists($contact_result_form,$record["repeat_instances"][$ev_id])) {
                $contact_result = end($record["repeat_instances"][$ev_id][$contact_result_form])["contact_result"];
                $record_info["contact_result"] = [
                    "raw" => $contact_result,
                    "status" => $contact_result_metadata[$contact_result]["status"]
                ];
                break;
            } else {
                $latest_data = end($record["repeat_instances"][$ev_id][null]);
                $common_form_fields = array_intersect_key($latest_data,$metadata["fields"][$field_name]["form_fields"]);
                $temp_array_filter_array = array_filter($common_form_fields, function($v) {
                    return $v !== null && $v != '';
                });
                if (count($temp_array_filter_array) < 2) {
                    continue;
                } else {
                    $contact_result = end($record["repeat_instances"][$ev_id][null])["contact_result"];
                    $record_info["contact_result"] = [
                        "raw" => $contact_result,
                        "status" => $contact_result_metadata[$contact_result]["status"]
                    ];
                    break;
                }
            }
        } else {
            $complete_field = $contact_result_form . "_complete";
            if($record[$ev_id][$complete_field] == '') {
                continue;
            } else {
                $contact_result = $record[$ev_id]["contact_result"];
                $record_info["contact_result"] = [
                    "raw" => $contact_result,
                    "status" => $contact_result_metadata[$contact_result]["status"]
                ];
                break;
            }
        }
    }

    foreach ($config["display_fields"] as $field_name => $field_info) {

        // do not attempt to process fields marked as 'ignore'
        // these fields are handled outside this loop
        if ($field_info["ignore"] === true) continue;

        // prep some form info
        $field_form_name = $metadata["fields"][$field_name]["form"];

        $field_form_event_id = array_reverse($metadata["forms"][$field_form_name]["event_info"],true);

        // initialize some helper variables/arrays
        $field_type = $Proj->metadata[$field_name]["element_type"];

        $element_validation_type = $field_info["element_validation_type"];
        $field_value = null;
        $form_values = [];

        foreach ($field_form_event_id as $ev_id => $event_info) {
            if ($field_form_event_id[$ev_id]["repeating"] == 1) {
                if (array_key_exists($field_form_name,$record["repeat_instances"][$ev_id])) {
                    $form_values = end($record["repeat_instances"][$ev_id][$field_form_name]);
                    break;
                } else {
                    $latest_data = end($record["repeat_instances"][$ev_id][null]);
                    $common_form_fields = array_intersect_key($latest_data,$metadata["fields"][$field_name]["form_fields"]);
                    $temp_array_filter_array = array_filter($common_form_fields, function($v) {
                        return $v !== null && $v != '';
                    });
                    if (count($temp_array_filter_array) < 2) {
                        continue;
                    } else {
                        $form_values = end($record["repeat_instances"][$ev_id][null]);
                        break;
                    }
                }
            } else {
                $complete_field = $field_form_name . "_complete";
                if ($record[$ev_id][$complete_field] == '') {
                    continue;
                } else {
                    $form_values = $record[$ev_id];
                    break;
                }
            }
        }
        // set the raw value of the field
        $field_value = $form_values[$field_name];

        // further process the field if it is anything other than a free-text field
        if ($field_info["is_form_status"] === true) {
            // special value handling for form statuses
            // REDCap::getData() defaults this value to 0 (Incomplete)
            $field_value = $metadata["form_statuses"][$field_value];
        } else if (!in_array($field_type, $metadata["unstructured_field_types"])) {
            switch ($field_type) {
                case "select":
                case "radio":
                    $field_value = $module->getDictionaryValuesFor($field_name)[$field_value];
                    break;
                case "checkbox":
                    $temp_field_array = [];
                    $field_value_dd = $module->getDictionaryValuesFor($field_name);
                    foreach ($field_value as $field_value_key => $field_value_value) {
                        if ($field_value_value === "1") {
                            $temp_field_array[$field_value_key] = $field_value_dd[$field_value_key];
                        }
                    }
                    $field_value = $temp_field_array;
                    break;
                case "yesno":
                case "truefalse":
                    $field_value = $metadata["custom_dictionary_values"][$Proj->metadata[$field_name]["element_type"]][$field_value];
                    break;
                default: break;
            }
        }

        // call_back_date_time exceeded warning
        if ($field_name === "call_back_date_time" && !empty($field_value)) {
            if (strtotime("now") >= strtotime($field_value)) {
                $record_info[$field_name]["alert"] = true;
            }
        }
        // update field value if this is a known date format
        if (array_key_exists($element_validation_type, $metadata["date_field_formats"]) && !empty($field_value)) {
            $record_info[$field_name]["__SORT__"] = strtotime($field_value);
            $field_value = date_format(date_create($field_value),$metadata["date_field_formats"][$element_validation_type]);
        }
        $record_info[$field_name]["value"] = $field_value;
    }

    // display aggregate contact attempts if setting is checked
    if ($config["contact_attempts"]["display"] === true) {
        $field_name = $config["contact_attempts"]["field_name"];
        $field_form_name = $metadata["fields"][$field_name]["form"];
        $field_form_event_id = array_reverse($metadata["forms"][$field_form_name]["event_info"], true);
        // initialize each group to 0
        $contact_attempts = array_fill_keys(array_keys($config["contact_attempts"]["ranges"]), 0);
        foreach ($field_form_event_id as $ev_id => $event_info) {
            if ($field_form_event_id[$ev_id]["repeating"] == 1) {
                foreach ($record["repeat_instances"][$ev_id][$field_form_name] as $instance => $form_info) {
                    if (empty($form_info[$field_name])) continue;

                    $contact_attempt = strtotime($form_info[$field_name]);
                    foreach ($config["contact_attempts"]["ranges"] as $range_key => $range_info) {
                        if ($contact_attempt >= strtotime($range_info["begin"], $contact_attempt) &&
                            $contact_attempt < strtotime($range_info["end"], $contact_attempt)) {
                            $contact_attempts[$range_key]++;
                            break;
                        }
                    }
                }
            }
        }

        foreach ($contact_attempts as $attempt => $count) {
            $contact_attempts[$attempt] = "$attempt ($count)";
        }
        $record_info["contact_attempts"]["value"] = $contact_attempts;
    }
    // add record data to the full dataset
    $results[$record_id] = $record_info;
}

// assign to local variable so it can be piped into a string
$username = USERID;
$parameter_fields = $module->getDictionaryValuesFor("contact_result");
$parameter_fields["ddlfilter"] = "";
// get all the logs for this user.  in this case, there should only be 1
$module_logs= $module->getLogs(array_keys($parameter_fields), "username = '$username'");
$call_list_selections = [];
foreach ($module_logs as $k=> $v) {
    foreach ($v["parameters"] as $key => $val) {
        $call_list_selections[$key] = $val;
    }
}

$module->addTime("post processing");
$module->setTemplateVariable("config", $config);
$module->setTemplateVariable("data", $results);
$module->setTemplateVariable("contact_result_metadata", $contact_result_metadata);
$module->setTemplateVariable("ajax_url",$module->getUrl("save_ajax.php",false,true));
$module->setTemplateVariable("call_list_selections", json_encode($call_list_selections));
//A variable used to inject into DataTables to set the default sorting for the table based on configured fields
$module->setTemplateVariable("call_list_field_sorting", json_encode($fieldSortingInfo));

echo "<link rel='stylesheet' type='text/css' href='" . $module->getUrl("css/call_list.css") . "' />";

$module->displayTemplate("orca_call_list.tpl");

$module->addTime("done");
$module->outputTimerInfo();
require_once APP_PATH_DOCROOT . "ProjectGeneral/footer.php";