<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class SitesSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('sites.site_name', 'PPSI Petra');
        $this->migrator->add('sites.site_description', 'Platform Pengembangan Sistem Informasi');
        $this->migrator->add('sites.site_keywords', 'PPSI, Petra, Sistem Informasi, Platform');
        $this->migrator->add('sites.site_profile', '');
        $this->migrator->add('sites.site_logo', '');
        $this->migrator->add('sites.site_author', 'PPSI Petra');
        $this->migrator->add('sites.site_address', 'Surabaya, Jawa Timur, Indonesia');
        $this->migrator->add('sites.site_email', 'info@petra.ac.id');
        $this->migrator->add('sites.site_phone', '+628156000506');
        $this->migrator->add('sites.site_phone_code', '+62');
        $this->migrator->add('sites.site_location', 'Indonesia');
        $this->migrator->add('sites.site_currency', 'IDR');
        $this->migrator->add('sites.site_language', 'Indonesian');
        $this->migrator->add('sites.site_social', []);
    }
}
