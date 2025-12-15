@extends('layouts.app')

@section('title', 'Modifier une Imprimante')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit"></i> Modifier l'Imprimante : {{ $printer->name }}
                    </h3>
                </div>
                
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-x-circle me-2"></i>
                            <strong>Erreur :</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.update', $printer) : route('hotel.printers.update', $printer) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        @if(auth()->user()->hasRole('super-admin') && isset($hotels))
                            <div class="mb-3">
                                <label for="hotel_id" class="form-label">Hôtel</label>
                                <select class="form-select" id="hotel_id" name="hotel_id" required>
                                    <option value="">Sélectionner un hôtel</option>
                                    @foreach($hotels as $hotel)
                                        <option value="{{ $hotel->id }}" {{ (old('hotel_id', $printer->hotel_id) == $hotel->id) ? 'selected' : '' }}>
                                            {{ $hotel->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom de l'imprimante</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $printer->name) }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Type d'imprimante</label>
                                    <select class="form-select" id="type" name="type" required>
                                        <option value="">Sélectionner le type</option>
                                        <option value="ticket" {{ old('type', $printer->type) == 'ticket' ? 'selected' : '' }}>Ticket (Thermique)</option>
                                        <option value="a4" {{ old('type', $printer->type) == 'a4' ? 'selected' : '' }}>A4 (Laser/Jet d'encre)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ip_address" class="form-label">Adresse IP</label>
                                    <input type="text" class="form-control" id="ip_address" name="ip_address" value="{{ old('ip_address', $printer->ip_address) }}" required>
                                    <div class="form-text">Exemple: 192.168.1.100</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="port" class="form-label">Port</label>
                                    <input type="number" class="form-control" id="port" name="port" value="{{ old('port', $printer->port) }}" min="1" max="65535" required>
                                    <div class="form-text">Port par défaut: 9100</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $printer->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Imprimante active
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.index') : route('hotel.printers.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection