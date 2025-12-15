@extends('layouts.app')

@section('title', 'Test d\'Impression')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-print"></i> Test d'Impression - {{ $printer->name }}
                    </h3>
            </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Informations de l'imprimante</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Nom:</strong></td>
                                    <td>{{ $printer->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Adresse:</strong></td>
                                    <td><code>{{ $printer->ip_address }}:{{ $printer->port ?? 9100 }}</code></td>
                                </tr>
                                <tr>
                                    <td><strong>Type:</strong></td>
                                    <td><span class="badge bg-{{ $printer->type === 'ticket' ? 'info' : 'warning' }}">{{ strtoupper($printer->type) }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Date du test:</strong></td>
                                    <td>{{ $test_date->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </table>
            </div>

                        <div class="col-md-6">
                            <h5>Résultat du test</h5>
                            <div class="alert alert-{{ $success ? 'success' : 'danger' }}">
                                <h6 class="alert-heading">
                                    <i class="fas fa-{{ $success ? 'check-circle' : 'times-circle' }}"></i>
                                    {{ $message }}
                                </h6>
                                @if(!empty($details))
                                    <ul class="mb-0">
                                    @foreach($details as $detail)
                                            <li>{{ $detail }}</li>
                                    @endforeach
                                </ul>
                                @endif
                            </div>
                        </div>
                        </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.index') : route('hotel.printers.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Retour à la liste
                        </a>
                        <div>
                                <a href="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.print-test', $printer) : route('hotel.printers.print-test', $printer) }}" 
                               class="btn btn-primary">
                                <i class="fas fa-redo"></i> Tester à nouveau
                            </a>
                                <a href="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.test', $printer) : route('hotel.printers.test', $printer) }}" 
                               class="btn btn-outline-info">
                                <i class="fas fa-wifi"></i> Test de connexion
                                </a>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection