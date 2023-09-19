# Guest Entries
Shield can help you protect against Spam in your guest entries.

1. Follow the [Guest Entries](https://github.com/craftcms/guest-entries) setup guide, if you haven’t already
2. Make sure [Guest Entries](https://github.com/craftcms/guest-entries) support is enabled in your [Shield Config](https://selvinortiz.com/plugins/shield/installation#configure)
3. Add the `hidden input` fields to your form so **Shield** knows what to validate

To setup Shield to protect your guest entries, the following hidden fields must be defined.

```html
<input type="hidden" name="shield[emailField]" value="{guestEntryEmailFieldHandle}">
<input type="hidden" name="shield[authorField]" value="{guestEntryFullNameFieldHandle}">
<input type="hidden" name="shield[contentField]" value="{guestEntryBodyFieldHandle}">
```

These fields need to be defined so that **Shield** knows what attributes to look for in the **guest entry** in order to prepare the data to pass along to Akismet for validation.

When the form is submitted and the **Guest Entry** is validated, the entry will be handed to **Shield** which will then grab the `shield[emailField|authorField|contentField]` values containing **twig** placeholders which will then be **replaced** by attribute values found in the [EntryModel](http://buildwithcraft.com/docs/templating/entrymodel)

_Note that the `emailField` and `authorField` are not required._
