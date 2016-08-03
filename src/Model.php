<?php

namespace Ryuske\RedisModel;

use Ryuske\RedisModel\Contracts\RepositoryInterface;
use Ryuske\RedisModel\Exceptions\ResourceNotFound;
use Illuminate\Redis\Database as Redis;

/**
 * Allows you to implement Redis as a primary datastore easily
 *
 * // How the Redis database ends up looking
 *      users = 2
 *      user:1 {id: 1, name: 'Kenyon Haliwell', ...}
 *      user:2 {id: 2, name: 'Joe Bob', ...}
 *
 *      // index0 is whatever index is used for the unique identifier (typically, id)
 *      user:1:id_index1_index2 = 1
 *
 * // Methods & how to use them
 *      $user = $userModel->get(1, ['name']); // Returns a resource; the unique index is always included in fieldset
 *      $user->name = 'New Name';
 *      $user->save(); // Returns NULL
 *
 *      $users = $userModel->searchBy(['key' => 'value']); // Returns either a NULL, resource or a collection
 *      // If $users is a collection
 *          $users->count();
 *          $users[0]->name;
 *
 *      $user = $userModel->create([...]); // Returns a resource; All possible fields are included
 *      $user->delete(); // Returns NULL; Delete the user that was just created
 *
 *      $userModel->update(1, [...]); // Returns NULL
 *      $userModel->delete(1); // Returns NULL
 *
 * Class Model
 * @package Ryuske\RedisModel
 */
class Model extends DataAccessor implements RepositoryInterface
{

    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @var bool
     */
    private $guardHidden = true;

    /**
     * Searchable indexes; This order matters - if you change it later, your
     * already saved resources may no longer be searchable
     *
     * - The first index should be a integer, that is auto-increments
     *
     * @TODO write some kind if migration script to make it so you can change the order of this list
     *
     * @var array
     */
    protected $indexes = [
        'id',
    ];

    /**
     * Additional fields that are not indexes
     *
     * @var array
     */
    protected $fields = [

    ];

    /**
     * Fields that should never be returned
     *
     * @var array
     */
    protected $hiddenFields = [

    ];

    /**
     * @var array
     */
    protected $data = [

    ];

    /**
     * @var string
     */
    protected $hashName;

    /**
     * @var integer
     */
    protected $totalHashes;

    /**
     * RedisModel constructor.
     *
     * @param \Redis $redis
     */
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;

        $this->hashName = strtolower((new \ReflectionClass($this))->getShortName());
    }

    /**
     * Used to get the specified resource from the database
     *
     * @param $id
     * @param string $fields
     *
     * @return Model
     */
    public function get($id, $fields='all')
    {
        $fields = $this->getFieldsForGet($fields);

        $data = $this->redis->hMGet($this->getHash($id), $fields);
        if (NULL === $data[0]) {
            return NULL;
        }

        $this->data = array_combine($fields, $data);

        return clone $this;
    }

    /**
     * Used to get the specified resource from the database
     * If not found, throw an exception
     *
     * @param $id
     * @param string $fields
     *
     * @return Model
     * @throws ResourceNotFound
     */
    public function getOrFail($id, $fields='all')
    {
        $resource = $this->get($id, $fields);

        if (NULL === $resource) {
            throw new ResourceNotFound(ucwords($this->hashName) . ' not found in database.');
        }

        return $resource;
    }

    /**
     * Returns the resources found via the $data parameter
     * HOWEVER, it does NOT allow wildcard searches
     *
     * @param $data
     * @param string $fields
     *
     * @return Collection|Model|null
     */
    public function searchBy($data, $fields='all')
    {
        foreach ($data as $key=>$value) {
            $data[$key] = str_replace('*', '', $value);
        }

        return $this->searchByWildcard($data, $fields);
    }

    /**
     * Returns the resources found via the $data parameter
     * HOWEVER, it DOES allow wildcard searches
     *
     * @param $data
     * @param string $fields
     *
     * @return Collection|Model|null
     */
    public function searchByWildcard($data, $fields='all')
    {
        $fields = $this->getFieldsForGet($fields);

        /**
         * If $this->indexes looks like ['id', 'email', 'password']
         * and $data looks like ['name' => 'kenyon.jh@gmail.com']
         * then $indexes will end up looking something like: **_kenyon.jh@gmail.com_*
         */
        $indexes = array_fill_keys($this->indexes, '*');
        foreach ($data as $index=>$value) {
            $indexes[$index] = str_replace(' ', '+', strtolower($value));
        }
        $indexes    = $this->hashName . ':*' . implode('_', $indexes);
        $foundKeys  = [];

        /**
         * Redis only shows X entries at a time, and then gives the
         * cursor position of the next X. This is to prevent blocking
         * queries since it is single-threaded
         */
        do {
            $cursorIndex = (!isset($redisScan)) ? 0 : $redisScan[0];
            $redisScan = $this->redis->scan($cursorIndex, 'match', $indexes, 'count', 500);
            $foundKeys = array_merge($redisScan[1], $foundKeys);
        } while ($redisScan[0] != 0);

        switch (count($foundKeys)) {
            case 0: // If no keys were found, return NULL
                return NULL;
            case 1: // If only 1 match was found, return an instance of it
                $key = $this->redis->get($foundKeys[0]);
                return $this->get($key, $fields);
            default: // If multiple matches were found, return a collection of all of them
                $results = [];
                foreach ($foundKeys as $key) {
                    $key = $this->redis->get($key);
                    $results[] = $this->get($key, $fields);
                }

                return new Collection($results);
        }
    }

    /**
     * Used to create a new resource in the database
     *
     * @param $keyValueArray
     *
     * @return Model
     */
    public function create($keyValueArray)
    {
        $allFields      = $this->allAvailableFields();

        // Create an array using all the possible fields as keys, and set them to null
        $allFields      = array_fill_keys($allFields, NULL);

        // Find the possible fields that are missing from the supplied list of explicitly defined key => value pairs
        $missingFields  = array_diff_key($allFields, $keyValueArray);

        // Add all the missing fields to the supplied key => value pairs
        $keyValueArray  = array_merge($missingFields, $keyValueArray);

        $id     = $this->generateNewId();
        $hashId = $this->getHash($id);
        $keyValueArray[$this->idFieldName()] = $id;

        // Set the actual key => values in the database, under the generated id (which is something like user:1)
        if ($this->redis->hMset($hashId, $keyValueArray)) {

            // Assuming nothing went wrong, set this ID as the latest one of the given resource type
            $this->saveGeneratedId();
        };

        $this->saveSearchableKey($id, $keyValueArray);

        $this->data = $keyValueArray;

        return clone $this;
    }

    /**
     * Used to update the given resource in the database
     *
     * @param $id
     * @param $keyValueArray
     */
    public function update($id, $keyValueArray)
    {
        $this->guardHidden = false;
        $resource = $this->get($id);
        $this->guardHidden = true;

        $this->renameSearchableKey($id, $resource->toArray(), $keyValueArray);

        $this->redis->hMset($this->getHash($id), $keyValueArray);

        $this->data = array_merge($this->data, $keyValueArray);
    }

    /**
     * Used to delete the given resource from the database
     *
     * @param null $id
     *
     * @return null|void
     */
    public function delete($id=NULL)
    {
        if (NULL === $id) {
            if (is_array($this->data) && array_key_exists($this->idFieldName(), $this->data)) {
                $id = $this->data[$this->idFieldName()];
            } else {
                return NULL;
            }
        }

        $this->guardHidden = false;
        $resource = $this->get($id);
        $this->guardHidden = true;

        if (!$resource) {
            throw new ResourceNotFound(ucwords($this->hashName) . ' not found in database.');
        }

        // Delete the resource
        $this->redis->del($this->getHash($id));

        // Delete the searchable key
        $this->redis->del($this->getSearchableKey($id, $resource->toArray()));
    }

    /**
     * Used to get the name of a hashset based on the given id
     *
     * @param $id
     *
     * @return string
     */
    protected function getHash($id)
    {
        return "$this->hashName:$id";
    }

    /**
     * Get the next unique id for the given resource type
     *
     * @return integer
     */
    protected function generateNewId()
    {
        $this->totalHashes = $this->redis->get(str_plural($this->hashName));
        $this->totalHashes++;

        return $this->totalHashes;
    }

    /**
     * Update the total number of resources created for a specific type of resource
     *
     * @return bool
     */
    protected function saveGeneratedId()
    {
        return $this->redis->set(str_plural($this->hashName), $this->totalHashes);
    }

    /**
     * Creates a key based on $this->indexes - used to create a searchable key that
     * points to the resource that it is based on
     *
     * @param $id
     * @param $keyValueArray
     *
     * @return string
     */
    protected function getSearchableKey($id, $keyValueArray)
    {
        /**
         * This block generates the "searchable" key value pair based on the given indexes.
         * Takes the supplied values in the $keyValueArray and matches them to the corresponding
         * index, transforming them into a string separated by underscores. The ultimate string
         * is preceded by the hashId, and the whole key is equal to the id of the actual resource.
         *
         * Spaces are converted to + & all strings are converted to lowercase
         *
         * So an outputted string could look like: user:1:kenyon.jh@gmail.com_kenyon+haliwell
         */

        $hashId = $this->getHash($id);
        $indexes = array_fill_keys($this->indexes, '');
        foreach ($indexes as $index=>&$value) {
            $value = str_replace(' ', '+', strtolower($keyValueArray[$index]));
        }
        $indexes = implode('_', $indexes);

        return "$hashId:$indexes";
    }

    /**
     * Set the key generated in getSearchableKey to the ID of the resource in Redis
     *
     * @param $id
     * @param $keyValueArray
     */
    protected function saveSearchableKey($id, $keyValueArray)
    {
        $this->redis->set($this->getSearchableKey($id, $keyValueArray), $id);
    }

    /**
     * Rename the searchable string for a specific resource
     * - This happens if you update a resource, and the values of the indexes change, for example
     *
     * @param $id
     * @param $originalKeyValue
     * @param $newKeyValue
     */
    protected function renameSearchableKey($id, $originalKeyValue, $newKeyValue)
    {
        $originalSearchableKey  = $this->getSearchableKey($id, $originalKeyValue);
        $newSearchableKey       = $this->getSearchableKey($id, array_merge($originalKeyValue, $newKeyValue));

        if ($originalSearchableKey !== $newSearchableKey) {
            $this->redis->rename($originalSearchableKey, $newSearchableKey);
        }
    }

    /**
     * Merges the available searchable indexes with the regular fields
     *
     * @return array
     */
    public function allAvailableFields()
    {
        return array_merge($this->indexes, $this->fields);
    }

    /**
     * Removes fields that are specified as hidden from an array of wanted fields
     *
     * @param $fields
     *
     * @return array
     */
    public function removeHiddenFields($fields)
    {
        return ($this->guardHidden) ? array_diff($fields, $this->hiddenFields) : $fields;
    }

    /**
     * Returns an array of fields usable by $this->get()
     *
     * @param $fields
     *
     * @return array
     */
    public function getFieldsForGet($fields)
    {
        if ('all' === $fields) {
            $fields = $this->allAvailableFields();
        } else if (!array_key_exists($this->idFieldName(), $fields)) {
            array_push($fields, $this->idFieldName());
        }

        return $this->removeHiddenFields($fields);
    }

    /**
     * Returns the name of the primary ID field
     *
     * @return string
     */
    public function idFieldName()
    {
        return $this->indexes[0];
    }
}