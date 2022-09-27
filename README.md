# ORCA Call List (REDCap External Module)

### Purpose

The purpose of this module is to have a sortable data table to show patient status along the recruitment process.   

### Disclaimer

This module does not yet support arms

## Features
  
- Customizable data table
- Customizable filtering

## Testing & Validation

- REDCap
  - v12.5.7
- PHP
  - v7.4.21
  
> **NOTE:** These are the most recent versions tested.  Previous versions should still be supported unless stated otherwise.

## Permissions

- redcap_data_entry_form

## Options

- Color coded list 
  - Variable name `contact_result` is hardcoded to make the colors appear.  There are 6 possible colors based on this coding values
    - 1, (Green)
    - 2, (Blue)
    - 3, (Red)
    - 4, (Purple)
    - 5, (Yellow)
    - 6, (Orange)
  - You can add more options however, the colors will not appear for additional values.  
  - You can add any value you want after the comma to utilized the comma.  For example, if you like 1, Appointment scheduled all the values of Appointment Scheduled will be green.  
- Viewable contact attempts (Display the contact attempts by time of day)
  - To get the contact attempts to show you must use variable name `call_date` and this variable must be on a repeating form.
- Display the title of the table:  
  - Name the table that fits your project
- Specify the number of results per page to display in the table
  - 10 (default), 25, 50, 100, 150, 200, 500
- Additional filters for call list:
  - Allows to filter the whole data table by one variable.  
- Select the fields to display
  - Select the variables from your project to display in the table.
  - Columns display in the order they are configured
- Custom piping option: `[user_fullname]`
  - **NOTE:** v9.2.0 Standard implemented a new `[user-fullname]` Smart Variable
    - If possible, this should be used in favor of our custom piping option 
  - When on a Data Entry Form, display logged in user's full name
  - Does not work on Surveys/Reports/etc.
- Display field sorting
  - To flag a display field for sorting, check the checkbox.
  - Specify a sort direction of Ascending or Descending
  - Specify a sort priority (order columns are sorted)
    - Must be numeric and greater than 0
    - Non-unique priority numbers will sort based on order in the config
  - An alert will be displayed if sort configuration is incorrect
- Callback Date/Time Exceeded: `[call_back_date_time]`
  - If this hard-coded field name is on the display field list, it will display an alert icon, indicating that date is in the past (past due).
  - Field must use a date or date/time validation.
## Considerations

- There are two hard coded values
  - `contact_result` shows the colors on the table
  - `call_date` is used to display the call attempts
- Repeating instruments and events will show the information from the latest form.  
- Projects with significant record counts will increase load times and may not render (10,000 + record counts)
- If you identify any issues, please submit an issue on this GitHub repo or make a post on the forums and tag (@chris.kadolph or @leila.deering)
- Your project should be created first then enable the module.  