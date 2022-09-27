## 1.3.1
- Fixed usage of "raw" vs "value" data source in handling filter logic when detecting of "No Result" is selected. Was a problem when "Contact Result" isn't selected in the module config (REDCap front-end), and would return invalid results even if you selected it.
## 1.3.0
- Upgraded Smarty library
- Added configuration option to prevent empty search, for performance reasons in large projects
## 1.2.1
- Added support for searching for records with NO contact (currently, selecting no filters returns everything)
- Prevent page re-submit when back/refresh used, to prevent re-searching -- for performance reasons
## 1.2.0
- Added support for searching to be delayed, as to not search upon page load -- for performance reasons.
- Changed submission to be a self-submitting form paradigm
- Added page state handling so selected filters are remembered when searching
## 1.1.2 (2020-06-17)
- Add speed
## 1.1.1 (2020-02-06)
- Added ability to configure the number of rows per page that display in the results table (default: 10)
- Fixed a bug that would prevent the data table from loading properly if a custom filter field was not specified.
## 1.1.0 (2020-01-14)
- Added support for display column sorting
- Fixed some bugs related to use of certain field types
- Fixed some bugs related to pulling latest values from repeating events
- Performance improvement by reducing dataset to minimum fields necessary
- Updated README
## 1.0.0
- Initial release