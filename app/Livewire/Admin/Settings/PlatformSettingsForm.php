<?php

namespace App\Livewire\Admin\Settings;

use App\Models\PlatformSetting;
use Livewire\Component;

class PlatformSettingsForm extends Component
{
    public string $tab = 'api_keys';
    public array $settings = [];
    public array $formData = [];

    #[Validate('required|string')]
    public string $serpapi_key = '';

    #[Validate('required|string')]
    public string $google_trends_region = 'BR';

    #[Validate('required|string')]
    public string $affiliate_commission_default = '10';

    public function mount(): void
    {
        $this->loadSettings();
    }

    public function loadSettings(): void
    {
        $this->serpapi_key = PlatformSetting::get('serpapi_key', '');
        $this->google_trends_region = PlatformSetting::get('google_trends_region', 'BR');
        $this->affiliate_commission_default = PlatformSetting::get('affiliate_commission_default', '10');
    }

    public function saveApiKeys(): void
    {
        PlatformSetting::set('serpapi_key', $this->serpapi_key, 'api_keys', 'SerpAPI Key para Google Trends');
        PlatformSetting::set('google_trends_region', $this->google_trends_region, 'trends', 'Região padrão para Google Trends');

        $this->dispatch('toast', message: '✅ Configurações de API salvas!', type: 'success');
    }

    public function saveAffiliateSettings(): void
    {
        PlatformSetting::set('affiliate_commission_default', $this->affiliate_commission_default, 'affiliate', 'Comissão padrão de afiliados');

        $this->dispatch('toast', message: '✅ Configurações de afiliados salvas!', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.settings.platform-settings-form', [
            'regions' => ['BR' => 'Brasil', 'US' => 'Estados Unidos', 'GLOBAL' => 'Global'],
        ]);
    }
}
