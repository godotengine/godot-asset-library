<?php

return [
  'category' => [
    'list' => 'SELECT * FROM `as_categories` ORDER BY id',
  ],
  'asset' => [
    'search' => 'SELECT * FROM `as_assets` WHERE category_id LIKE :category AND (title LIKE :filter OR cost LIKE :filter OR author LIKE :filter) ORDER BY :order LIMIT :page_size OFFSET :skip_count',
    'get_one' => 'SELECT * FROM `as_assets` LEFT JOIN `as_asset_previews` USING (asset_id) WHERE asset_id = :id',
  ],
];
