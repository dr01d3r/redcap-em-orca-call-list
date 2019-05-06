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
  - 8.11.7
- PHP
  - This module has been tested against all major versions of PHP that are supported by REDCap >= 8.0.0

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
- Additional filters for call list:
  - Allows to filter the whole data table by one variable.  
- Select the fields to display:
  - Select the variables from your project to display in the table.  1. Is the most left hand column.  
- Display logged in users name within field
  - `user_fullname` will pull in the logged in users full name

## Considerations

- There are two hard coded values
  - `contact_result` shows the colors on the table 
  - `call_date` is uses to display the call attempts
- Repeating instruments and events will show the information from the latest form.  
- Projects with significant record counts will increase load times and may not render (10,000 + record counts)
- If you identify any issues, please submit an issue on this GitHub repo or make a post on the forums and tag (@chris.kadolph or @leila.deering)
- Your project should be created first then enable the module.  