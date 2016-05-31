<?php

return [
  'user' => [
    'get_one' => 'SELECT user_id as id, username, email, password_hash FROM `as_users` WHERE user_id = :id',
    'get_by_username' => 'SELECT user_id as id, username, email, password_hash FROM `as_users` WHERE username = :username',
    'get_by_session_token' => 'SELECT user_id as id, username, email, password_hash FROM `as_users` WHERE session_token = :session_token',
    'set_session_token' => 'UPDATE `as_users` SET session_token = :session_token WHERE user_id = :id',
    'register' => 'INSERT INTO `as_users` SET username = :username, email = :email, password_hash = :password_hash',
    'change_password' => 'INSERT INTO `as_users` SET username = :username, password_hash = :password_hash',
  ],
  'category' => [
    'list' => 'SELECT category_id as id, category as name FROM `as_categories` ORDER BY category_id',
  ],
  'asset' => [// TODO: use users instead of authors
    'search' => 'SELECT asset_id, title, username as author, user_id as author_id, category_id, rating, cost, icon_url FROM `as_assets`
      LEFT JOIN `as_users` USING (user_id)

      WHERE accepted<>"UNACCEPTED" AND category_id LIKE :category
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
        END
      END ASC,
      CASE
        WHEN :order_direction = "desc" THEN
        CASE
          WHEN :order = "rating" THEN rating
          WHEN :order = "cost" THEN cost
          WHEN :order = "title" THEN title
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

    'get_one' => 'SELECT asset_id, title, username as author, user_id as author_id, version, version_string, category, category_id, rating, cost, description, download_url, browse_url, icon_url, preview_id, type, link, thumbnail, accepted FROM `as_assets`
      LEFT JOIN `as_categories` USING (category_id)
      LEFT JOIN `as_users` USING (user_id)
      LEFT JOIN `as_asset_previews` USING (asset_id)
      WHERE asset_id = :id',

    'submit' => 'INSERT INTO `as_assets`
      SET user_id=:user_id, title=:title, description=:description, category_id=:category_id, cost=:cost,
        version=0, version_string=:version_string, download_url=:download_url, browse_url=:browse_url, icon_url=:icon_url,
        rating=0, accepted="UNACCEPTED"',

    'update_details' => 'UPDATE `as_assets`
      SET title=:title, category_id=:category_id, cost=:cost, description=:description, icon_url=:icon_url
      WHERE asset_id=:asset_id AND user_id=:user_id',

    'update_version' => 'UPDATE `as_assets`
      SET version=version+1, version_string=:version_string, download_url=:download_url, browse_url=:browse_url,
        accepted=IF(accepted="UNACCEPTED", accepted, "UPDATED")
      WHERE asset_id=:asset_id AND user_id=:user_id',
  ],
];
