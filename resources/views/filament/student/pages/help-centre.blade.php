<x-filament-panels::page>
    <x-student-ui />

    <div class="ef-help-shell">
        <section class="ef-help-intro">
            <p class="ef-eyebrow">Student support</p>
            <h2>Help, corrections, and payment issues.</h2>
            <p>Use this page for account corrections, payment rectification, missing course access, practice issues, or general support.</p>
        </section>

        <div class="ef-help-grid">
            <section class="ef-help-form">
                <form wire:submit="submitRequest">
                    {{ $this->form }}
                    <div class="mt-5 flex justify-end">
                        <x-filament::button type="submit" icon="heroicon-o-paper-airplane" wire:loading.attr="disabled">Send request</x-filament::button>
                    </div>
                </form>
            </section>

            <aside class="ef-help-tips">
                <strong>Before you send a request</strong>
                <ul>
                    <li>For payment issues, include the payment reference.</li>
                    <li>For account corrections, state the incorrect and correct details.</li>
                    <li>For course access, include the course code.</li>
                </ul>
            </aside>
        </div>

        <section class="ef-help-history">
            <div class="ef-help-history-head"><div><p class="ef-eyebrow">Your requests</p><h3>Recent support requests</h3></div></div>
            <div class="ef-help-request-list">
                @forelse ($this->requests as $request)
                    <article class="ef-help-request">
                        <div><span>{{ $request->category->label() }}</span><strong>{{ $request->subject }}</strong><small>{{ $request->created_at->diffForHumans() }}@if ($request->course) · {{ $request->course->code }}@endif</small></div>
                        <span @class(['ef-help-status', 'is-resolved' => $request->status === \App\Enums\SupportRequestStatus::Resolved])>{{ $request->status->label() }}</span>
                    </article>
                @empty
                    <p class="ef-help-empty">You have not sent any support requests yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-filament-panels::page>
