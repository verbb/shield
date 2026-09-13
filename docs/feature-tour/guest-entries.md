# Guest Entries

Shield checks Guest Entries submissions with Akismet before the entry is saved. Configure your Akismet credentials and enable `enableGuestEntriesSupport` in [Configuration](docs:get-started/configuration), then configure the fields Shield should read from the submitted entry.

## Map the Submitted Fields

Add hidden inputs to your existing Guest Entries form. For example, if the entry has fields with handles `email`, `fullName` and `message`, use:

```html
<input type="hidden" name="shield[emailHandle]" value="{email}">
<input type="hidden" name="shield[authorHandle]" value="{fullName}">
<input type="hidden" name="shield[contentHandle]" value="{message}">
```

Replace the values in braces with the actual field handles on your entry. These are object-template expressions evaluated against the submitted entry; they tell Shield where to obtain the email address, author name and message content. They do not replace the visible form inputs that collect those values.

## Check the Submission

Submit the form with an address and message you control. Confirm that a legitimate entry follows your normal Guest Entries workflow. When Shield identifies spam, it marks the Guest Entries event as spam so that the integration can handle that result.

If protection does not appear to run, confirm that Guest Entries is installed and enabled, that its Shield integration is enabled, and that the hidden input names match this example. Check that the mapped fields exist on the submitted entry and inspect Shield's logging when enabled. A missing or invalid mapping is a configuration problem, not evidence that the submission is safe.
