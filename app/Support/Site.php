<?php

namespace App\Support;

final class Site
{
    /**
     * @return array<string, mixed>
     */
    public static function current(): array
    {
        $sites = config('sites.sites', []);
        $defaultKey = (string) config('sites.default', 'loomcraft');

        if (! is_array($sites)) {
            return [];
        }

        $site = $sites[$defaultKey] ?? $sites['loomcraft'] ?? reset($sites);

        return is_array($site) ? $site : [];
    }

    public static function key(): string
    {
        return (string) (self::current()['key'] ?? 'loomcraft');
    }

    public static function is(string $site): bool
    {
        return self::key() === $site;
    }

    public static function hidesLoomFeatures(): bool
    {
        return (bool) (self::current()['hide_loom_features'] ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public static function inertiaPayload(): array
    {
        $site = self::current();

        return [
            'key' => (string) ($site['key'] ?? 'loomcraft'),
            'name' => (string) ($site['name'] ?? 'LoomCraft'),
            'displayName' => (string) ($site['display_name'] ?? $site['name'] ?? 'LoomCraft'),
            'domain' => (string) ($site['domain'] ?? ''),
            'theme' => (string) ($site['theme'] ?? $site['key'] ?? 'loomcraft'),
            'tagline' => (string) ($site['tagline'] ?? ''),
            'description' => (string) ($site['description'] ?? ''),
            'marketplaceLabel' => (string) ($site['marketplace_label'] ?? 'Marketplace'),
            'productsLabel' => (string) ($site['products_label'] ?? 'Products'),
            'dashboardLabel' => (string) ($site['dashboard_label'] ?? 'Dashboard'),
            'registerLabel' => (string) ($site['register_label'] ?? 'Register'),
            'vendorLabel' => (string) ($site['vendor_label'] ?? 'Vendor'),
            'vendorPluralLabel' => (string) ($site['vendor_plural_label'] ?? 'Vendors'),
            'reviewerLabel' => (string) ($site['reviewer_label'] ?? 'Customer'),
            'hideLoomFeatures' => (bool) ($site['hide_loom_features'] ?? false),
        ];
    }
}
