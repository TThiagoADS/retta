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
            ->map(fn($model) => $this->modelToEntity($model));
    }

    public function getActive(): Collection
    {
        return DeputyModel::whereNotNull('name')
            ->whereNotNull('party_abbr')
            ->whereNotNull('state_abbr')
            ->get()
            ->map(fn($model) => $this->modelToEntity($model));
    }

    public function findByParty(string $partyAbbr): Collection
    {
        return DeputyModel::where('party_abbr', $partyAbbr)
            ->get()
            ->map(fn($model) => $this->modelToEntity($model));
    }

    public function findByState(string $stateAbbr): Collection
    {
        return DeputyModel::where('state_abbr', $stateAbbr)
            ->get()
            ->map(fn($model) => $this->modelToEntity($model));
    }
}
