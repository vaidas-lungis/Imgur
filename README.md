Imgur is a BoxBilling extension for support ticket image attachment upload to Imgur service

## Install options:
* Clone this repository to your Boxbilling bb-modules folder (`src/bb-modules/`) `git clone https://github.com/vaidas-lungis/Imgur.git`.
* Create a submodule. `git submodule add https://github.com/vaidas-lungis/Imgur.git src/bb-modules/Imgur`.
* Use `Download Zip` button in right sidebar and extract files in your Boxbilling bb-modules folder (`src/bb-modules/`).
* Download from [release page](https://github.com/vaidas-lungis/Imgur/releases) and extract files in your Boxbilling bb-modules folder (`src/bb-modules/`).

## Enable extension
* Open Extensions page in admin area (Left sidebar: `Extensions->Overview`).
* Locate Imgur extension and click `Activate`.
* You will be redirected to Imgur extension setting page.

## Using extension
* You need to register application and get Client ID from [Imgur developer page](https://api.imgur.com/oauth2/addclient)
* Edit admin area and clients support ticket templates (mod_support_ticket.phtml) to include attachment upload form template
```
{% include 'mod_imgur_upload.phtml with {'support_ticket_id' : ticket.id}'%}
```

## Tests
Imgur module tests work only when it is placed in Boxbilling bb-modules folder.