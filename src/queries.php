<?php

return [
  'user' => [
    'get_one' => 'SELECT user_id as id, username, email, password_hash, type FROM `as_users` WHERE user_id = :id',
    'get_by_username' => 'SELECT user_id as id, username, email, password_hash, type FROM `as_users` WHERE username = :username',
    'get_by_session_token' => 'SELECT user_id as id, username, email, password_hash, type FROM `as_users` WHERE session_token = :session_token',
    'set_session_token' => 'UPDATE `as_users` SET session_token = :session_token WHERE user_id = :id',
    'register' => 'INSERT INTO `as_users` SET username = :username, email = :email, password_hash = :password_hash',
    'change_password' => 'INSERT INTO `as_users` SET username = :username, password_hash = :password_hash',
  ],
  'category' => [
    'list' => 'SELECT category_id as id, category as name FROM `as_categories` ORDER BY category_id',
  ],
  'asset' => [
    'search' => 'SELECT asset_id, title, username as author, user_id as author_id, category_id, rating, cost, icon_url, version, version_string FROM `as_assets`
      LEFT JOIN `as_users` USING (user_id)

      WHERE searchable = TRUE AND category_id LIKE :category
      AND (
        title LIKE :filter
        OR cost LIKE :filter
        OR username LIKE :filter
      )

      ORDER BY
      CASE
        WHEN :order_direction = "asc" THEN
        CASE
          WHEN :order = "rating" THEN rating
          WHEN :order = "cost" THEN cost
          WHEN :order = "title" THEN title
          WHEN :order = "modify_date" THEN modify_date
        END
      END ASC,
      CASE
        WHEN :order_direction = "desc" THEN
        CASE
          WHEN :order = "rating" THEN rating
          WHEN :order = "cost" THEN cost
          WHEN :order = "title" THEN title
          WHEN :order = "modify_date" THEN modify_date
        END
      END DESC

      LIMIT :page_size OFFSET :skip_count',

    'search_count' => 'SELECT count(*) as count FROM `as_assets`
      LEFT JOIN `as_users` USING (user_id)
      WHERE category_id LIKE :category
      AND (
        title LIKE :filter
        OR cost LIKE :filter
        OR username LIKE :filter
      )',

    'get_one' => 'SELECT asset_id, title, username as author, user_id as author_id, version, version_string, category, category_id, rating, cost, description, download_url, browse_url, icon_url, preview_id, `as_asset_previews`.type, link, thumbnail, searchable FROM `as_assets`
      LEFT JOIN `as_categories` USING (category_id)
      LEFT JOIN `as_users` USING (user_id)
      LEFT JOIN `as_asset_previews` USING (asset_id)
      WHERE asset_id = :id',

    'get_one_bare' => 'SELECT * FROM `as_assets` WHERE asset_id = :asset_id',

    'apply_creational_edit' => 'INSERT INTO `as_assets`
      SET title=:title, description=:description, category_id=:category_id, user_id=:user_id,
      version_string=:version_string, cost=:cost,
      download_url=:download_url, browse_url=:browse_url, icon_url=:icon_url,
      version=0+:update_version, rating=0',

    'apply_edit' => 'UPDATE `as_assets`
      SET title=COALESCE(:title, title), description=COALESCE(:description, description), category_id=COALESCE(:category_id, category_id),  version_string=COALESCE(:version_string, version_string), cost=COALESCE(:cost, cost),
      download_url=COALESCE(:download_url, download_url), browse_url=COALESCE(:browse_url, browse_url), icon_url=COALESCE(:icon_url, icon_url),
      version=version+:update_version
      WHERE asset_id=:asset_id',
  ],
  'asset_edit' => [
    'get_one' => 'SELECT * FROM `as_asset_edits` WHERE edit_id=:edit_id',
    'get_one_with_status' => 'SELECT * FROM `as_asset_edits` WHERE edit_id=:edit_id AND status=:status',
    'get_editable_by_asset_id' => 'SELECT * FROM `as_asset_edits` WHERE asset_id=:asset_id AND status=0',

    'submit' => 'INSERT INTO `as_asset_edits`
      SET asset_id=:asset_id, user_id=:user_id, title=:title, description=:description, category_id=:category_id, version_string=:version_string,
        cost=:cost, download_url=:download_url, browse_url=:browse_url, icon_url=:icon_url,
        status=0',

    'update' => 'UPDATE `as_asset_edits`
      SET title=COALESCE(:title, title), description=COALESCE(:description, description), category_id=COALESCE(:category_id, category_id), version_string=COALESCE(:version_string, version_string), cost=COALESCE(:cost, cost),
      download_url=COALESCE(:download_url, download_url), browse_url=COALESCE(:browse_url, browse_url), icon_url=COALESCE(:icon_url, icon_url)
      WHERE edit_id=:edit_id AND status=0',

    'set_asset_id' => 'UPDATE `as_asset_edits` SET asset_id=:asset_id WHERE edit_id=:edit_id',
    'set_status_and_reason' => 'UPDATE `as_asset_edits` SET status=:status, reason=:reason WHERE edit_id=:edit_id',
  ],
];
