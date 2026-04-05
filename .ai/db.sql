create table loom_craft.cache
(
    `key`      varchar(255) not null
        primary key,
    value      mediumtext   not null,
    expiration int          not null
)
    collate = utf8mb4_unicode_ci;

create index cache_expiration_index
    on loom_craft.cache (expiration);

create table loom_craft.cache_locks
(
    `key`      varchar(255) not null
        primary key,
    owner      varchar(255) not null,
    expiration int          not null
)
    collate = utf8mb4_unicode_ci;

create index cache_locks_expiration_index
    on loom_craft.cache_locks (expiration);

create table loom_craft.exchange_rates
(
    id            bigint unsigned auto_increment
        primary key,
    from_currency varchar(3)     not null,
    to_currency   varchar(3)     not null,
    rate          decimal(18, 8) not null,
    source        varchar(255)   not null,
    fetched_at    timestamp      not null,
    created_at    timestamp      null,
    updated_at    timestamp      null
)
    collate = utf8mb4_unicode_ci;

create index exchange_rates_pair_fetched_at_index
    on loom_craft.exchange_rates (from_currency, to_currency, fetched_at);

create table loom_craft.failed_jobs
(
    id         bigint unsigned auto_increment
        primary key,
    uuid       varchar(255)                          not null,
    connection text                                  not null,
    queue      text                                  not null,
    payload    longtext                              not null,
    exception  longtext                              not null,
    failed_at  timestamp default current_timestamp() not null,
    constraint failed_jobs_uuid_unique
        unique (uuid)
)
    collate = utf8mb4_unicode_ci;

create table loom_craft.job_batches
(
    id             varchar(255) not null
        primary key,
    name           varchar(255) not null,
    total_jobs     int          not null,
    pending_jobs   int          not null,
    failed_jobs    int          not null,
    failed_job_ids longtext     not null,
    options        mediumtext   null,
    cancelled_at   int          null,
    created_at     int          not null,
    finished_at    int          null
)
    collate = utf8mb4_unicode_ci;

create table loom_craft.jobs
(
    id           bigint unsigned auto_increment
        primary key,
    queue        varchar(255)     not null,
    payload      longtext         not null,
    attempts     tinyint unsigned not null,
    reserved_at  int unsigned     null,
    available_at int unsigned     not null,
    created_at   int unsigned     not null
)
    collate = utf8mb4_unicode_ci;

create index jobs_queue_index
    on loom_craft.jobs (queue);

create table loom_craft.migrations
(
    id        int unsigned auto_increment
        primary key,
    migration varchar(255) not null,
    batch     int          not null
)
    collate = utf8mb4_unicode_ci;

create table loom_craft.password_reset_tokens
(
    email      varchar(255) not null
        primary key,
    token      varchar(255) not null,
    created_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table loom_craft.personal_access_tokens
(
    id             bigint unsigned auto_increment
        primary key,
    tokenable_type varchar(255)    not null,
    tokenable_id   bigint unsigned not null,
    name           text            not null,
    token          varchar(64)     not null,
    abilities      text            null,
    last_used_at   timestamp       null,
    expires_at     timestamp       null,
    created_at     timestamp       null,
    updated_at     timestamp       null,
    constraint personal_access_tokens_token_unique
        unique (token)
)
    collate = utf8mb4_unicode_ci;

create index personal_access_tokens_expires_at_index
    on loom_craft.personal_access_tokens (expires_at);

create index personal_access_tokens_tokenable_type_tokenable_id_index
    on loom_craft.personal_access_tokens (tokenable_type, tokenable_id);

create table loom_craft.product_categories
(
    id                  bigint unsigned auto_increment
        primary key,
    name                varchar(255)           not null,
    slug                varchar(255)           not null,
    description         text                   null,
    discount_percentage decimal(5, 2)          null,
    is_active           tinyint(1)   default 1 not null,
    sort_order          int unsigned default 0 not null,
    created_at          timestamp              null,
    updated_at          timestamp              null,
    constraint product_categories_name_unique
        unique (name),
    constraint product_categories_slug_unique
        unique (slug)
)
    collate = utf8mb4_unicode_ci;

create table loom_craft.product_colors
(
    id         bigint unsigned auto_increment
        primary key,
    name       varchar(255)           not null,
    slug       varchar(255)           not null,
    is_active  tinyint(1)   default 1 not null,
    sort_order int unsigned default 0 not null,
    created_at timestamp              null,
    updated_at timestamp              null,
    constraint product_colors_name_unique
        unique (name),
    constraint product_colors_slug_unique
        unique (slug)
)
    collate = utf8mb4_unicode_ci;

create table loom_craft.sessions
(
    id            varchar(255)    not null
        primary key,
    user_id       bigint unsigned null,
    ip_address    varchar(45)     null,
    user_agent    text            null,
    payload       longtext        not null,
    last_activity int             not null
)
    collate = utf8mb4_unicode_ci;

create index sessions_last_activity_index
    on loom_craft.sessions (last_activity);

create index sessions_user_id_index
    on loom_craft.sessions (user_id);

create table loom_craft.subscription_items
(
    id               bigint unsigned auto_increment
        primary key,
    subscription_id  bigint unsigned not null,
    stripe_id        varchar(255)    not null,
    stripe_product   varchar(255)    not null,
    stripe_price     varchar(255)    not null,
    meter_id         varchar(255)    null,
    quantity         int             null,
    meter_event_name varchar(255)    null,
    created_at       timestamp       null,
    updated_at       timestamp       null,
    constraint subscription_items_stripe_id_unique
        unique (stripe_id)
)
    collate = utf8mb4_unicode_ci;

create index subscription_items_subscription_id_stripe_price_index
    on loom_craft.subscription_items (subscription_id, stripe_price);

create table loom_craft.subscriptions
(
    id            bigint unsigned auto_increment
        primary key,
    user_id       bigint unsigned not null,
    type          varchar(255)    not null,
    stripe_id     varchar(255)    not null,
    stripe_status varchar(255)    not null,
    stripe_price  varchar(255)    null,
    quantity      int             null,
    trial_ends_at timestamp       null,
    ends_at       timestamp       null,
    created_at    timestamp       null,
    updated_at    timestamp       null,
    constraint subscriptions_stripe_id_unique
        unique (stripe_id)
)
    collate = utf8mb4_unicode_ci;

create index subscriptions_user_id_stripe_status_index
    on loom_craft.subscriptions (user_id, stripe_status);

create table loom_craft.users
(
    id                        bigint unsigned auto_increment
        primary key,
    name                      varchar(255)                    not null,
    email                     varchar(255)                    not null,
    email_verified_at         timestamp                       null,
    password                  varchar(255)                    not null,
    role                      varchar(255) default 'customer' not null,
    two_factor_secret         text                            null,
    two_factor_recovery_codes text                            null,
    two_factor_confirmed_at   timestamp                       null,
    remember_token            varchar(100)                    null,
    created_at                timestamp                       null,
    updated_at                timestamp                       null,
    stripe_id                 varchar(255)                    null,
    pm_type                   varchar(255)                    null,
    pm_last_four              varchar(4)                      null,
    trial_ends_at             timestamp                       null,
    constraint users_email_unique
        unique (email)
)
    collate = utf8mb4_unicode_ci;

create table loom_craft.carts
(
    id          bigint unsigned auto_increment
        primary key,
    user_id     bigint unsigned null,
    guest_token varchar(255)    null,
    currency    varchar(255)    not null,
    created_at  timestamp       null,
    updated_at  timestamp       null,
    constraint carts_guest_token_unique
        unique (guest_token),
    constraint carts_user_id_foreign
        foreign key (user_id) references loom_craft.users (id)
)
    collate = utf8mb4_unicode_ci;

create table loom_craft.complaints
(
    id          bigint unsigned auto_increment
        primary key,
    user_id     bigint unsigned null,
    guest_email varchar(255)    null,
    subject     varchar(255)    not null,
    message     text            not null,
    status      varchar(255)    not null,
    handled_by  bigint unsigned null,
    created_at  timestamp       null,
    updated_at  timestamp       null,
    constraint complaints_handled_by_foreign
        foreign key (handled_by) references loom_craft.users (id),
    constraint complaints_user_id_foreign
        foreign key (user_id) references loom_craft.users (id)
)
    collate = utf8mb4_unicode_ci;

create index complaints_status_index
    on loom_craft.complaints (status);

create table loom_craft.mobile_notification_tokens
(
    id           bigint unsigned auto_increment
        primary key,
    user_id      bigint unsigned               not null,
    fcm_token    varchar(512)                  not null,
    platform     varchar(32) default 'android' not null,
    last_used_at timestamp                     null,
    created_at   timestamp                     null,
    updated_at   timestamp                     null,
    constraint mobile_notification_tokens_fcm_token_unique
        unique (fcm_token),
    constraint mobile_notification_tokens_user_id_foreign
        foreign key (user_id) references loom_craft.users (id)
            on delete cascade
)
    collate = utf8mb4_unicode_ci;

create table loom_craft.orders
(
    id                      bigint unsigned auto_increment
        primary key,
    public_id               varchar(40)     null,
    user_id                 bigint unsigned null,
    guest_name              varchar(255)    null,
    guest_email             varchar(255)    null,
    status                  varchar(255)    not null,
    currency                varchar(255)    not null,
    subtotal                decimal(10, 2)  not null,
    commission_total        decimal(10, 2)  not null,
    total                   decimal(10, 2)  not null,
    shipping_responsibility varchar(255)    not null,
    placed_at               timestamp       null,
    created_at              timestamp       null,
    updated_at              timestamp       null,
    deleted_at              timestamp       null,
    constraint orders_public_id_unique
        unique (public_id),
    constraint orders_user_id_foreign
        foreign key (user_id) references loom_craft.users (id)
)
    collate = utf8mb4_unicode_ci;

create table loom_craft.order_addresses
(
    id           bigint unsigned auto_increment
        primary key,
    order_id     bigint unsigned not null,
    type         varchar(255)    not null,
    full_name    varchar(255)    not null,
    line1        varchar(255)    not null,
    line2        varchar(255)    null,
    city         varchar(255)    not null,
    region       varchar(255)    null,
    postal_code  varchar(255)    null,
    country_code varchar(255)    not null,
    phone        varchar(255)    null,
    created_at   timestamp       null,
    updated_at   timestamp       null,
    constraint order_addresses_order_id_foreign
        foreign key (order_id) references loom_craft.orders (id)
)
    collate = utf8mb4_unicode_ci;

create index order_addresses_type_index
    on loom_craft.order_addresses (type);

create index orders_status_index
    on loom_craft.orders (status);

create table loom_craft.payments
(
    id                               bigint unsigned auto_increment
        primary key,
    order_id                         bigint unsigned not null,
    method                           varchar(255)    not null,
    status                           varchar(255)    not null,
    amount                           decimal(10, 2)  not null,
    currency                         varchar(255)    not null,
    original_amount                  decimal(10, 2)  null,
    original_currency                varchar(3)      null,
    exchange_rate                    decimal(18, 8)  null,
    exchange_rate_source             varchar(255)    null,
    exchange_rate_fetched_at         timestamp       null,
    provider_reference               varchar(255)    null,
    bank_transfer_slip_path          varchar(255)    null,
    bank_transfer_slip_original_name varchar(255)    null,
    bank_transfer_slip_mime_type     varchar(255)    null,
    bank_transfer_slip_uploaded_at   timestamp       null,
    verified_by                      bigint unsigned null,
    verified_at                      timestamp       null,
    created_at                       timestamp       null,
    updated_at                       timestamp       null,
    constraint payments_order_id_foreign
        foreign key (order_id) references loom_craft.orders (id),
    constraint payments_verified_by_foreign
        foreign key (verified_by) references loom_craft.users (id)
)
    collate = utf8mb4_unicode_ci;

create index payments_status_index
    on loom_craft.payments (status);

create table loom_craft.suggestions
(
    id          bigint unsigned auto_increment
        primary key,
    user_id     bigint unsigned null,
    guest_email varchar(255)    null,
    title       varchar(255)    not null,
    details     text            not null,
    status      varchar(255)    not null,
    handled_by  bigint unsigned null,
    created_at  timestamp       null,
    updated_at  timestamp       null,
    constraint suggestions_handled_by_foreign
        foreign key (handled_by) references loom_craft.users (id),
    constraint suggestions_user_id_foreign
        foreign key (user_id) references loom_craft.users (id)
)
    collate = utf8mb4_unicode_ci;

create index suggestions_status_index
    on loom_craft.suggestions (status);

create index users_role_index
    on loom_craft.users (role);

create index users_stripe_id_index
    on loom_craft.users (stripe_id);

create table loom_craft.vendors
(
    id                bigint unsigned auto_increment
        primary key,
    user_id           bigint unsigned              not null,
    display_name      varchar(255)                 not null,
    slug              varchar(255)                 null,
    bio               text                         null,
    tagline           varchar(255)                 null,
    website_url       varchar(255)                 null,
    contact_email     varchar(255)                 null,
    contact_phone     varchar(50)                  null,
    whatsapp_number   varchar(50)                  null,
    logo_path         varchar(255)                 null,
    cover_image_path  varchar(255)                 null,
    about_title       varchar(255)                 null,
    craft_specialties longtext collate utf8mb4_bin null
        check (json_valid(`craft_specialties`)),
    years_active      smallint unsigned            null,
    is_contact_public tinyint(1) default 1         not null,
    is_website_public tinyint(1) default 1         not null,
    location          varchar(255)                 null,
    status            varchar(255)                 not null,
    approved_at       timestamp                    null,
    approved_by       bigint unsigned              null,
    created_at        timestamp                    null,
    updated_at        timestamp                    null,
    constraint vendors_slug_unique
        unique (slug),
    constraint vendors_approved_by_foreign
        foreign key (approved_by) references loom_craft.users (id),
    constraint vendors_user_id_foreign
        foreign key (user_id) references loom_craft.users (id)
)
    collate = utf8mb4_unicode_ci;

create table loom_craft.products
(
    id                   bigint unsigned auto_increment
        primary key,
    vendor_id            bigint unsigned            not null,
    name                 varchar(255)               not null,
    product_code         varchar(100)               not null,
    slug                 varchar(255)               not null,
    description          text                       not null,
    vendor_price         decimal(10, 2)             not null,
    commission_rate      decimal(5, 2) default 7.00 not null,
    selling_price        decimal(10, 2)             not null,
    discount_percentage  decimal(5, 2)              null,
    materials            text                       null,
    pieces_count         int unsigned               null,
    production_time_days int unsigned               null,
    dimension_length     decimal(10, 2)             null,
    dimension_width      decimal(10, 2)             null,
    dimension_height     decimal(10, 2)             null,
    dimension_unit       varchar(255)               null,
    status               varchar(255)               not null,
    created_at           timestamp                  null,
    updated_at           timestamp                  null,
    constraint products_product_code_unique
        unique (product_code),
    constraint products_slug_unique
        unique (slug),
    constraint products_vendor_id_foreign
        foreign key (vendor_id) references loom_craft.vendors (id)
)
    collate = utf8mb4_unicode_ci;

create table loom_craft.cart_items
(
    id         bigint unsigned auto_increment
        primary key,
    cart_id    bigint unsigned not null,
    product_id bigint unsigned not null,
    quantity   int unsigned    not null,
    unit_price decimal(10, 2)  not null,
    created_at timestamp       null,
    updated_at timestamp       null,
    constraint cart_items_cart_id_foreign
        foreign key (cart_id) references loom_craft.carts (id),
    constraint cart_items_product_id_foreign
        foreign key (product_id) references loom_craft.products (id)
)
    collate = utf8mb4_unicode_ci;

create table loom_craft.category_product
(
    id                  bigint unsigned auto_increment
        primary key,
    product_id          bigint unsigned not null,
    product_category_id bigint unsigned not null,
    created_at          timestamp       null,
    updated_at          timestamp       null,
    constraint category_product_product_id_product_category_id_unique
        unique (product_id, product_category_id),
    constraint category_product_product_category_id_foreign
        foreign key (product_category_id) references loom_craft.product_categories (id)
            on delete cascade,
    constraint category_product_product_id_foreign
        foreign key (product_id) references loom_craft.products (id)
            on delete cascade
)
    collate = utf8mb4_unicode_ci;

create table loom_craft.order_items
(
    id                bigint unsigned auto_increment
        primary key,
    order_id          bigint unsigned not null,
    product_id        bigint unsigned not null,
    vendor_id         bigint unsigned not null,
    quantity          int unsigned    not null,
    unit_price        decimal(10, 2)  not null,
    commission_rate   decimal(5, 2)   not null,
    commission_amount decimal(10, 2)  not null,
    line_total        decimal(10, 2)  not null,
    created_at        timestamp       null,
    updated_at        timestamp       null,
    constraint order_items_order_id_foreign
        foreign key (order_id) references loom_craft.orders (id),
    constraint order_items_product_id_foreign
        foreign key (product_id) references loom_craft.products (id),
    constraint order_items_vendor_id_foreign
        foreign key (vendor_id) references loom_craft.vendors (id)
)
    collate = utf8mb4_unicode_ci;

create table loom_craft.disputes
(
    id                bigint unsigned auto_increment
        primary key,
    order_id          bigint unsigned not null,
    order_item_id     bigint unsigned null,
    opened_by_user_id bigint unsigned null,
    status            varchar(255)    not null,
    reason            text            not null,
    resolution        text            null,
    refund_amount     decimal(10, 2)  null,
    handled_by        bigint unsigned null,
    created_at        timestamp       null,
    updated_at        timestamp       null,
    constraint disputes_handled_by_foreign
        foreign key (handled_by) references loom_craft.users (id),
    constraint disputes_opened_by_user_id_foreign
        foreign key (opened_by_user_id) references loom_craft.users (id),
    constraint disputes_order_id_foreign
        foreign key (order_id) references loom_craft.orders (id),
    constraint disputes_order_item_id_foreign
        foreign key (order_item_id) references loom_craft.order_items (id)
)
    collate = utf8mb4_unicode_ci;

create index disputes_status_index
    on loom_craft.disputes (status);

create index order_items_vendor_id_index
    on loom_craft.order_items (vendor_id);

create table loom_craft.product_color_product
(
    id               bigint unsigned auto_increment
        primary key,
    product_id       bigint unsigned not null,
    product_color_id bigint unsigned not null,
    created_at       timestamp       null,
    updated_at       timestamp       null,
    constraint product_color_product_product_id_product_color_id_unique
        unique (product_id, product_color_id),
    constraint product_color_product_product_color_id_foreign
        foreign key (product_color_id) references loom_craft.product_colors (id)
            on delete cascade,
    constraint product_color_product_product_id_foreign
        foreign key (product_id) references loom_craft.products (id)
            on delete cascade
)
    collate = utf8mb4_unicode_ci;

create table loom_craft.product_media
(
    id         bigint unsigned auto_increment
        primary key,
    product_id bigint unsigned        not null,
    type       varchar(255)           not null,
    path       varchar(255)           not null,
    alt_text   varchar(255)           null,
    sort_order int unsigned default 0 not null,
    created_at timestamp              null,
    updated_at timestamp              null,
    constraint product_media_product_id_foreign
        foreign key (product_id) references loom_craft.products (id)
)
    collate = utf8mb4_unicode_ci;

create index product_media_type_index
    on loom_craft.product_media (type);

create table loom_craft.product_reports
(
    id          bigint unsigned auto_increment
        primary key,
    product_id  bigint unsigned not null,
    user_id     bigint unsigned null,
    guest_email varchar(255)    null,
    reason      text            not null,
    status      varchar(255)    not null,
    handled_by  bigint unsigned null,
    created_at  timestamp       null,
    updated_at  timestamp       null,
    constraint product_reports_handled_by_foreign
        foreign key (handled_by) references loom_craft.users (id),
    constraint product_reports_product_id_foreign
        foreign key (product_id) references loom_craft.products (id),
    constraint product_reports_user_id_foreign
        foreign key (user_id) references loom_craft.users (id)
)
    collate = utf8mb4_unicode_ci;

create index product_reports_status_index
    on loom_craft.product_reports (status);

create table loom_craft.product_reviews
(
    id         bigint unsigned auto_increment
        primary key,
    product_id bigint unsigned  not null,
    user_id    bigint unsigned  not null,
    rating     tinyint unsigned not null,
    review     text             not null,
    created_at timestamp        null,
    updated_at timestamp        null,
    constraint product_reviews_product_id_user_id_unique
        unique (product_id, user_id),
    constraint product_reviews_product_id_foreign
        foreign key (product_id) references loom_craft.products (id)
            on delete cascade,
    constraint product_reviews_user_id_foreign
        foreign key (user_id) references loom_craft.users (id)
            on delete cascade
)
    collate = utf8mb4_unicode_ci;

create index products_status_created_at_index
    on loom_craft.products (status, created_at);

create index products_status_index
    on loom_craft.products (status);

create index products_status_selling_price_index
    on loom_craft.products (status, selling_price);

create table loom_craft.shipments
(
    id              bigint unsigned auto_increment
        primary key,
    order_id        bigint unsigned not null,
    vendor_id       bigint unsigned null,
    responsibility  varchar(255)    not null,
    status          varchar(255)    not null,
    carrier         varchar(255)    null,
    tracking_number varchar(255)    null,
    shipped_at      timestamp       null,
    delivered_at    timestamp       null,
    created_at      timestamp       null,
    updated_at      timestamp       null,
    constraint shipments_order_id_foreign
        foreign key (order_id) references loom_craft.orders (id),
    constraint shipments_vendor_id_foreign
        foreign key (vendor_id) references loom_craft.vendors (id)
)
    collate = utf8mb4_unicode_ci;

create table loom_craft.vendor_contact_submissions
(
    id           bigint unsigned auto_increment
        primary key,
    vendor_id    bigint unsigned                          not null,
    name         varchar(255)                             not null,
    email        varchar(255)                             not null,
    phone        varchar(50)                              null,
    subject      varchar(255)                             not null,
    message      text                                     not null,
    status       varchar(255) default 'pending'           not null,
    handled_by   bigint unsigned                          null,
    handled_at   timestamp                                null,
    submitted_at timestamp    default current_timestamp() not null,
    created_at   timestamp                                null,
    updated_at   timestamp                                null,
    constraint vendor_contact_submissions_handled_by_foreign
        foreign key (handled_by) references loom_craft.users (id),
    constraint vendor_contact_submissions_vendor_id_foreign
        foreign key (vendor_id) references loom_craft.vendors (id)
            on delete cascade
)
    collate = utf8mb4_unicode_ci;

create index vendor_contact_submissions_vendor_id_status_index
    on loom_craft.vendor_contact_submissions (vendor_id, status);

create table loom_craft.vendor_locations
(
    id             bigint unsigned auto_increment
        primary key,
    vendor_id      bigint unsigned      not null,
    location_name  varchar(255)         not null,
    address_line_1 varchar(255)         not null,
    address_line_2 varchar(255)         null,
    city           varchar(255)         not null,
    region         varchar(255)         null,
    postal_code    varchar(255)         null,
    country        varchar(255)         not null,
    phone          varchar(50)          null,
    hours          varchar(255)         null,
    map_url        varchar(255)         null,
    is_primary     tinyint(1) default 0 not null,
    created_at     timestamp            null,
    updated_at     timestamp            null,
    constraint vendor_locations_vendor_id_foreign
        foreign key (vendor_id) references loom_craft.vendors (id)
            on delete cascade
)
    collate = utf8mb4_unicode_ci;

create index vendor_locations_vendor_id_index
    on loom_craft.vendor_locations (vendor_id);

create table loom_craft.vendor_payouts
(
    id         bigint unsigned auto_increment
        primary key,
    vendor_id  bigint unsigned not null,
    order_id   bigint unsigned null,
    amount     decimal(10, 2)  not null,
    currency   varchar(255)    not null,
    status     varchar(255)    not null,
    paid_at    timestamp       null,
    created_at timestamp       null,
    updated_at timestamp       null,
    constraint vendor_payouts_order_id_foreign
        foreign key (order_id) references loom_craft.orders (id),
    constraint vendor_payouts_vendor_id_foreign
        foreign key (vendor_id) references loom_craft.vendors (id)
)
    collate = utf8mb4_unicode_ci;

create index vendors_status_display_name_index
    on loom_craft.vendors (status, display_name);

create index vendors_status_index
    on loom_craft.vendors (status);

