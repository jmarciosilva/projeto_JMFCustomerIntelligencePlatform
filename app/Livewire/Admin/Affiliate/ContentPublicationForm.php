<?php

namespace App\Livewire\Admin\Affiliate;

use App\Models\Campaign;
use App\Models\ContentPublication;
use App\Models\ProductOpportunity;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class ContentPublicationForm extends Component
{
    public ?ContentPublication $content = null;

    public int $campaign_id = 0;

    public ?int $product_opportunity_id = null;

    public string $title = '';

    public string $description = '';

    public string $content_type = ContentPublication::TYPE_BLOG_POST;

    public string $platform = ContentPublication::PLATFORM_WEBSITE;

    public string $url = '';

    public string $status = ContentPublication::STATUS_DRAFT;

    public string $published_at = '';

    public function mount(?ContentPublication $content = null): void
    {
        if ($content) {
            $this->content = $content;
            $this->campaign_id = $content->campaign_id;
            $this->product_opportunity_id = $content->product_opportunity_id;
            $this->title = $content->title;
            $this->description = $content->description ?? '';
            $this->content_type = $content->content_type;
            $this->platform = $content->platform;
            $this->url = $content->url ?? '';
            $this->status = $content->status;
            $this->published_at = $content->published_at?->toDateTimeLocalString() ?? '';
        }
    }

    public function save(): void
    {
        $this->validate([
            'campaign_id' => 'required|integer|exists:campaigns,id',
            'product_opportunity_id' => 'nullable|integer|exists:product_opportunities,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content_type' => 'required|in:blog_post,social_media,email,video,other',
            'platform' => 'required|in:instagram,facebook,whatsapp,email,website,other',
            'url' => 'nullable|url',
            'status' => 'required|in:draft,published,archived',
            'published_at' => 'nullable|date_format:Y-m-d\TH:i',
        ]);

        $data = [
            'campaign_id' => $this->campaign_id,
            'product_opportunity_id' => $this->product_opportunity_id,
            'title' => $this->title,
            'description' => $this->description,
            'content_type' => $this->content_type,
            'platform' => $this->platform,
            'url' => $this->url ?: null,
            'status' => $this->status,
            'published_at' => $this->published_at ? \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $this->published_at) : null,
        ];

        if ($this->content) {
            $this->content->update($data);
            session()->flash('message', 'Conteúdo atualizado com sucesso.');
        } else {
            ContentPublication::create($data);
            session()->flash('message', 'Conteúdo criado com sucesso.');
            redirect()->route('admin.affiliate.content.index');
        }
    }

    public function render()
    {
        $campaigns = Campaign::orderBy('name')->get();
        $opportunities = ProductOpportunity::where('status', 'pending')
            ->orderByDesc('opportunity_score')
            ->get();

        return view('livewire.admin.affiliate.content-publication-form', [
            'campaigns' => $campaigns,
            'opportunities' => $opportunities,
        ]);
    }
}
