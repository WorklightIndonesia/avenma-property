<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgentRequest;
use App\Models\Agent;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AgentController extends Controller
{
    public function __construct(private readonly ImageService $images) {}

    public function index(): View
    {
        return view('admin.agents.index', [
            'agents' => Agent::query()->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.agents.create', ['agent' => new Agent]);
    }

    public function store(AgentRequest $request): RedirectResponse
    {
        $agent = new Agent($request->safe()->except('photo'));
        $agent->save();

        $this->storePhoto($request, $agent);

        return redirect()
            ->route('admin.agents.index')
            ->with('status', 'Agen ditambahkan.');
    }

    public function edit(Agent $agent): View
    {
        return view('admin.agents.edit', ['agent' => $agent]);
    }

    public function update(AgentRequest $request, Agent $agent): RedirectResponse
    {
        $agent->fill($request->safe()->except('photo'))->save();

        $this->storePhoto($request, $agent);

        return redirect()
            ->route('admin.agents.index')
            ->with('status', 'Perubahan disimpan.');
    }

    public function destroy(Agent $agent): RedirectResponse
    {
        // Listings keep their row: the foreign key nulls agent_id, so they fall
        // back to the Avenma contact instead of disappearing.
        $this->deletePhoto($agent->photo_path);

        $agent->delete();

        return redirect()
            ->route('admin.agents.index')
            ->with('status', 'Agen dihapus. Listing yang ia pegang kembali ke Avenma Properti.');
    }

    private function storePhoto(AgentRequest $request, Agent $agent): void
    {
        if (! $file = $request->file('photo')) {
            return;
        }

        $this->deletePhoto($agent->photo_path);

        $agent->photo_path = $this->images->store($file, 'images/agents/'.$agent->id);
        $agent->save();
    }

    private function deletePhoto(?string $path): void
    {
        if (! $path) {
            return;
        }

        Storage::disk('uploads')->delete([
            $path,
            ImageService::thumbPath($path),
            ImageService::mediumPath($path),
        ]);
    }
}
