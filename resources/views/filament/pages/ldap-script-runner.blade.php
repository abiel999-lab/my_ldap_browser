<x-filament-panels::page>
    <div style="display:flex;flex-direction:column;gap:18px;">
        <div style="padding:24px;border-radius:20px;background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.03));border:1px solid rgba(255,255,255,.08);">
            <div style="font-size:34px;font-weight:800;color:#fff;line-height:1.2;">
                Run LDAP Script
            </div>

            <div style="margin-top:16px;display:flex;flex-direction:column;gap:14px;font-size:15px;line-height:1.9;color:#d1d5db;">
                <p>
                    Halaman ini digunakan untuk upload, preview, dan menjalankan script yang memberikan efek ke LDAP.
                    Script yang sudah diupload akan disimpan oleh aplikasi, bisa dipilih kembali, dipreview isinya,
                    lalu dijalankan langsung melalui tombol run.
                </p>
            </div>
        </div>

        <div style="padding:22px;border-radius:20px;background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.03));border:1px solid rgba(255,255,255,.08);">
            <div style="font-size:24px;font-weight:800;color:#fff;">Selected Script Preview</div>

            @if ($this->selectedScript)
                <div style="margin-top:14px;display:flex;flex-direction:column;gap:10px;font-size:15px;line-height:1.9;color:#d1d5db;">
                    <div><strong style="color:#fff;">Name:</strong> {{ $this->selectedScript->name }}</div>
                    <div><strong style="color:#fff;">File:</strong> {{ $this->selectedScript->original_filename }}</div>
                    <div><strong style="color:#fff;">Extension:</strong> {{ $this->selectedScript->extension ?: '-' }}</div>
                    <div><strong style="color:#fff;">Uploaded At:</strong> {{ $this->selectedScript->created_at }}</div>
                    <div>
                        <strong style="color:#fff;">Uploaded By:</strong>
                        {{ $this->selectedScript->uploaded_by_name ?? '-' }}
                        ({{ $this->selectedScript->uploaded_by_email ?? '-' }})
                    </div>
                </div>

                <div style="margin-top:18px;">
                    <div style="font-size:18px;font-weight:800;color:#fff;margin-bottom:10px;">
                        Script Content
                    </div>

                    <pre style="margin:0;padding:18px;border-radius:16px;background:rgba(0,0,0,.35);border:1px solid rgba(255,255,255,.08);font-size:13px;line-height:1.8;color:#86efac;white-space:pre-wrap;overflow:auto;">{{ $this->selectedScript->script_content ?: '-' }}</pre>
                </div>
            @else
                <div style="margin-top:14px;font-size:15px;line-height:1.9;color:#d1d5db;">
                    Belum ada script yang dipilih.
                </div>
            @endif
        </div>

        <div style="padding:22px;border-radius:20px;background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.03));border:1px solid rgba(255,255,255,.08);">
            <div style="font-size:24px;font-weight:800;color:#fff;">Last Script Run</div>

            @if ($this->lastRun)
                <div style="margin-top:14px;display:flex;flex-direction:column;gap:10px;font-size:15px;line-height:1.9;color:#d1d5db;">
                    <div><strong style="color:#fff;">Script:</strong> {{ $this->lastRun->script_label }}</div>
                    <div><strong style="color:#fff;">Status:</strong> {{ $this->lastRun->status }}</div>
                    <div><strong style="color:#fff;">Exit Code:</strong> {{ $this->lastRun->exit_code }}</div>
                    <div><strong style="color:#fff;">Run At:</strong> {{ $this->lastRun->created_at }}</div>
                    <div>
                        <strong style="color:#fff;">Actor:</strong>
                        {{ $this->lastRun->actor_name ?? '-' }}
                        ({{ $this->lastRun->actor_email ?? '-' }})
                    </div>
                </div>

                <div style="margin-top:18px;">
                    <div style="font-size:18px;font-weight:800;color:#fff;margin-bottom:10px;">
                        STDOUT
                    </div>

                    <pre style="margin:0;padding:18px;border-radius:16px;background:rgba(0,0,0,.35);border:1px solid rgba(255,255,255,.08);font-size:13px;line-height:1.8;color:#86efac;white-space:pre-wrap;overflow:auto;">{{ $this->lastRun->stdout ?: '-' }}</pre>
                </div>

                <div style="margin-top:18px;">
                    <div style="font-size:18px;font-weight:800;color:#fff;margin-bottom:10px;">
                        STDERR
                    </div>

                    <pre style="margin:0;padding:18px;border-radius:16px;background:rgba(0,0,0,.35);border:1px solid rgba(255,255,255,.08);font-size:13px;line-height:1.8;color:#fca5a5;white-space:pre-wrap;overflow:auto;">{{ $this->lastRun->stderr ?: '-' }}</pre>
                </div>
            @else
                <div style="margin-top:14px;font-size:15px;line-height:1.9;color:#d1d5db;">
                    Belum ada script yang dijalankan.
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
