<?php

return [
    'user' => [
        'get_one' => 'SELECT user_id, username, email, password_hash, type FROM `as_users` WHERE user_id = :id',
        'get_by_username' => 'SELECT user_id, username, email, password_hash, type FROM `as_users` WHERE username = :username',
        'get_by_email' => 'SELECT user_id, username, email, password_hash, type FROM `as_users` WHERE email = :email',
        'get_by_session_token' => 'SELECT user_id, username, email, password_hash, type FROM `as_users` WHERE session_token = :session_token',
        'get_by_reset_token' => 'SELECT user_id, username, email, password_hash, type FROM `as_users` WHERE reset_token = :reset_token',
        'set_session_token' => 'UPDATE `as_users` SET session_token = :session_token WHERE user_id = :id',
        'set_reset_token' => 'UPDATE `as_users` SET reset_token = :reset_token WHERE user_id = :id',
        'set_password_and_nullify_session' => 'UPDATE `as_users` SET password_hash = :password_hash, session_token = null WHERE user_id = :id',
        'register' => 'INSERT INTO `as_users` SET username = :username, email = :email, password_hash = :password_hash',
        'promote' => 'UPDATE `as_users` SET type = :type WHERE user_id = :id AND type < :type',
        // 'demote' => 'UPDATE `as_users` SET type = :type WHERE user_id = :id AND type > :type',
        'list_edit_events' => 'SELECT edit_id, asset_id, COALESCE(`as_asset_edits`.title, `as_assets`.title) AS title, `as_asset_edits`.submit_date, `as_asset_edits`.modify_date, category, COALESCE(`as_asset_edits`.version_string, `as_assets`.version_string) AS version_string, COALESCE(`as_asset_edits`.icon_url, `as_assets`.icon_url) AS icon_url, status, reason FROM `as_asset_edits`
            LEFT JOIN `as_assets` USING (asset_id)
            LEFT JOIN `as_categories` ON `as_categories`.category_id = COALESCE(`as_asset_edits`.category_id, `as_assets`.category_id)
            WHERE `as_asset_edits`.user_id = :user_id
            ORDER BY `as_asset_edits`.modify_date DESC
            LIMIT :page_size OFFSET :skip_count',
    ],
    'category' => [
        'list' => 'SELECT category_id as id, category as name, category_type as type FROM `as_categories` WHERE category_type LIKE :category_type ORDER BY category_id',
    ],
    'asset' => [
        'search' => 'SELECT asset_id, title, searchable, username as author, user_id as author_id, category, category_id, godot_version, rating, cost, support_level, icon_url, version, version_string, modify_date FROM `as_assets`
            LEFT JOIN `as_users` USING (user_id)
            LEFT JOIN `as_categories` USING (category_id)

            WHERE (searchable = TRUE OR user_id = :user_id) AND category_id LIKE :category AND category_type LIKE :category_type
            AND support_level RLIKE :support_levels_regex AND username LIKE :username AND cost LIKE :cost
            AND godot_version <= :max_godot_version AND godot_version >= :min_godot_version
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
            LEFT JOIN `as_categories` USING (category_id)
            WHERE (searchable = TRUE OR user_id = :user_id) AND category_id LIKE :category AND category_type LIKE :category_type
            AND support_level RLIKE :support_levels_regex AND username LIKE :username AND cost LIKE :cost
            AND godot_version <= :max_godot_version AND godot_version >= :min_godot_version
            AND (
                title LIKE :filter
                OR cost LIKE :filter
                OR username LIKE :filter
            )',

        'get_one' => 'SELECT asset_id, category_type, title, username as author, user_id as author_id, version, version_string, category, category_id, godot_version, rating, cost, description, support_level, download_provider, download_commit, browse_url, issues_url, icon_url, preview_id, `as_asset_previews`.type, link, thumbnail, searchable, modify_date FROM `as_assets`
            LEFT JOIN `as_categories` USING (category_id)
            LEFT JOIN `as_users` USING (user_id)
            LEFT JOIN `as_asset_previews` USING (asset_id)
            WHERE asset_id = :id',

        'get_one_bare' => 'SELECT * FROM `as_assets` WHERE asset_id = :asset_id',
        'get_one_preview_bare' => 'SELECT * FROM `as_asset_previews` WHERE preview_id = :preview_id',

        'apply_creational_edit' => 'INSERT INTO `as_assets`
            SET user_id=:user_id, title=:title, description=:description, category_id=:category_id, godot_version=:godot_version,
            version_string=:version_string, cost=:cost,
            download_provider=:download_provider, download_commit=:download_commit, browse_url=:browse_url, issues_url=:issues_url, icon_url=:icon_url,
            version=0+:update_version, support_level=:support_level, rating=0, searchable=TRUE',

        'apply_edit' => 'UPDATE `as_assets`
            SET title=COALESCE(:title, title), description=COALESCE(:description, description), category_id=COALESCE(:category_id, category_id),  godot_version=COALESCE(:godot_version, godot_version), version_string=COALESCE(:version_string, version_string), cost=COALESCE(:cost, cost),
            download_provider=COALESCE(:download_provider, download_provider), download_commit=COALESCE(:download_commit, download_commit), browse_url=COALESCE(:browse_url, browse_url), issues_url=COALESCE(:issues_url, issues_url), icon_url=COALESCE(:icon_url, icon_url),
            version=version+:update_version
            WHERE asset_id=:asset_id',

        'apply_preview_edit_insert' => 'INSERT INTO `as_asset_previews`
            SET asset_id=:asset_id, type=:type, link=:link, thumbnail=:thumbnail',
        'apply_preview_edit_remove' => 'DELETE FROM `as_asset_previews`
            WHERE preview_id=:preview_id AND asset_id=:asset_id',
        'apply_preview_edit_update' => 'UPDATE `as_asset_previews`
            SET type=COALESCE(:type, type), link=COALESCE(:link, link), thumbnail=COALESCE(:thumbnail, thumbnail)
            WHERE preview_id=:preview_id AND asset_id=:asset_id',

        'set_support_level' => 'UPDATE `as_assets`
            SET support_level=:support_level
            WHERE asset_id=:asset_id',

        'delete' => 'UPDATE `as_assets` SET searchable=FALSE WHERE asset_id=:asset_id',
        'undelete' => 'UPDATE `as_assets` SET searchable=TRUE WHERE asset_id=:asset_id'
    ],
    'asset_edit' => [
        'get_one' => 'SELECT edit_id, `as_asset_edits`.asset_id, user_id, title, description, category_id, godot_version, version_string,
            cost, download_provider, download_commit, browse_url, issues_url, icon_url, status, reason,
            edit_preview_id, `as_asset_previews`.preview_id, `as_asset_edit_previews`.type, `as_asset_edit_previews`.link, `as_asset_edit_previews`.thumbnail, `as_asset_edit_previews`.operation,
            `as_asset_previews`.type AS orig_type, `as_asset_previews`.link AS orig_link, `as_asset_previews`.thumbnail AS orig_thumbnail,
            unedited_previews.preview_id AS unedited_preview_id, unedited_previews.type AS unedited_type, unedited_previews.link AS unedited_link, unedited_previews.thumbnail AS unedited_thumbnail, username AS author
            FROM `as_asset_edits`
            LEFT JOIN `as_users` USING (user_id)
            LEFT JOIN `as_asset_edit_previews` USING (edit_id)
            LEFT JOIN `as_asset_previews` USING (preview_id)
            LEFT JOIN `as_asset_previews` AS unedited_previews ON `as_asset_edits`.asset_id = unedited_previews.asset_id
            WHERE edit_id=:edit_id',

        'get_one_bare' => 'SELECT * FROM `as_asset_edits` WHERE edit_id=:edit_id',
        'get_one_with_status' => 'SELECT * FROM `as_asset_edits` WHERE edit_id=:edit_id AND status=:status',
        'get_editable_by_asset_id' => 'SELECT * FROM `as_asset_edits` WHERE asset_id=:asset_id AND status=0',

        'search' => 'SELECT edit_id, asset_id,
        `as_asset_edits`.user_id,
        `as_asset_edits`.submit_date,
        `as_asset_edits`.modify_date,
        COALESCE(`as_asset_edits`.title, `as_assets`.title) AS title,
        COALESCE(`as_asset_edits`.description, `as_assets`.description) AS description,
        COALESCE(`as_asset_edits`.godot_version, `as_assets`.godot_version) AS godot_version,
        COALESCE(`as_asset_edits`.version_string, `as_assets`.version_string) AS version_string,
        COALESCE(`as_asset_edits`.cost, `as_assets`.cost) AS cost,
        COALESCE(`as_asset_edits`.browse_url, `as_assets`.browse_url) AS browse_url,
        COALESCE(`as_asset_edits`.icon_url, `as_assets`.icon_url) AS icon_url,
        category, `as_assets`.support_level, status, reason, username AS author FROM `as_asset_edits`
            LEFT JOIN `as_users` USING (user_id)
            LEFT JOIN `as_categories` USING (category_id)
            LEFT JOIN `as_assets` USING (asset_id)
            WHERE
                status RLIKE :statuses_regex
                AND asset_id LIKE :asset_id AND username LIKE :username
                AND (
                    `as_asset_edits`.title LIKE :filter
                    OR `as_assets`.title LIKE :filter
                    OR username LIKE :filter
                )
            ORDER BY `as_asset_edits`.modify_date DESC
            LIMIT :page_size OFFSET :skip_count',

        'search_count' => 'SELECT count(*) AS count FROM `as_asset_edits`
            LEFT JOIN `as_users` USING (user_id)
            WHERE
                status RLIKE :statuses_regex
                AND asset_id LIKE :asset_id AND username LIKE :username
                AND (
                    title LIKE :filter
                    OR username LIKE :filter
                )
            ',

        'submit' => 'INSERT INTO `as_asset_edits`
            SET asset_id=:asset_id, user_id=:user_id, title=:title, description=:description, category_id=:category_id, godot_version=:godot_version, version_string=:version_string,
                cost=:cost, download_provider=:download_provider, download_commit=:download_commit, browse_url=:browse_url, issues_url=:issues_url, icon_url=:icon_url,
                status=0, reason="", submit_date=NOW()',

        'update' => 'UPDATE `as_asset_edits`
            SET title=:title, description=:description, category_id=:category_id, godot_version=:godot_version, version_string=:version_string, cost=:cost,
            download_provider=:download_provider, download_commit=:download_commit, browse_url=:browse_url, issues_url=:issues_url, icon_url=:icon_url
            WHERE edit_id=:edit_id AND status=0',

        'add_preview' => 'INSERT INTO `as_asset_edit_previews`
            SET edit_id=:edit_id, preview_id=:preview_id, type=:type, link=:link, thumbnail=:thumbnail, operation=:operation',
        'update_preview' => 'UPDATE `as_asset_edit_previews`
            SET type=COALESCE(:type, type), link=COALESCE(:link, link), thumbnail=COALESCE(:thumbnail, thumbnail)
            WHERE edit_id=:edit_id AND edit_preview_id=:edit_preview_id',
        'remove_preview' => 'DELETE FROM `as_asset_edit_previews`
            WHERE edit_id=:edit_id AND edit_preview_id=:edit_preview_id',

        'set_asset_id' => 'UPDATE `as_asset_edits` SET asset_id=:asset_id WHERE edit_id=:edit_id',
        'set_status_and_reason' => 'UPDATE `as_asset_edits` SET status=:status, reason=:reason WHERE edit_id=:edit_id'
    ]
];
