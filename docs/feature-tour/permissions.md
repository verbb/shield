# Permissions

Shield's permissions decide who can review and tidy up its submission logs, which record the name, email and content of each form submission Shield checks when `logSubmissions` is on, with spam flagged in red. Someone checking why a message never arrived might only need to read them, while the person looking after the site can clear them out. Set them in **Settings → Users → User Groups**, or on an individual user's **Permissions** tab.

**Access Shield** is listed under **General**, with Craft's other permissions for reaching each plugin's section. **Manage submission logs** is listed under the **Shield** heading.

| Permission | What It Allows |
| --- | --- |
| Access Shield | Open Shield's section of the control panel and read the submission logs. |
| Manage submission logs | Delete individual log entries, and clear every log at once. |

**Manage submission logs** works on the logs screen, so anyone given it needs **Access Shield** too. Admins have both, and Shield's settings are only available to admins.

The logs hold names, email addresses and message content from people who filled in your forms, so give **Access Shield** only to people who should see that information. Clearing the logs can't be undone, which makes **Manage submission logs** one to keep for whoever is responsible for that data. See [Configuration](docs:get-started/configuration) to turn logging on or off.
