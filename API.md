# Asset library API

All POST APIs accept JSON-encoded or formdata-encoded bodies.
GET APIs accept standard query strings.

## Core

The core part of the API is understood and used by the C++ frontend embedded in the Godot editor. It has to stay compatible with all versions of Godot.

* [`GET /configure`](#api-get-configure) - get details such as category and login URL
* [`GET /asset?…`](#api-get-asset) - list assets by filter
* [`GET /asset/{id}`](#api-get-asset-id) - get asset by id

## Auth API

<div id="api-post-register"></div>

### `POST /register`
```json
{
  "username": "(username)",
  "password": "(password)",
  "email": "(email)"
}
```
Successful result:
```json
{
  "username": "(username)",
  "registered": true
}
```


Register a user, given a username, password, and email.

<div id="api-post-login"></div>

### `POST /login`
```json
{
  "username": "(username)",
  "password": "(password)"
}
```
Successful result:
```json
{
  "authenticated": true,
  "username": "(username)",
  "token": "(token)"
}
```

Login as a given user. Results in a token which can be used for authenticated requests.

<div id="api-post-logout"></div>

### `POST /logout`
```json
{
  "token": "(token)"
}
```
Successful result:
```json
{
  "authenticated": false,
  "token": ""
}
```

Logout a user, given a token. The token is invalidated in the process.


<div id="api-post-change-password"></div>

### `POST /change_password`
```json
{
  "token": "(token)",
  "old_password": "(password)",
  "new_password": "(new password)"
}
```
Successful result:
```json
{
  "token": ""
}
```

Change a user's password. The token is invalidated in the process.


<div id="api-get-configure"></div>

### `GET /configure`
```http
?type=(any|addon|project)
&session
```
Example result:
```json
{
  "categories": [
    {
      "id": "1",
      "name": "2D Tools",
      "type": "0"
    },
    {
      "id": "2",
      "name": "Templates",
      "type": "1"
    },
  ],
  "token": "…",
  "login_url": "https://…"
}
```

Get a list of categories (needed for filtering assets) and potentially a login URL which can be given to the user in order to authenticate him in the engine (currently unused and not working).

## Assets API

<div id="api-get-asset"></div>

### `GET /asset?…`
```http
?type=(any|addon|project)
&category=(category id)
&support=(official|featured|community|testing)
&filter=(search text)
&user=(submitter username)
&cost=(license)
&godot_version=(major).(minor).(patch)
&max_results=(number 1…500)
&page=(number, pages to skip) OR &offset=(number, rows to skip)
&sort=(rating|cost|name|updated)
&reverse
```
Example response:
```json
{
  "result": [
    {
      "asset_id": "1",
      "title": "Snake",
      "author": "test",
      "author_id": "1",
      "category": "2D Tools",
      "category_id": "1",
      "godot_version": "2.1",
      "rating": "0",
      "cost": "GPLv3",
      "support_level": "testing",
      "icon_url": "https://….png",
      "version": "1",
      "version_string": "alpha",
      "modify_date": "2018-08-21 15:49:00"
    }
  ],
  "page": 0,
  "pages": 0,
  "page_length": 10,
  "total_items": 1
}
```

Get a list of assets.

Some notes:
* Leading and trailing whitespace in `filter` is trimmed on the server side.
* For legacy purposes, not supplying godot version would list only 2.1 assets, while not supplying type would list only addons.
* To specify multiple support levels, join them with `+`, e.g. `support=featured+community`.
* Godot version can be specified as you see fit, for example, `godot_version=3.1` or `godot_version=3.1.5`. Currently, the patch version is disregarded, but may be honored in the future.

<div id="api-get-asset-id"></div>

### `GET /asset/{id}`
No query params.
Example result:
```json
{
  "asset_id": "1",
  "type": "addon",
  "title": "Snake",
  "author": "test",
  "author_id": "1",
  "version": "1",
  "version_string": "alpha",
  "category": "2D Tools",
  "category_id": "1",
  "godot_version": "2.1",
  "rating": "0",
  "cost": "GPLv3",
  "description": "Lorem ipsum…",
  "support_level": "testing",
  "download_provider": "GitHub",
  "download_commit": "master",
  "download_hash": "(sha256 hash of the downloaded zip)",
  "browse_url": "https://github.com/…",
  "issues_url": "https://github.com/…/issues",
  "icon_url": "https://….png",
  "searchable": "1",
  "modify_date": "2018-08-21 15:49:00",
  "download_url": "https://github.com/…/archive/master.zip",
  "previews": [
    {
      "preview_id": "1",
      "type": "video",
      "link": "https://www.youtube.com/watch?v=…",
      "thumbnail": "https://img.youtube.com/vi/…/default.jpg"
    },
    {
      "preview_id": "2",
      "type": "image",
      "link": "https://….png",
      "thumbnail": "https://….png"
    }
  ]
}
```

Notes:
* The `cost` field is the license. Other asset libraries may put the price there and supply a download URL which requires authentication.
* In the official asset library, the `download_hash` field is always empty and is kept for compatibility only. The editor will skip hash checks if `download_hash` is an empty string. Third-party asset libraries may specify a SHA-256 hash to be used by the editor to verify the download integrity.
* The download URL is generated based on the download commit and the browse URL.

<div id="api-post-asset-id-delete"></div>

### `POST /asset/{id}/delete`
```json
{
  "token": "…"
}
```
Successful response:
```json
{
  "changed": true
}
```

Soft-delete an asset. Useable by moderators and the owner of the asset.

<div id="api-post-asset-id-undelete"></div>

### `POST /asset/{id}/undelete`
```json
{
  "token": "…"
}
```
Successful response:
```json
{
  "changed": true
}
```

Revert a deletion of an asset. Useable by moderators and the owner of the asset.

<div id="api-post-asset-id-support-level"></div>

### `POST /asset/{id}/support_level`
```json
{
  "support_level": "official|featured|community|testing",
  "token": "…"
}
```
Successful response:
```json
{
  "changed": true
}
```

API used by moderators to change the support level of an asset.

## Asset edits API

<div id="api-post-asset-post-asset-id-post-asset-edit-id"></div>

### `POST /asset`, `POST /asset/{id}`, `POST /asset/edit/{id}`
```json
{
  "token": "…",

  "title": "Snake",
  "description": "Lorem ipsum…",
  "category_id": "1",
  "godot_version": "2.1",
  "version_string": "alpha",
  "cost": "GPLv3",
  "download_provider": "GitHub",
  "download_commit": "master",
  "browse_url": "https://github.com/…",
  "issues_url": "https://github.com/…/issues",
  "icon_url": "https://….png",
  "download_url": "https://github.com/…/archive/master.zip",
  "previews": [
    {
      "enabled": true,
      "operation": "insert",
      "type": "image|video",
      "link": "…",
      "thumbnail": "…"
    },
    {
      "enabled": true,
      "operation": "update",
      "edit_preview_id": "…",
      "type": "image|video",
      "link": "…",
      "thumbnail": "…"
    },
    {
      "enabled": true,
      "operation": "delete",
      "edit_preview_id": "…"
    },
  ]
}
```
Successful result:
```json
{
  "id": "(id of the asset edit)"
}
```

Create a new edit or update an existing one. Fields are required when creating a new asset, and are optional otherwise. Same for previews -- required when creating a new preview, may be missing if updating one.

Notes:
* Not passing `"enabled": true` for previews will result in them not being included in the edit. This may be fixed in the future.
* `version_string` is free-form text, but `major.minor` or `major.minor.patch` format is best.
* Available download providers can be seen on the asset library fronted.

<div id="api-get-asset-edit-id"></div>

### `GET /asset/edit/{id}`
No query params.
Example result:
```json
{
  "edit_id": "1",
  "asset_id": "1",
  "user_id": "1",
  "title": null,
  "description": null,
  "category_id": null,
  "godot_version": null,
  "version_string": null,
  "cost": null,
  "download_provider": null,
  "download_commit": null,
  "browse_url": "…",
  "issues_url": "…",
  "icon_url": null,
  "download_url": "…",
  "author": "test",
  "previews": [
    {
      "operation": "insert",
      "edit_preview_id": "60",
      "preview_id": null,
      "type": "image",
      "link": "…",
      "thumbnail": "…",
    },
    {
      "preview_id": "35",
      "type": "image",
      "link": "…",
      "thumbnail": "…"
    }
  ],
  "original": {
    … original asset fields …
  },
  "status": "new",
  "reason": "",
  "warning": "…"
}
```

Returns a previously-submitted asset edit. All fields with `null` are unchanged, and will stay the same as in the `original`.
The `previews` array is merged from the new and original previews.

<div id="api-post-asset-edit-id-review"></div>

### `POST /asset/edit/{id}/review`
```json
{
  "token": "…"
}
```
Successful result: the asset edit, without the original asset.

Moderator-only. Put an edit in review. It is impossible to change it after this.

<div id="api-post-asset-edit-id-accept"></div>

### `POST /asset/edit/{id}/accept`
```json
{
  "token": "…",
}
```
Successful result: the asset edit, without the original asset.

Moderator-only. Apply an edit previously put in review.

<div id="api-post-asset-edit-id-reject"></div>

### `POST /asset/edit/{id}/reject`
```json
{
  "token": "…",
  "reason": "…"
}
```
Successful result: the asset edit, without the original asset.

Moderator-only. Reject an edit previously put in review.
