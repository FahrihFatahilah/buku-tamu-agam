<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Models\WeddingDomain;
use App\Services\DomainService;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    public function __construct(private DomainService $domainService) {}

    public function index(Wedding $wedding)
    {
        $this->authorize('manageSettings', $wedding);
        $domains = $wedding->domains()->orderBy('is_primary', 'desc')->get();
        return view('admin.domains.index', compact('wedding', 'domains'));
    }

    public function store(Request $request, Wedding $wedding)
    {
        $this->authorize('manageSettings', $wedding);

        $validated = $request->validate([
            'domain' => 'required|string|max:253|unique:wedding_domains,domain',
            'type'   => 'required|in:subdomain,custom',
        ]);

        try {
            if ($validated['type'] === 'subdomain') {
                $this->domainService->addSubdomain($wedding, $validated['domain']);
            } else {
                $this->domainService->addCustomDomain($wedding, $validated['domain']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['domain' => $e->getMessage()]);
        }

        return back()->with('success', 'Domain berhasil ditambahkan.');
    }

    public function verify(Wedding $wedding, WeddingDomain $domain)
    {
        $this->authorize('manageSettings', $wedding);
        abort_if($domain->wedding_id !== $wedding->id, 403);

        $verified = $this->domainService->verifyDomain($domain);

        return back()->with(
            $verified ? 'success' : 'error',
            $verified ? 'Domain berhasil diverifikasi.' : 'Verifikasi gagal. Pastikan DNS TXT record sudah ditambahkan.'
        );
    }

    public function destroy(Wedding $wedding, WeddingDomain $domain)
    {
        $this->authorize('manageSettings', $wedding);
        abort_if($domain->wedding_id !== $wedding->id, 403);

        if ($domain->is_primary) {
            return back()->withErrors(['domain' => 'Domain utama tidak dapat dihapus.']);
        }

        app(\App\Services\DomainResolver::class)->forgetCache($domain->domain);
        $domain->delete();

        return back()->with('success', 'Domain berhasil dihapus.');
    }
}
