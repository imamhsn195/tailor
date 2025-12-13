<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait HandlesSoftDeletes
{
    /**
     * Get the query builder for soft deletes
     *
     * @return Builder
     */
    protected function softDeleteQuery(): Builder
    {
        $modelClass = $this->getModelClass();
        return $modelClass::onlyTrashed();
    }

    /**
     * Get the model class name
     *
     * @return string
     */
    abstract protected function getModelClass(): string;

    /**
     * Get the soft delete route prefix
     *
     * @return string
     */
    protected function softDeleteRoutePrefix(): string
    {
        return 'trash';
    }

    /**
     * Get soft deleted models
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function trash(Request $request)
    {
        $query = $this->softDeleteQuery();

        // Apply filters if needed
        if (method_exists($this, 'applyFilters')) {
            $query = $this->applyFilters($query, $request);
        }

        $items = $query->paginate(10);

        return view($this->getTrashView(), [
            'items' => $items,
        ]);
    }

    /**
     * Restore a soft deleted model
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($id)
    {
        $model = $this->softDeleteQuery()->findOrFail($id);
        $model->restore();

        return redirect()
            ->route($this->getIndexRoute())
            ->with('success', trans_common('restored_successfully'));
    }

    /**
     * Permanently delete a model
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forceDelete($id)
    {
        $model = $this->softDeleteQuery()->findOrFail($id);
        $model->forceDelete();

        return redirect()
            ->route($this->getTrashRoute())
            ->with('success', trans_common('deleted_permanently'));
    }

    /**
     * Get the trash view name
     *
     * @return string
     */
    protected function getTrashView(): string
    {
        $viewPrefix = $this->getViewPrefix();
        return "{$viewPrefix}.trash";
    }

    /**
     * Get the view prefix (e.g., 'admin.orders')
     *
     * @return string
     */
    abstract protected function getViewPrefix(): string;

    /**
     * Get the index route name
     *
     * @return string
     */
    abstract protected function getIndexRoute(): string;

    /**
     * Get the trash route name
     *
     * @return string
     */
    abstract protected function getTrashRoute(): string;
}

