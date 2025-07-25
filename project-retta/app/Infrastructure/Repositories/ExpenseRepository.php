<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Expense;
use App\Domain\Repositories\ExpenseRepositoryInterface;
use App\Infrastructure\Models\ExpenseModel;
use Illuminate\Support\Collection;

class ExpenseRepository implements ExpenseRepositoryInterface
{
    public function findById(int $id): ?Expense
    {
        $model = ExpenseModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->modelToEntity($model);
    }

    public function findByUniqueFields(
        int $deputyId,
        int $documentCode,
        ?int $reimbursementNumber = null
    ): ?Expense {
        $query = ExpenseModel::where('deputy_id', $deputyId)
            ->where('document_code', $documentCode);

        if ($reimbursementNumber !== null) {
            $query->where('reimbursement_number', $reimbursementNumber);
        } else {
            $query->whereNull('reimbursement_number');
        }

        $model = $query->first();

        return $model ? $this->modelToEntity($model) : null;
    }

    public function findByDeputyId(int $deputyId): Collection
    {
        return ExpenseModel::where('deputy_id', $deputyId)
            ->get()
            ->map(fn($model) => $this->modelToEntity($model));
    }

    public function findByPeriod(int $year, int $month): Collection
    {
        return ExpenseModel::where('year', $year)
            ->where('month', $month)
            ->get()
            ->map(fn($model) => $this->modelToEntity($model));
    }

    public function deleteOlderThan(\DateTime $date): int
    {
        return ExpenseModel::where('created_at', '<', $date)->delete();
    }
}
