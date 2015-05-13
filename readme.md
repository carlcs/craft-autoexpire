# Auto Expire plugin for Craft

Define expiration rules to automatically set entry expiration dates.

## Installation

To install the plugin, copy the autoexpire/ folder into craft/plugins/. Then go to Settings → Plugins and click the "Install" button next to "Auto Expire".

## Usage

New expiration rules can be added from the plugin's settings page (Settings → Plugins → Auto Expire).

Select the section and the entry type of the entries you want the rule to be applied to, and set the expiration date which is set automatically on entry save. You can include tags that output entry properties.

## Settings

### Expiration Date

When using tags to output entry properties keep in mind that, by default, Twig prints dates in "Y-m-d" format without any time information (which is the same as if you had set it to 12:00 AM).

Use Twig's `date` filter to explicitly define a format containing the time, i.e. `date('c')`.

**Examples:**

- Expire entries on a fixed date:

        2015-05-13 13:00

- Expire entries based on the entry's post date:

        { postDate|date_modify('+7 days')|date('c') }

  or with any other [relative time format][1]:

      { postDate|date_modify('first day of next month 5am')|date('c') }

- More complex example using a conditional (ternary syntax):

        {{ object.myDateTimeField ? object.myDateTimeField|date('c') : object.postDate|date_modify('+7 days')|date('c') }}

### Allow User Changes

Check to allow users to overwrite the automatically set expiration date. The plugin sets an expiration date only if the field is left blank.


  [1]: http://php.net/manual/de/datetime.formats.relative.php
