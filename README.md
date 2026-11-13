# Green Leaf Café

A small café website built with WordPress and the free Astra theme. Pages: Home, About, Menu, Contact. No page builder and no third-party plugins.

## What is in this repo
Only my own work is stored here, not the full WordPress install.

| Folder | Content |
| --- | --- |
| `plugin/green-leaf-cafe-setup/` | Custom plugin: creates the pages, menu, homepage setting and styling, adds a contact form with a Messages inbox, and a settings page for address, phone, email and opening hours |
| `plugin/.../css/green-leaf-cafe.css` | Custom green and cream styling |
| `assets/` | Menu photos (800x800) |
| `docs/screenshots/` | Screenshots of the finished site |

## Tech
WordPress, Astra (free), PHP (custom plugin), CSS, WAMP (local server).

## Features
- Responsive layout (desktop and mobile)
- Hero section, customer favourites, opening hours
- Menu page with 6 items
- Contact page with your details and a contact form (nonce, spam trap, sanitised input); messages are stored in the admin area
- Settings > Green Leaf Cafe: address, phone, email and opening hours (empty fields are hidden, no made-up details)
- Line icons, equal-height menu cards with price tags

## How to run it locally
1. Install WordPress with WAMP in `C:\wamp64\www\cafe` (database `cafe_db`).
2. Install the Astra theme and activate it.
3. Zip the folder `plugin/green-leaf-cafe-setup`, then Plugins > Add New > Upload Plugin, and activate it.
4. Settings > Permalinks > Post name > Save.
5. Settings > Green Leaf Cafe: enter the address, phone, email and opening hours.

## What I learned
Building a small WordPress site, writing a plugin, and using Git and GitHub safely (secrets such as `wp-config.php` are never committed).
