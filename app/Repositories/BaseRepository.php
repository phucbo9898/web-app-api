<?php

namespace App\Repositories;

use App\Repositories\Interfaces\BaseRepositoryInterface;

abstract class BaseRepository extends BaseService implements BaseRepositoryInterface
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function create(array $data)
    {
        // TODO: Implement create() method.
        return $this->model->create($data);
    }

    public function all()
    {
        // TODO: Implement all() method.
        return $this->model->all();
    }

    public function insert(array $data)
    {
        return $this->model->insert($data);
    }

    public function update($id, array $data)
    {
        // TODO: Implement update() method.
        $oldData = $this->model->find($id);
        if ($oldData) {
            $oldData->update($data);

            return $oldData;
        }

        return false;
    }

    // Delete data in the database
    public function delete($id)
    {
        return $this->model->find($id)->delete();
    }

    // Detail of data in the database
    public function show($id)
    {
        return $this->model->findOrFail($id);
    }

    // Detail of data in the database
    public function find($id)
    {
        return $this->model->find($id);
    }

    // Search data in the database
    public function search($fieldName, $param)
    {
        return $this->model->where($fieldName, 'LIKE', '%' . $param . '%');
    }

    // Get the associated model
    public function getModel()
    {
        return $this->model;
    }

    public function take($amount)
    {
        return $this->model->take($amount);
    }
}
