@extends('layouts.app')

@section('title', 'Gestion des Imprimantes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-print"></i> Gestion des Imprimantes
                    </h3>
                    <div class="btn-group">
                        <a href="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.create') : route('hotel.printers.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Ajouter Imprimante
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
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

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    @if(auth()->user()->hasRole('super-admin'))
                                        <th>Hôtel</th>
                                    @endif
                                    <th>Nom</th>
                                    <th>Adresse</th>
                                    <th>Type</th>
                                    <th>Statut</th>
                                    <th style="width: 200px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($printers as $printer)
                                    <tr>
                                        @if(auth()->user()->hasRole('super-admin'))
                                            <td>
                                                @if($printer->hotel)
                                                    <span class="badge bg-info">{{ $printer->hotel->name }}</span>
                                                @else
                                                    <span class="badge bg-secondary">Global</span>
                                                @endif
                                            </td>
                                        @endif
                                        <td>
                                                    <strong>{{ $printer->name }}</strong>
                                        </td>
                                        <td>
                                            <code>{{ $printer->ip_address }}:{{ $printer->port ?? 9100 }}</code>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $printer->type === 'ticket' ? 'info' : 'warning' }}">
                                                {{ $printer->type === 'ticket' ? 'Ticket' : 'A4' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $printer->is_active ? 'success' : 'danger' }}">
                                                {{ $printer->is_active ? 'En ligne' : 'Hors ligne' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.test', $printer) : route('hotel.printers.test', $printer) }}" 
                                                   class="btn btn-sm btn-outline-info" 
                                                   title="Tester connexion">
                                                    <i class="bi bi-wifi"></i>
                                                </a>
                                                
                                                <a href="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.print-test', $printer) : route('hotel.printers.print-test', $printer) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Test impression">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                                
                                                <a href="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.edit', $printer) : route('hotel.printers.edit', $printer) }}" 
                                                   class="btn btn-sm btn-outline-warning" title="Modifier">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                <form action="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.destroy', $printer) : route('hotel.printers.destroy', $printer) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette imprimante ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()->hasRole('super-admin') ? '6' : '5' }}" class="text-center text-muted py-4">
                                            <i class="bi bi-printer" style="font-size: 2rem;"></i>
                                            <br>Aucune imprimante configurée
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection