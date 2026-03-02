<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-envelope-check me-2"></i>Notifications client - {{ $hotel->name }}
        </h2>
        <a href="{{ route('super.hotels.show', $hotel) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <p class="text-muted mb-4">
            Choisissez les canaux (email, SMS, WhatsApp) et personnalisez les messages envoyés au client : enregistrement enregistré, validé ou rejeté.
            Utilisez les variables ci-dessous dans les textes (ex. <code>@verbatim {{ NOM_CLIENT }} @endverbatim</code> ou <code>@verbatim {{ NOM_HOTEL }} @endverbatim</code>).
        </p>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Variables disponibles</h6>
            </div>
            <div class="card-body py-2">
                <div class="row small">
                    @foreach(array_chunk($placeholders, 4, true) as $chunk)
                        <div class="col-md-4">
                            @foreach($chunk as $key => $label)
                                <code>{{ '{{ ' . $key . ' }' . '}' }}</code> <span class="text-muted">— {{ $label }}</span><br>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <form action="{{ route('super.hotels.notifications.update', $hotel) }}" method="POST">
            @csrf
            @method('PUT')

            <ul class="nav nav-tabs mb-3" id="channelTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="email-tab" data-bs-toggle="tab" data-bs-target="#email-panel" type="button" role="tab">
                        <i class="bi bi-envelope me-2"></i>Email
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="sms-tab" data-bs-toggle="tab" data-bs-target="#sms-panel" type="button" role="tab">
                        <i class="bi bi-chat-dots me-2"></i>SMS
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="whatsapp-tab" data-bs-toggle="tab" data-bs-target="#whatsapp-panel" type="button" role="tab">
                        <i class="bi bi-whatsapp me-2"></i>WhatsApp
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="channelTabsContent">
                {{-- Onglet Email --}}
                <div class="tab-pane fade show active" id="email-panel" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="form-check form-switch mb-4">
                                <input type="hidden" name="notification_settings[email][enabled]" value="0">
                                <input class="form-check-input" type="checkbox" name="notification_settings[email][enabled]" value="1" id="email-enabled"
                                    {{ !empty($config['email']['enabled']) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="email-enabled">Activer l'envoi d'emails au client</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nom de l'expéditeur (optionnel)</label>
                                <input type="text" name="notification_settings[email][from_name]" class="form-control" value="{{ $config['email']['from_name'] ?? '' }}" placeholder="{{ $hotel->name }}">
                            </div>
                            @foreach([
                                'created' => ['label' => 'Enregistrement enregistré', 'help' => 'Envoyé à la création de la demande'],
                                'validated' => ['label' => 'Enregistrement validé', 'help' => 'Envoyé après validation par l\'hôtel'],
                                'rejected' => ['label' => 'Enregistrement rejeté', 'help' => 'Envoyé après rejet (variable {{ RAISON_REJET }})'],
                                'check_in' => ['label' => 'Message de bienvenue (check-in)', 'help' => 'Envoyé au client lors du check-in pour le captiver'],
                                'check_out' => ['label' => 'Message d\'au revoir (check-out)', 'help' => 'Envoyé au client lors du check-out pour le fidéliser'],
                            ] as $event => $opts)
                                @php $t = $config['email']['templates'][$event] ?? []; @endphp
                                <div class="border rounded p-3 mb-4">
                                    <h6 class="text-primary">{{ $opts['label'] }}</h6>
                                    <p class="small text-muted mb-2">{{ $opts['help'] }}</p>
                                    <div class="form-check form-switch mb-2">
                                        <input type="hidden" name="notification_settings[email][templates][{{ $event }}][use_system_default]" value="0">
                                        <input class="form-check-input" type="checkbox" name="notification_settings[email][templates][{{ $event }}][use_system_default]" value="1" id="email-use-default-{{ $event }}"
                                            {{ !empty($t['use_system_default']) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="email-use-default-{{ $event }}">Utiliser le message par défaut du système</label>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Sujet de l'email</label>
                                        <input type="text" name="notification_settings[email][templates][{{ $event }}][subject]" class="form-control form-control-sm" value="{{ $t['subject'] ?? '' }}" placeholder="Ex: Votre enregistrement - {{ $hotel->name }}">
                                    </div>
                                    <div>
                                        <label class="form-label small">Corps de l'email (HTML)</label>
                                        <textarea name="notification_settings[email][templates][{{ $event }}][body_html]" class="form-control font-monospace small" rows="8" placeholder="HTML avec variables {{ '{{ NOM_CLIENT }' . '}' . '}' }}, {{ '{{ NUMERO_RESERVATION }' . '}' . '}' }}, etc.">{{ $t['body_html'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Onglet SMS --}}
                <div class="tab-pane fade" id="sms-panel" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="form-check form-switch mb-4">
                                <input type="hidden" name="notification_settings[sms][enabled]" value="0">
                                <input class="form-check-input" type="checkbox" name="notification_settings[sms][enabled]" value="1" id="sms-enabled"
                                    {{ !empty($config['sms']['enabled']) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="sms-enabled">Activer l'envoi de SMS au client</label>
                            </div>
                            <div class="alert alert-info small">
                                <i class="bi bi-info-circle me-2"></i>
                                Une fois l'API SMS configurée (clé et expéditeur ci-dessous), les messages seront envoyés automatiquement. En attendant, la clé peut rester vide.
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Clé API SMS</label>
                                    <input type="text" name="notification_settings[sms][api_key]" class="form-control" value="{{ $config['sms']['api_key'] ?? '' }}" placeholder="À remplir quand l'API sera branchée">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Expéditeur (numéro ou nom court)</label>
                                    <input type="text" name="notification_settings[sms][sender]" class="form-control" value="{{ $config['sms']['sender'] ?? '' }}" placeholder="Ex: HOTEL">
                                </div>
                            </div>
                            @foreach(['created' => 'Enregistrement enregistré', 'validated' => 'Enregistrement validé', 'rejected' => 'Enregistrement rejeté', 'check_in' => 'Message de bienvenue (check-in)', 'check_out' => 'Message d\'au revoir (check-out)'] as $event => $label)
                                <div class="mb-3">
                                    <label class="form-label">{{ $label }}</label>
                                    <textarea name="notification_settings[sms][templates][{{ $event }}]" class="form-control" rows="2" placeholder="Texte court avec variables {{ '{{ NOM_HOTEL }' . '}' . '}' }}, {{ '{{ NUMERO_RESERVATION }' . '}' . '}' }}">{{ $config['sms']['templates'][$event] ?? '' }}</textarea>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Onglet WhatsApp --}}
                <div class="tab-pane fade" id="whatsapp-panel" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="form-check form-switch mb-4">
                                <input type="hidden" name="notification_settings[whatsapp][enabled]" value="0">
                                <input class="form-check-input" type="checkbox" name="notification_settings[whatsapp][enabled]" value="1" id="whatsapp-enabled"
                                    {{ !empty($config['whatsapp']['enabled']) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="whatsapp-enabled">Activer l'envoi WhatsApp au client</label>
                            </div>
                            <div class="alert alert-info small">
                                <i class="bi bi-info-circle me-2"></i>
                                Une fois l'API WhatsApp Business configurée (clé, ID numéro, nom d'affichage), les messages seront envoyés. Le logo de l'hôtel pourra être utilisé si l'API le permet.
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Clé API WhatsApp</label>
                                    <input type="text" name="notification_settings[whatsapp][api_key]" class="form-control" value="{{ $config['whatsapp']['api_key'] ?? '' }}" placeholder="À remplir quand l'API sera branchée">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">ID du numéro (Phone Number ID)</label>
                                    <input type="text" name="notification_settings[whatsapp][phone_number_id]" class="form-control" value="{{ $config['whatsapp']['phone_number_id'] ?? '' }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Numéro expéditeur (affichage)</label>
                                    <input type="text" name="notification_settings[whatsapp][sender_number]" class="form-control" value="{{ $config['whatsapp']['sender_number'] ?? '' }}" placeholder="Ex: +237...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nom affiché (ex: nom de l'hôtel)</label>
                                    <input type="text" name="notification_settings[whatsapp][sender_name]" class="form-control" value="{{ $config['whatsapp']['sender_name'] ?? $hotel->name }}">
                                </div>
                            </div>
                            @foreach(['created' => 'Enregistrement enregistré', 'validated' => 'Enregistrement validé', 'rejected' => 'Enregistrement rejeté', 'check_in' => 'Message de bienvenue (check-in)', 'check_out' => 'Message d\'au revoir (check-out)'] as $event => $label)
                                <div class="mb-3">
                                    <label class="form-label">{{ $label }}</label>
                                    <textarea name="notification_settings[whatsapp][templates][{{ $event }}]" class="form-control" rows="3" placeholder="Message avec variables {{ '{{ NOM_HOTEL }' . '}' . '}' }}, {{ '{{ NOM_CLIENT }' . '}' . '}' }}, {{ '{{ NUMERO_RESERVATION }' . '}' . '}' }}">{{ $config['whatsapp']['templates'][$event] ?? '' }}</textarea>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-2"></i>Enregistrer la configuration
                </button>
                <a href="{{ route('super.hotels.show', $hotel) }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</x-app-layout>
