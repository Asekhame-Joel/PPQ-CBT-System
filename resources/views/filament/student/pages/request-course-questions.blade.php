<x-filament-panels::page>
    <x-student-ui />

    <div class="ef-help-shell">
        <section class="ef-help-intro">
            <p class="ef-eyebrow">Grow the question bank</p>
            <h2>Request a course question bank.</h2>
            <p>Can't find a course yet? Send the code and title. Our team will review it and update you here when questions are available.</p>
        </section>

        <div class="ef-help-grid">
            <section class="ef-help-form">
                <form wire:submit="submitRequest">
                    {{ $this->form }}
                    <div class="mt-5 flex justify-end">
                        <x-filament::button type="submit" icon="heroicon-o-paper-airplane" wire:loading.attr="disabled">Send course request</x-filament::button>
                    </div>
                </form>
            </section>

            <aside class="ef-help-tips">
                <strong>Help us add it faster</strong>
                <ul>
                    <li>Use the official course code where possible.</li>
                    <li>Add the complete course title.</li>
                    <li>Include your department or level if the course is specific to it.</li>
                </ul>
            </aside>
        </div>

        <section class="ef-help-history">
            <div class="ef-help-history-head">
                <div>
                    <p class="ef-eyebrow">Your requests</p>
                    <h3>Requested course question banks</h3>
                </div>
            </div>

            <div class="ef-help-request-list">
                @forelse ($this->requests as $request)
                    <article class="ef-help-request">
                        <div>
                            <span>{{ $request->course_code }}</span>
                            <strong>{{ $request->course_name }}</strong>
                            <small>{{ $request->created_at->diffForHumans() }}</small>
                            @if ($request->admin_notes)
                                <p class="ef-request-response"><strong>Admin update:</strong> {{ $request->admin_notes }}</p>
                            @endif
                        </div>
                        <span @class([
                            'ef-help-status',
                            'is-resolved' => $request->status === \App\Enums\CourseQuestionRequestStatus::Added,
                            'is-unavailable' => $request->status === \App\Enums\CourseQuestionRequestStatus::Unavailable,
                        ])>{{ $request->status->label() }}</span>
                    </article>
                @empty
                    <p class="ef-help-empty">You have not requested any course question banks yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-filament-panels::page>
