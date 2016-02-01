# Auto Expire plugin for Craft

This plugin allows to set entry post dates or expiration dates automatically on every entry save computed from predefined Twig code.

## Installation

To install the plugin, copy the autoexpire/ folder into craft/plugins/. Then go to Settings → Plugins and click the "Install" button next to "Auto Expire".

## Usage

- Navigate to the plugin's settings page (Settings → Auto Expire) and add a new rule to define the conditions for how dates are being set by the plugin.
- Select the section and entry type of the entries you want the rule to be applied to.
- Select the date field you want to apply this rule to.
- Enter the Twig code which is computed and set to the date field on entry save. You can include tags that output entry properties.

## Configuration

### Expiration Date

Auto Expire uses the same approach to parse a Twig Template from a field as Craft's Title Format settings field, please see [Dynamic Entry Titles][1] in the Craft Documentation for more information about the expected syntax.

When using tags to output date properties keep in mind that, by default, Twig returns dates in "Y-m-d" format without any time information (which is the same as if you had set it to 12:00 AM).

That's why you probably want to use Twig's `date` filter to explicitly define a format containing the time, i.e. `date('c')`.

**Examples:**

- Set to a fixed date:

        2015-05-13 13:00

- Set an expiry date based on the entry's post date:

        { postDate|date_modify('+7 days')|date('c') }

  or using another [relative time format][2]:

    { postDate|date_modify('first day of next month 5am')|date('c') }

- More complex example using a conditional (ternary syntax):

        {{ object.myDateTimeField ? object.myDateTimeField|date('c') : object.postDate|date_modify('+7 days')|date('c') }}

### Allow User Changes

Check this field to allow users to overwrite the automatically set date. In this case the plugin will only set the date if the date field is left blank by the user.


  [1]: http://buildwithcraft.com/docs/sections-and-entries#dynamic-entry-titles
  [2]: http://php.net/manual/de/datetime.formats.relative.php
