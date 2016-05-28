<?php

return [
  'user' => [
    'get_one' => 'SELECT user_id as id, username, email, password_hash FROM `as_users` WHERE user_id = :id',
    'get_by_username' => 'SELECT user_id as id, username, email, password_hash FROM `as_users` WHERE username = :username',
    'register' => 'INSERT INTO `as_users` SET username = :username, email = :email, password_hash = :password_hash',
    'change_password' => 'INSERT INTO `as_users` SET username = :username, password_hash = :password_hash',
  ],
  'category' => [
    'list' => 'SELECT category_id as id, category as name FROM `as_categories` ORDER BY category_id',
  ],
  'asset' => [// TODO: use users instead of authors
    'search' => 'SELECT asset_id, title, author, author_id, category_id, rating, cost, icon_url FROM `as_assets`
      LEFT JOIN `as_authors` USING (author_id)

      WHERE category_id LIKE :category
      AND (
        title LIKE :filter
        OR cost LIKE :filter
        OR author LIKE :filter
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
      LEFT JOIN `as_authors` USING (author_id)
      WHERE category_id LIKE :category
      AND (
        title LIKE :filter
        OR cost LIKE :filter
        OR author LIKE :filter
      )',

    'get_one' => 'SELECT asset_id, title, author, author_id, version, version_string, category, category_id, rating, cost, description, download_url, browse_url, icon_url, preview_id, type, link, thumbnail FROM `as_assets`
      LEFT JOIN `as_categories` USING (category_id)
      LEFT JOIN `as_authors` USING (author_id)
      LEFT JOIN `as_asset_previews` USING (asset_id)
      WHERE asset_id = :id',
  ],
];
