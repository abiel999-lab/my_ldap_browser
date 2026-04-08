<?php

namespace App\Listeners;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use App\Services\Sso\SamlGroupExtractor;
use App\Services\Sso\SamlSessionAuthenticator;
use Illuminate\Support\Facades\Log;

class HandleSamlLogin
{
    public function __construct(
        protected SamlSessionAuthenticator $authenticator
    ) {
    }

    public function handle(Saml2LoginEvent $event): void
    {
        $samlUser = $event->getSaml2User();
        $attributes = $samlUser->getAttributes();

        $groupAttribute = config('petra_sso.group_attribute', 'groups');
        $allowedGroup = config('petra_sso.allowed_group', '/app-web/admin-role-web');

        $groups = SamlGroupExtractor::extract($attributes, $groupAttribute);

        $email = $attributes['email'][0]
            ?? $attributes['mail'][0]
            ?? $samlUser->getUserId()
            ?? $samlUser->getNameId();

        $name = $attributes['name'][0]
            ?? $attributes['displayName'][0]
            ?? $attributes['cn'][0]
            ?? $email;

        $payload = [
            'email' => $email,
            'name' => $name,
            'name_id' => $samlUser->getNameId(),
            'groups' => $groups,
            'authorized' => SamlGroupExtractor::hasAllowedGroup($groups, $allowedGroup),
            'raw_attributes' => $attributes,
        ];

        Log::info('Petra SAML login processed', [
            'email' => $email,
            'groups' => $groups,
            'authorized' => $payload['authorized'],
        ]);

        $this->authenticator->login($payload);
    }
}
