<x-filament-panels::page>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
        <a href="{{ \App\Filament\Resources\Ldap\LdapSchemaBrowserResource::getUrl('object-classes') }}"
           style="display:block;padding:22px 20px;border-radius:18px;background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.03));border:1px solid rgba(255,255,255,.08);color:#fff;text-decoration:none;font-weight:700;font-size:20px;transition:.2s ease;">
            Object Classes
        </a>

        <a href="{{ \App\Filament\Resources\Ldap\LdapSchemaBrowserResource::getUrl('attribute-types') }}"
           style="display:block;padding:22px 20px;border-radius:18px;background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.03));border:1px solid rgba(255,255,255,.08);color:#fff;text-decoration:none;font-weight:700;font-size:20px;transition:.2s ease;">
            Attribute Types
        </a>

        <a href="{{ \App\Filament\Resources\Ldap\LdapSchemaBrowserResource::getUrl('matching-rules') }}"
           style="display:block;padding:22px 20px;border-radius:18px;background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.03));border:1px solid rgba(255,255,255,.08);color:#fff;text-decoration:none;font-weight:700;font-size:20px;transition:.2s ease;">
            Matching Rules
        </a>

        <a href="{{ \App\Filament\Resources\Ldap\LdapSchemaBrowserResource::getUrl('matching-rule-use') }}"
           style="display:block;padding:22px 20px;border-radius:18px;background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.03));border:1px solid rgba(255,255,255,.08);color:#fff;text-decoration:none;font-weight:700;font-size:20px;transition:.2s ease;">
            Matching Rule Use
        </a>

        <a href="{{ \App\Filament\Resources\Ldap\LdapSchemaBrowserResource::getUrl('syntaxes') }}"
           style="display:block;padding:22px 20px;border-radius:18px;background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.03));border:1px solid rgba(255,255,255,.08);color:#fff;text-decoration:none;font-weight:700;font-size:20px;transition:.2s ease;">
            Syntaxes
        </a>
    </div>
</x-filament-panels::page>
