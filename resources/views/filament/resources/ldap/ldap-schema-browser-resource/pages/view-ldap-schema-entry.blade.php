<x-filament-panels::page>
    <div style="display:flex;flex-direction:column;gap:18px;">

        <div style="padding:24px;border-radius:20px;background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.03));border:1px solid rgba(255,255,255,.08);">
            <div style="font-size:34px;font-weight:800;color:#fff;line-height:1.2;">
                {{ $this->record->name }}
            </div>

            <div style="margin-top:18px;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;">
                <div style="padding:14px 16px;border-radius:14px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);">
                    <div style="font-size:12px;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;">Type</div>
                    <div style="margin-top:6px;font-size:16px;font-weight:700;color:#fff;">{{ $this->record->type ?: '-' }}</div>
                </div>

                <div style="padding:14px 16px;border-radius:14px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);">
                    <div style="font-size:12px;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;">OID</div>
                    <div style="margin-top:6px;font-size:16px;font-weight:700;color:#fff;word-break:break-all;">{{ $this->record->oid ?: '-' }}</div>
                </div>

                <div style="padding:14px 16px;border-radius:14px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);">
                    <div style="font-size:12px;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;">Description</div>
                    <div style="margin-top:6px;font-size:16px;font-weight:700;color:#fff;">{{ $this->record->description ?: '-' }}</div>
                </div>

                <div style="padding:14px 16px;border-radius:14px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);">
                    <div style="font-size:12px;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;">SUP</div>
                    <div style="margin-top:6px;font-size:16px;font-weight:700;color:#fff;">{{ $this->record->sup ?: '-' }}</div>
                </div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:18px;">
            <div style="padding:22px;border-radius:20px;background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.03));border:1px solid rgba(255,255,255,.08);">
                <div style="font-size:22px;font-weight:800;color:#fff;">MUST Attributes</div>

                <div style="margin-top:14px;">
                    @if (! empty($this->record->must))
                        <div style="display:flex;flex-wrap:wrap;gap:10px;">
                            @foreach ($this->record->must as $item)
                                <span style="display:inline-block;padding:8px 12px;border-radius:999px;background:rgba(59,130,246,.14);border:1px solid rgba(59,130,246,.3);color:#dbeafe;font-size:14px;font-weight:600;">
                                    {{ $item }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <div style="color:#9ca3af;font-size:15px;">Tidak ada MUST attribute.</div>
                    @endif
                </div>
            </div>

            <div style="padding:22px;border-radius:20px;background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.03));border:1px solid rgba(255,255,255,.08);">
                <div style="font-size:22px;font-weight:800;color:#fff;">MAY Attributes</div>

                <div style="margin-top:14px;">
                    @if (! empty($this->record->may))
                        <div style="display:flex;flex-wrap:wrap;gap:10px;">
                            @foreach ($this->record->may as $item)
                                <span style="display:inline-block;padding:8px 12px;border-radius:999px;background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.28);color:#d1fae5;font-size:14px;font-weight:600;">
                                    {{ $item }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <div style="color:#9ca3af;font-size:15px;">Tidak ada MAY attribute.</div>
                    @endif
                </div>
            </div>
        </div>

        <div style="padding:22px;border-radius:20px;background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.03));border:1px solid rgba(255,255,255,.08);">
            <div style="font-size:22px;font-weight:800;color:#fff;">Raw Schema Definition</div>

            <pre style="margin-top:14px;padding:16px;border-radius:16px;background:#0b1220;border:1px solid rgba(255,255,255,.08);color:#d1d5db;font-size:13px;line-height:1.7;overflow:auto;white-space:pre-wrap;word-break:break-word;">{{ $this->record->raw }}</pre>
        </div>
    </div>
</x-filament-panels::page>
