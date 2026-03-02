<x-app-layout>
    <x-slot name="header">Signaler une panne</x-slot>

    <div class="mb-4">
        <div class="d-flex gap-2 flex-wrap mb-2">
        <a href="{{ route('maintenance.pannes.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Retour aux pannes</a>
        <a href="{{ route('maintenance.pannes.index', ['status' => 'résolue']) }}" class="btn btn-outline-success btn-sm">Voir les pannes résolues</a>
    </div>
        <h4 class="mb-0">Signaler une panne</h4>
        <p class="text-muted small mb-0">{{ $hotel->name }}</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($types->isEmpty())
        <div class="alert alert-warning">
            <strong>Configuration requise.</strong> Créez d'abord au moins un type de panne dans
            <a href="{{ route('maintenance.panne-types.index') }}">Types et catégories de pannes</a>.
        </div>
    @else
        <div class="alert alert-info border-0 shadow-sm mb-3" role="status">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Où se situe la panne ?</strong> Indiquez le lieu concerné en choisissant une <strong>chambre</strong> ou un <strong>espace existant</strong> dans la liste. Vous ne créez pas de nouveau lieu ici, uniquement un signalement.
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
@php
                    $selectedAreaId = old('maintenance_area_id', $selectedArea?->id ?? request()->get('maintenance_area_id'));
                    $selectedRoomId = old('room_id', $selectedRoom?->id ?? request()->get('room_id'));
                    $initialLocation = old('location_type', request()->get('location_type', $selectedAreaId ? 'area' : 'room'));
                @endphp
                <form action="{{ route('maintenance.pannes.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Image de panne <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" id="image_name" class="form-control" placeholder="Aucune image choisie" readonly>
                                <button type="button" class="btn btn-outline-secondary" id="browseBtn">Parcourir</button>
                                <button type="button" class="btn btn-outline-danger d-none" id="clearBtn" title="Supprimer l'image">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                            <input type="file" name="image" id="image_input" style="display: none;" accept="image/*">
                            @error('image')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                            <div id="image_preview" class="mt-2 d-none">
                                <div class="position-relative d-inline-block">
                                    <img src="" alt="Aperçu de l'image" 
                                         class="rounded shadow-sm" 
                                         style="max-height: 150px; object-fit: cover; border: 2px solid #dee2e6;">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                        <i class="bi bi-check-circle"></i> À uploader
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type de panne <span class="text-danger">*</span></label>
                            <select name="panne_type_id" id="panne_type_id" class="form-select" required>
                                @foreach($types as $t)
                                    <option value="{{ $t->id }}" data-category="{{ $t->panne_category_id }}">{{ $t->name }} ({{ $t->panneCategory->name ?? '' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Localisation <span class="text-danger">*</span></label>
                            <p class="small text-muted mb-3">Choisissez le lieu existant où la panne a été constatée.</p>
                            <div class="btn-group" role="group" aria-label="Type de localisation">
                                <input type="radio" class="btn-check" name="location_type" id="loc_room" value="room" {{ $initialLocation === 'room' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="loc_room"><i class="bi bi-door-closed me-2"></i>Chambre</label>

                                <input type="radio" class="btn-check" name="location_type" id="loc_area" value="area" {{ $initialLocation === 'area' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="loc_area"><i class="bi bi-building me-2"></i>Espace (existant)</label>
                            </div>
                        </div>
                        
                        {{-- Information box for selected room --}}
                        @if($selectedRoom)
                        <div class="col-12" id="selected_room_info">
                            <div class="alert alert-info d-flex align-items-center justify-content-between mb-0">
                                <div>
                                    <i class="bi bi-door-closed me-2"></i>
                                    <strong>Chambre sélectionnée :</strong> 
                                    {{ $selectedRoom->room_number }}
                                    @if($selectedRoom->roomType)
                                        <span class="text-muted">– {{ $selectedRoom->roomType->name }}</span>
                                    @endif
                                    @if($selectedRoom->floor)
                                        <span class="text-muted">(Étage {{ $selectedRoom->floor }})</span>
                                    @endif
                                </div>
                                <input type="hidden" name="room_id" value="{{ $selectedRoom->id }}">
                                <a href="{{ route('maintenance.pannes.select-room', ['location_type' => 'room', 'return_to' => route('maintenance.pannes.create')]) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i> Modifier
                                </a>
                            </div>
                        </div>
                        @endif

                        {{-- Information box for selected area --}}
                        @if($selectedArea)
                        <div class="col-12" id="selected_area_info">
                            <div class="alert alert-info d-flex align-items-center justify-content-between mb-0">
                                <div>
                                    <i class="bi bi-building me-2"></i>
                                    <strong>Espace sélectionné :</strong> 
                                    {{ $selectedArea->name }}
                                    @php $catLabel = \App\Modules\Maintenance\Models\MaintenanceArea::getCategoryLabel($selectedArea->category ?? ''); @endphp
                                    @if($catLabel)
                                        <span class="text-muted">– {{ $catLabel }}</span>
                                    @endif
                                </div>
                                <input type="hidden" name="maintenance_area_id" value="{{ $selectedArea->id }}">
                                <a href="{{ route('maintenance.pannes.select-area', ['location_type' => 'area', 'return_to' => route('maintenance.pannes.create')]) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i> Modifier
                                </a>
                            </div>
                        </div>
                        @endif
                        
                        <div class="col-md-6 @if($selectedRoom) d-none @endif" id="room_block">
                            <a href="{{ route('maintenance.pannes.select-room', ['location_type' => 'room', 'return_to' => route('maintenance.pannes.create')]) }}" class="btn btn-outline-primary">
                                <i class="bi bi-door-closed me-2"></i>Sélectionner une chambre
                            </a>
                            @error('room_id')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-6 @if($selectedArea) d-none @endif" id="area_block">
                            <a href="{{ route('maintenance.pannes.select-area', ['location_type' => 'area', 'return_to' => route('maintenance.pannes.create')]) }}" class="btn btn-outline-primary">
                                <i class="bi bi-building me-2"></i>Sélectionner un espace
                            </a>
                            @error('maintenance_area_id')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description détaillée <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="4" required maxlength="2000" placeholder="Décrivez la panne...">{{ old('description') }}</textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Enregistrer le signalement</button>
                            <a href="{{ route('maintenance.pannes.index') }}" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var locRoom = document.getElementById('loc_room');
            var locArea = document.getElementById('loc_area');
            var roomBlock = document.getElementById('room_block');
            var areaBlock = document.getElementById('area_block');
            var selectedRoomInfo = document.getElementById('selected_room_info');
            var selectedAreaInfo = document.getElementById('selected_area_info');
            var browseBtn = document.getElementById('browseBtn');
            var clearBtn = document.getElementById('clearBtn');
            var imageInput = document.getElementById('image_input');
            var imageName = document.getElementById('image_name');
            var imagePreview = document.getElementById('image_preview');
            var imagePreviewImg = imagePreview ? imagePreview.querySelector('img') : null;
            var form = document.querySelector('form');
            var panneTypeSelect = document.getElementById('panne_type_id');
            var descriptionTextarea = document.querySelector('textarea[name="description"]');

            // Vérifier que tous les éléments existent avant de les utiliser
            if (!browseBtn || !imageInput) {
                console.error('Éléments du formulaire manquants');
                return;
            }

            // Clés pour le sessionStorage
            var IMAGE_STORAGE_KEY = 'maintenance_panne_image_data';
            var FORM_STORAGE_KEY = 'maintenance_panne_form_data';

            // Fonction pour sauvegarder l'état complet du formulaire
            function saveFormState() {
                var formData = {
                    panne_type_id: panneTypeSelect ? panneTypeSelect.value : '',
                    location_type: form.querySelector('input[name="location_type"]:checked') ? form.querySelector('input[name="location_type"]:checked').value : '',
                    room_id: form.querySelector('input[name="room_id"]') ? form.querySelector('input[name="room_id"]').value : '',
                    maintenance_area_id: form.querySelector('input[name="maintenance_area_id"]') ? form.querySelector('input[name="maintenance_area_id"]').value : '',
                    description: descriptionTextarea ? descriptionTextarea.value : '',
                    timestamp: Date.now()
                };
                sessionStorage.setItem(FORM_STORAGE_KEY, JSON.stringify(formData));
            }

            // Fonction pour restaurer l'état du formulaire
            function restoreFormState() {
                try {
                    var stored = sessionStorage.getItem(FORM_STORAGE_KEY);
                    if (!stored) return;

                    var formData = JSON.parse(stored);

                    // Restaurer le type de panne
                    if (panneTypeSelect && formData.panne_type_id) {
                        panneTypeSelect.value = formData.panne_type_id;
                    }

                    // Restaurer le type de localisation
                    if (formData.location_type) {
                        var radio = form.querySelector('input[name="location_type"][value="' + formData.location_type + '"]');
                        if (radio) {
                            radio.checked = true;
                        }
                    }

                    // Restaurer la description
                    if (descriptionTextarea && formData.description) {
                        descriptionTextarea.value = formData.description;
                    }

                    // Restaurer les IDs cachés
                    if (formData.room_id) {
                        var roomInput = form.querySelector('input[name="room_id"]');
                        if (roomInput) roomInput.value = formData.room_id;
                    }
                    if (formData.maintenance_area_id) {
                        var areaInput = form.querySelector('input[name="maintenance_area_id"]');
                        if (areaInput) areaInput.value = formData.maintenance_area_id;
                    }

                } catch (e) {
                    console.error('Erreur lors de la restauration de l\'état du formulaire:', e);
                }
            }

            // Fonction pour sauvegarder l'image dans le sessionStorage
            function saveImageToStorage(file) {
                if (!file) {
                    sessionStorage.removeItem(IMAGE_STORAGE_KEY);
                    return;
                }
                var reader = new FileReader();
                reader.onload = function(e) {
                    var data = {
                        name: file.name,
                        type: file.type,
                        size: file.size,
                        dataUrl: e.target.result
                    };
                    sessionStorage.setItem(IMAGE_STORAGE_KEY, JSON.stringify(data));
                };
                reader.readAsDataURL(file);
            }

            // Fonction pour restaurer l'image depuis le sessionStorage
            function restoreImageFromStorage() {
                try {
                    var stored = sessionStorage.getItem(IMAGE_STORAGE_KEY);
                    if (!stored) return null;

                    var data = JSON.parse(stored);
                    if (!data || !data.dataUrl) return null;

                    // Convertir dataUrl en fichier
                    var response = fetch(data.dataUrl)
                        .then(function(res) { return res.blob(); })
                        .then(function(blob) {
                            return new File([blob], data.name, { type: data.type });
                        });
                    return { data: data, filePromise: response };
                } catch (e) {
                    console.error('Erreur lors de la restauration de l\'image:', e);
                    return null;
                }
            }

            // Fonction pour charger et afficher l'image restaurée
            function loadRestoredImage() {
                var restored = restoreImageFromStorage();
                if (restored && restored.filePromise) {
                    restored.filePromise.then(function(file) {
                        // Créer un DataTransfer pour simuler la sélection de fichier
                        var dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        imageInput.files = dataTransfer.files;

                        // Mettre à jour l'affichage
                        if (imageName) {
                            imageName.value = file.name;
                        }

                        // Afficher l'aperçu
                        if (imagePreview && imagePreviewImg) {
                            imagePreviewImg.src = restored.data.dataUrl;
                            imagePreview.classList.remove('d-none');
                            if (clearBtn) clearBtn.classList.remove('d-none');
                        }
                    });
                }
            }

            // Restaurer l'état du formulaire et l'image au chargement de la page
            restoreFormState();
            loadRestoredImage();

            // Sauvegarder l'état avant de naviguer vers les pages de sélection
            document.querySelectorAll('a[href*="select-room"], a[href*="select-area"]').forEach(function(link) {
                link.addEventListener('click', function() {
                    saveFormState();
                    if (imageInput.files[0]) {
                        saveImageToStorage(imageInput.files[0]);
                    }
                });
            });

            // Sauvegarder automatiquement l'état du formulaire lors des changements
            if (panneTypeSelect) {
                panneTypeSelect.addEventListener('change', saveFormState);
            }
            if (descriptionTextarea) {
                descriptionTextarea.addEventListener('input', saveFormState);
            }
            if (locRoom) {
                locRoom.addEventListener('change', saveFormState);
            }
            if (locArea) {
                locArea.addEventListener('change', saveFormState);
            }

            // Localisation toggle
            if (locRoom && locArea && roomBlock && areaBlock) {
                function toggle() {
                    var isRoom = locRoom.checked;
                    var hasRoomSelection = selectedRoomInfo !== null;
                    var hasAreaSelection = selectedAreaInfo !== null;

                    // Show/hide blocks based on selection and radio state
                    roomBlock.classList.toggle('d-none', !isRoom || hasRoomSelection);
                    areaBlock.classList.toggle('d-none', isRoom || hasAreaSelection);
                }
                locRoom.addEventListener('change', toggle);
                locArea.addEventListener('change', toggle);
                toggle();
            }

            // Handle radio button clicks to navigate to selection pages
            // Sauvegarder l'état avant la navigation
            if (locRoom) {
                locRoom.addEventListener('click', function(e) {
                    if (!selectedRoomInfo) {
                        e.preventDefault();
                        // Sauvegarder l'état complet avant la navigation
                        saveFormState();
                        if (imageInput.files[0]) {
                            saveImageToStorage(imageInput.files[0]);
                        }
                        window.location.href = '{{ route("maintenance.pannes.select-room", ["location_type" => "room", "return_to" => route("maintenance.pannes.create")]) }}';
                    }
                });
            }
            if (locArea) {
                locArea.addEventListener('click', function(e) {
                    if (!selectedAreaInfo) {
                        e.preventDefault();
                        // Sauvegarder l'état complet avant la navigation
                        saveFormState();
                        if (imageInput.files[0]) {
                            saveImageToStorage(imageInput.files[0]);
                        }
                        window.location.href = '{{ route("maintenance.pannes.select-area", ["location_type" => "area", "return_to" => route("maintenance.pannes.create")]) }}';
                    }
                });
            }

            // Fonction pour mettre à jour l'aperçu de l'image
            function updateImagePreview(file) {
                if (!imagePreview || !imagePreviewImg) return;

                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreviewImg.src = e.target.result;
                        imagePreview.classList.remove('d-none');
                        if (clearBtn) clearBtn.classList.remove('d-none');
                    };
                    reader.readAsDataURL(file);
                } else {
                    imagePreviewImg.src = '';
                    imagePreview.classList.add('d-none');
                    if (clearBtn) clearBtn.classList.add('d-none');
                }
            }

            // Bouton Parcourir - ouvrir le sélecteur de fichier
            browseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                imageInput.click();
            });

            // Bouton Supprimer - effacer la sélection
            if (clearBtn) {
                clearBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    imageInput.value = ''; // Effacer la sélection
                    if (imageName) {
                        imageName.value = '';
                        imageName.placeholder = 'Aucune image choisie';
                    }
                    updateImagePreview(null);
                    // Supprimer du sessionStorage
                    sessionStorage.removeItem(IMAGE_STORAGE_KEY);
                });
            }

            // Afficher le nom du fichier sélectionné et l'aperçu
            imageInput.addEventListener('change', function() {
                var file = this.files[0];
                if (imageName) {
                    imageName.value = file ? file.name : 'Aucune image choisie';
                }
                updateImagePreview(file);
                // Sauvegarder dans le sessionStorage
                if (file) {
                    saveImageToStorage(file);
                } else {
                    sessionStorage.removeItem(IMAGE_STORAGE_KEY);
                }
            });

            // Validation du formulaire avant soumission
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (imageInput.files.length === 0) {
                        e.preventDefault();
                        alert('Veuillez sélectionner une image pour la panne');
                        return false;
                    }
                    // Nettoyer le sessionStorage après soumission
                    sessionStorage.removeItem(IMAGE_STORAGE_KEY);
                    sessionStorage.removeItem(FORM_STORAGE_KEY);
                });
            }
        });
        </script>
    @endif
</x-app-layout>
