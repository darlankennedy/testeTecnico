<?php

namespace App\interface;

interface BaseRepositoryInterface
{
    public function all($columns = array('*'), $relations = array(),$conditions = array());
    public function create(array $data);
    public function update(array $data, $id, String $attribute = "id");
    public function delete($id);
    public function findBy($attribute, $value, $columns = array('*'), $relations = array());
    public function find($id, $columns = array('*'), $with = array());
    public function query(string $rawQuery, array $bindings = []);
    public function paginate(
        int    $perPage = 15,
        array  $filters = [],
        array  $with = [],
        array $conditions = [],
        string $orderBy = 'id',
        string $direction = 'asc'
    );

}
