# DH Maintenance

A simple WordPress maintenance mode plugin. Activating the plugin does **not** put your site into maintenance — you control that with a toggle in the settings.

---

## Features

- **Toggle switch** to enable or disable maintenance mode independently of plugin activation
- **Logo upload** via the WordPress media library
- **Title** and **rich-text message** fully editable from the settings
- **Background and text colour** pickers
- **Horizontally and vertically centred** layout on the maintenance page
- **Admins bypass** the maintenance page automatically while logged in
- **Live preview** iframe in the settings so you can see your changes before going live
- SEO-safe `503 Service Unavailable` + `Retry-After: 3600` HTTP response

---

## Installation

1. Download or clone this repository into your `wp-content/plugins/` directory.
2. In the WordPress admin go to **Plugins** and activate **DH Maintenance**.
3. Go to **Settings → DH Maintenance** to configure the plugin.

---

## Usage

### Enabling maintenance mode

1. Go to **Settings → DH Maintenance**.
2. Flip the **Maintenance Mode** toggle to the on position.
3. Click **Save Settings**.

Visitors will now see the maintenance page. Logged-in administrators always see the live site.

### Disabling maintenance mode

1. Go to **Settings → DH Maintenance**.
2. Flip the toggle off.
3. Click **Save Settings**.

### Customising the maintenance page

All of the following can be set from **Settings → DH Maintenance**:

| Setting | Description |
|---|---|
| **Bypass Roles** | Roles that can see the live site while maintenance mode is active. Administrators always bypass and cannot be unchecked. |
| **Logo** | Upload an image via the media library (PNG or SVG recommended) |
| **Title** | The main heading shown on the maintenance page |
| **Message** | Rich-text content shown below the title |
| **Background Color** | Page background colour |
| **Text Color** | Colour applied to the title, message and links |

A **Live Preview** iframe at the bottom of the settings page reflects your saved settings in real time.

---

## File Structure

```
dh-maintenance/
├── dh-maintenance.php        Main plugin file
├── templates/
│   └── maintenance.php       Maintenance page template
└── assets/
    ├── css/
    │   └── admin.css         Admin settings styles
    └── js/
        └── admin.js          Media uploader & preview logic
```

---

## Requirements

- WordPress 5.5 or higher
- PHP 7.4 or higher

---

## License

[GPL-2.0-or-later](https://www.gnu.org/licenses/gpl-2.0.html)
