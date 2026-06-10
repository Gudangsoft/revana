{{-- birthday-notification.blade.php
     Variables expected:
       $todayBirthdays  — Collection of stdObject {id, name, type, umur, role}
       $wishRoute       — POST route URL for sending a wish
       $myWishes        — array of "type-id" strings (wishes already sent by current user this year)
--}}
@if(isset($todayBirthdays) && $todayBirthdays->count() > 0)
<div class="birthday-notification mb-4" id="birthday-notification-widget">
    {{-- header --}}
    <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-top"
         style="background:linear-gradient(135deg,#ff6b9d 0%,#ffd700 50%,#c77dff 100%);">
        <span style="font-size:1.5rem;animation:bday-bounce 1.2s ease infinite alternate;display:inline-block">🎂</span>
        <span class="fw-bold text-white">
            Ada yang ulang tahun hari ini!
        </span>
        <span class="badge ms-auto" style="background:rgba(255,255,255,.25);color:#fff;font-size:.85rem">
            {{ $todayBirthdays->count() }} orang
        </span>
        <button type="button"
                class="btn-close btn-close-white btn-sm ms-1"
                aria-label="Close"
                onclick="document.getElementById('birthday-notification-widget').style.display='none'"></button>
    </div>

    {{-- body --}}
    <div class="border border-top-0 rounded-bottom px-3 pt-2 pb-3"
         style="border-color:#ffd700!important;background:rgba(255,215,0,.06);">

        @if(session('wish_sent'))
        <div class="alert alert-success py-1 px-3 mb-2 d-flex align-items-center gap-2"
             style="font-size:.85rem;border-radius:8px">
            <i class="bi bi-check-circle-fill"></i> {{ session('wish_sent') }}
        </div>
        @endif

        @foreach($todayBirthdays as $person)
            @php $key = $person->type . '-' . $person->id; @endphp
            <div class="py-2 {{ !$loop->last ? 'border-bottom' : '' }}"
                 style="border-color:rgba(255,215,0,.35)!important">
                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    {{-- person info --}}
                    <div class="d-flex align-items-center gap-2">
                        <span style="font-size:1.3rem">🎉</span>
                        <div>
                            <span class="fw-semibold">{{ $person->name }}</span>
                            <small class="text-muted ms-1 text-capitalize">({{ $person->type }})</small>
                            @if($person->umur)
                            <span class="badge rounded-pill ms-1 fw-normal"
                                  style="background:#ffd700;color:#1a0533;font-size:.7rem">
                                {{ $person->umur }} tahun
                            </span>
                            @endif
                        </div>
                    </div>
                    {{-- action --}}
                    <div>
                        @if(in_array($key, $myWishes ?? []))
                        <span class="badge bg-success py-1 px-2">
                            <i class="bi bi-check-lg me-1"></i>Ucapan terkirim
                        </span>
                        @else
                        <button class="btn btn-sm btn-outline-warning fw-semibold"
                                data-bs-toggle="collapse"
                                data-bs-target="#wish-form-{{ $key }}">
                            <i class="bi bi-chat-heart me-1"></i>Kirim Ucapan
                        </button>
                        @endif
                    </div>
                </div>

                @if(!in_array($key, $myWishes ?? []))
                <div class="collapse mt-2" id="wish-form-{{ $key }}">
                    <form action="{{ $wishRoute }}" method="POST">
                        @csrf
                        <input type="hidden" name="recipient_type" value="{{ $person->type }}">
                        <input type="hidden" name="recipient_id"   value="{{ $person->id }}">
                        <div class="input-group input-group-sm">
                            <input type="text" name="message" class="form-control border-warning"
                                   placeholder="Tulis ucapan..."
                                   required maxlength="200"
                                   value="Selamat ulang tahun{{ $person->umur ? ' ke-'.$person->umur : '' }}, {{ $person->name }}! Semoga sukses selalu 🎉">
                            <button type="submit" class="btn btn-warning fw-semibold">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>
                    </form>
                </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<style>
@keyframes bday-bounce {
    from { transform: translateY(0) rotate(-8deg); }
    to   { transform: translateY(-6px) rotate(8deg); }
}
</style>
@endif
