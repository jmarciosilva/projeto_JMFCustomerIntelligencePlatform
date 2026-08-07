<?php

namespace JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi;

use JmfSystem\CustomerIntelligence\Services\JmfCiApiClient;
use Livewire\Component;

class Configuration extends Component
{
    public string $baseUrl = '';

    public string $token = '';

    public bool $tokenVisible = false;

    public bool $isOnline = false;

    public string $lastCheckTime = '';

    public string $message = '';

    private JmfCiApiClient $apiClient;

    public function mount(JmfCiApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
        $this->baseUrl = (string) config('customer-intelligence.base_url', '');
        $this->token = (string) config('customer-intelligence.token', '');
    }

    public function toggleTokenVisibility(): void
    {
        $this->tokenVisible = ! $this->tokenVisible;
    }

    public function validateConnection(): void
    {
        if (! $this->baseUrl || ! $this->token) {
            $this->message = 'Base URL e Token são obrigatórios';
            $this->isOnline = false;

            return;
        }

        $isOnline = $this->apiClient->healthCheck();

        if ($isOnline) {
            $this->message = '✓ Conectado com sucesso!';
            $this->isOnline = true;
        } else {
            $this->message = '✗ Falha ao conectar. Verifique Base URL e Token.';
            $this->isOnline = false;
        }

        $this->lastCheckTime = now()->format('H:i:s');
    }

    public function render()
    {
        return view('plugins.jmf-ci.configuration', [
            'isOnline' => $this->isOnline,
            'message' => $this->message,
            'lastCheckTime' => $this->lastCheckTime,
        ]);
    }
}
