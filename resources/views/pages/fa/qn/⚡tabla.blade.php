<?php

use App\Models\Evento;
use App\Models\Pronostico;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public Evento $evento;
    public int $ronda;
    public $juegos;
    public $participaciones;
    public array $pronosticosPorCelda = [];

    public function mount(Evento $evento): void
    {
        if (Gate::forUser(auth()->user())->denies('view', $evento)) {
            $this->redirectRoute('evento.show', ['evento' => $evento]);

            return;
        }

        $this->evento = $evento;
        $this->ronda = (int) (request()->query('rd') ?? $evento->temporada->ronda);

        $this->cargarTabla();
    }

    #[On('ronda-seleccionada')]
    public function actualizarRonda($ronda): void
    {
        $this->ronda = (int) $ronda;
        $this->redirectRoute('fa.qn.tabla', ['evento' => $this->evento, 'rd' => $this->ronda]);
    }

    protected function cargarTabla(): void
    {
        $this->juegos = $this->evento
            ->temporada
            ->juegos()
            ->where('ronda', $this->ronda)
            ->with(['homeTeam', 'awayTeam'])
            ->orderBy('valido_hasta')
            ->orderBy('id')
            ->get();

        $this->participaciones = $this->evento
            ->participaciones()
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        $this->pronosticosPorCelda = [];

        if ($this->juegos->isEmpty() || $this->participaciones->isEmpty()) {
            return;
        }

        $pronosticos = Pronostico::query()
            ->whereIn('juego_id', $this->juegos->pluck('id'))
            ->whereIn('participacion_id', $this->participaciones->pluck('id'))
            ->get(['participacion_id', 'juego_id', 'diferencia', 'res', 'dif']);

        foreach ($pronosticos as $pronostico) {
            $this->pronosticosPorCelda[$pronostico->participacion_id][$pronostico->juego_id] = [
                'diferencia' => $pronostico->diferencia,
                'res' => $pronostico->res,
                'dif' => $pronostico->dif,
            ];
        }
    }

    public function juegoIniciado($juego): bool
    {
        return (bool) ($juego->valido_hasta?->lte(now()));
    }

    public function juegoCalificado($juego): bool
    {
        $status = strtolower((string) $juego->status);

        return in_array($status, ['ft', 'match finished', ''], true);
    }

    public function logoPronosticado($juego, ?array $pronostico): ?string
    {
        if (! $pronostico || $pronostico['diferencia'] === null) {
            return null;
        }

        $diferencia = (int) $pronostico['diferencia'];

        if ($diferencia > 0) {
            return $juego->homeTeam?->logo;
        }

        if ($diferencia < 0) {
            return $juego->awayTeam?->logo;
        }

        return null;
    }

    public function claseResultado(?int $res, ?int $dif): string
    {
        if ($res === null || $dif === null) {
            return 'bg-base-200';
        }

        $score = $res + $dif;

        if ($score > 1) {
            return 'bg-success/30';
        }

        if ($score > 0) {
            return 'bg-warning/30';
        }

        return 'bg-error/25';
    }
};
?>

<div>
    <x-title title="{{ $evento->nombre }}" subtitle="Tabla de Picks" />

    <livewire:nav-evento :evento="$evento" :key="'nav-evento-' . $evento->id" opc="6" />
    <livewire:selector-rondas :model="$evento" :ronda="$ronda" :key="'selector-ronda-' . $evento->id" />

    @if ($juegos->isEmpty())
        <x-alert
            title="Sin juegos"
            description="No hay juegos cargados para la ronda {{ $ronda }}."
            icon="fas.circle-info"
            class="alert-info"
            />
    @elseif ($participaciones->isEmpty())
        <x-alert
            title="Sin participaciones"
            description="No hay participaciones registradas en este evento."
            icon="fas.circle-info"
            class="alert-info"
            />
    @else
        <div class="mb-3 mt-2 flex items-center gap-3 text-xs md:text-sm">
            <span class="badge badge-neutral">No iniciado</span>
            <span class="badge bg-base-200 border-base-300">En juego / sin calificar</span>
            <span class="badge badge-success">Acierto total</span>
            <span class="badge badge-warning">Acierto parcial</span>
            <span class="badge badge-error">Fallo</span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-base-300 bg-base-100 shadow">
            <table class="table table-pin-rows w-full border-separate border-spacing-0">
                <thead>
                    <tr>
                        <th class="sticky left-0 z-30 w-24 border-b border-r border-base-300 bg-base-200">Participaciones</th>

                        @foreach ($juegos as $juego)
                            <th class="w-24 min-w-24 border-b border-base-300 bg-base-200 px-2 py-2 text-center">
                                <div class="flex flex-col items-center justify-center gap-1">
                                    <img src="{{ $juego->awayTeam->logo }}" alt="{{ $juego->awayTeam->nombre }}" class="h-7 w-7 object-contain">
                                    <img src="{{ $juego->homeTeam->logo }}" alt="{{ $juego->homeTeam->nombre }}" class="h-7 w-7 object-contain">
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @foreach ($participaciones as $participacion)
                        <tr>
                            <th class="sticky left-0 z-20 border-b border-r border-base-300 bg-base-100 font-medium whitespace-nowrap max-w-16 overflow-hidden text-ellipsis px-1 w-16">
                                {{ $participacion->nombre }}
                            </th>

                            @foreach ($juegos as $juego)
                                @php
                                    $pronostico = $pronosticosPorCelda[$participacion->id][$juego->id] ?? null;
                                    $iniciado = $this->juegoIniciado($juego);
                                    $calificado = $this->juegoCalificado($juego);
                                    $logo = $this->logoPronosticado($juego, $pronostico);
                                    $sinPick = ! $pronostico || $pronostico['diferencia'] === null;
                                    $clase = $iniciado
                                            ? $this->claseResultado($pronostico['res'] ?? null, $pronostico['dif'] ?? null)
                                            : 'bg-base-100';

                                    if ($iniciado && $sinPick && $calificado) {
                                        $clase = 'bg-error/25';
                                    }
                                @endphp

                                <td class="w-24 min-w-24 border-b border-base-200 p-0 text-center align-middle">
                                    <div class="flex h-16 w-full items-center justify-center {{ $clase }}">
                                        @if (! $iniciado)
                                            <span class="text-sm font-semibold text-base-content/60">???</span>
                                        @elseif ($sinPick && $calificado)
                                            <span class="text-sm font-semibold">NO PICK</span>
                                        @elseif ($logo)
                                            <img src="{{ $logo }}" class="h-8 w-8 object-contain" alt="Pick {{ $participacion->nombre }}">
                                        @else
                                            <span class="text-sm font-semibold text-base-content/60">???</span>
                                        @endif
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>