<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Deputy;
use App\Domain\Repositories\DeputyRepositoryInterface;
use App\Models\Deputy as DeputyModel;
use Illuminate\Support\Collection;

class DeputyRepository implements DeputyRepositoryInterface
{
    public function findById(int $id): ?Deputy
    {
        $model = DeputyModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->modelToEntity($model);
    }

    public function getAll(): Collection
    {
        return DeputyModel::all()
            ->map(fn(DeputyModel $model) => $this->modelToEntity($model));
    }

    public function getActive(): Collection
    {
        return DeputyModel::whereNotNull('name')
            ->whereNotNull('party_abbr')
            ->whereNotNull('state_abbr')
            ->get()
            ->map(fn(DeputyModel $model) => $this->modelToEntity($model));
    }

    public function findByParty(string $partyAbbr): Collection
    {
        return DeputyModel::where('party_abbr', $partyAbbr)
            ->get()
            ->map(fn(DeputyModel $model) => $this->modelToEntity($model));
    }

    public function findByState(string $stateAbbr): Collection
    {
        return DeputyModel::where('state_abbr', $stateAbbr)
            ->get()
            ->map(fn(DeputyModel $model) => $this->modelToEntity($model));
    }

    public function getWithExpenses(): Collection
    {
        return DeputyModel::with('expenses')
            ->get()
            ->map(fn(DeputyModel $model) => $this->modelToEntity($model));
    }

    public function save(Deputy $deputy): void
    {
        $model = isset($deputy->id)
            ? DeputyModel::find($deputy->id) ?? new DeputyModel()
            : new DeputyModel();
        $model->name = $deputy->nome;
        $model->party_abbr = $deputy->siglaPartido;
        $model->state_abbr = $deputy->siglaUf;
        $model->uri = $deputy->uri ?? null;
        $model->party_uri = $deputy->uriPartido ?? null;
        $model->legislature_id = $deputy->idLegislatura ?? null;
        $model->photo_url = $deputy->urlFoto ?? null;
        $model->email = $deputy->email ?? null;

        $model->save();

        if (!isset($deputy->id)) {
            $deputy->id = $model->id;
        }
    }

    private function modelToEntity(DeputyModel $model): Deputy
    {
        $deputy = new Deputy();

        $deputy->id = $model->id;
        $deputy->uri = $model->uri ?? '';
        $deputy->nome = $model->name ?? '';
        $deputy->siglaPartido = $model->party_abbr ?? '';
        $deputy->uriPartido = $model->party_uri ?? '';
        $deputy->siglaUf = $model->state_abbr ?? '';
        $deputy->idLegislatura = $model->legislature_id ?? '';
        $deputy->urlFoto = $model->photo_url ?? '';
        $deputy->email = $model->email ?? '';

        return $deputy;
    }
}
