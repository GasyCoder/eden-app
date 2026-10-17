<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Actions groupées sur les lignes cochées d'un tableau d'administration.
 * Le contrôleur décrit ses actions : nom => [message de succès (%d = nombre), traitement].
 */
trait HandlesBulkActions
{
    /** @return class-string<Model> */
    abstract protected function bulkModel(): string;

    /** @return array<string, array{0:string, 1:callable(Collection):void}> */
    abstract protected function bulkActions(): array;

    public function bulk(Request $request): RedirectResponse
    {
        $actions = $this->bulkActions();

        $data = $request->validate([
            'action' => ['required', Rule::in(array_keys($actions))],
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer'],
        ], ['ids.required' => 'Sélectionnez au moins une ligne.']);

        $models = ($this->bulkModel())::query()->whereKey($data['ids'])->get();
        [$message, $handler] = $actions[$data['action']];

        DB::transaction(fn () => $handler($models));

        return back(303)->with('success', sprintf($message, $models->count()));
    }

    /** Met à jour chaque modèle (déclenche les événements Eloquent). */
    protected function updateEach(Collection $models, array|callable $attributes): void
    {
        $models->each(fn ($model) => $model->update(is_callable($attributes) ? $attributes($model) : $attributes));
    }

    protected function deleteEach(Collection $models): void
    {
        $models->each->delete();
    }
}
