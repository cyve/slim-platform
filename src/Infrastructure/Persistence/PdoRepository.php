<?php

namespace SlimPlatform\Infrastructure\Persistence;

use Doctrine\Inflector\InflectorFactory;
use SlimPlatform\Domain\Repository;

class PdoRepository implements Repository
{
    public function __construct(
        private \PDO $pdo,
        private string $table,
    ) {
    }

    public function getAll(): array
    {
        $query = $this->pdo->prepare('SELECT * FROM `'.$this->table.'`');
        $query->execute();

        $results = $query->fetchAll(\PDO::FETCH_OBJ) ?: [];

        return array_map([$this, 'normalize'], $results);
    }

    public function get(int $id): object|null
    {
        $query = $this->pdo->prepare('SELECT * FROM `'.$this->table.'` WHERE id = :id');
        $query->execute(['id' => $id]);

        $result = $query->fetch(\PDO::FETCH_OBJ) ?: null;

        if (empty($result)) {
            return null;
        }

        return $this->normalize($result);
    }

    public function has(int $id): bool
    {
        $query = $this->pdo->prepare('SELECT COUNT(id) FROM `'.$this->table.'` WHERE id = :id');
        $query->execute(['id' => $id]);

        return (bool) $query->fetch(\PDO::FETCH_COLUMN);
    }

    public function create(array $data): int
    {
        $data = $this->filterData($data);
        $data = $this->denormalize($data);

        $properties = array_keys($data);
        $columns = array_map(fn ($property) => sprintf('`%s`', $property), $properties);
        $aliases = array_map(fn ($property) => sprintf(':%s', $property), $properties);

        $query = $this->pdo->prepare('INSERT INTO `'.$this->table.'` ('.implode(', ', $columns).') VALUES ('.implode(',', $aliases).')');
        $query->execute($data);

        return $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data = $this->filterData($data);
        $data = $this->denormalize($data);

        $properties = array_keys($data);
        $expressions = array_map(fn ($property) => sprintf('`%s`=:%s', $property, $property), $properties);

        $query = $this->pdo->prepare('UPDATE `'.$this->table.'` SET '.implode(',', $expressions).' WHERE id = :id');
        $query->execute(['id' => $id] + $data);
    }

    public function delete(int $id): void
    {
        $query = $this->pdo->prepare('DELETE FROM `'.$this->table.'` WHERE id = :id');
        $query->execute(['id' => $id]);
    }

    private function getSchema(): object
    {
        static $schema;
        static $inflector;

        if (empty($schema)) {
            $query = $this->pdo->prepare('SHOW FULL FIELDS FROM '.$this->table);
            $query->execute();
            $fields = array_column($query->fetchAll(\PDO::FETCH_OBJ), null, 'Field');

            $schema = (object) [
                'table' => $this->table,
                'fields' => [],
                'foreignKeys' => [],
            ];
            foreach ($fields as $fieldName => $field) {
                $schema->fields[$fieldName] = (object) [
                    'type' => match (true) {
                        str_starts_with($field->Type, 'varchar') || $field->Type === 'text' => 'string',
                        $field->Type === 'tinyint(1)' => 'bool',
                        default => $field->Type,
                    },
                    'length' => match (true) {
                        (bool) preg_match('/^varchar\((\d+)\)$/', $field->Type, $matches) => (int) $matches[1],
                        default => null,
                    },
                    'nullable' => $field->Null === 'YES',
                    'required' => $field->Null === 'NO' && $field->Default === null && $field->Extra !== 'auto_increment',
                ];

                if ($field->Key === 'MUL') {
                    $query = $this->pdo->prepare('SELECT REFERENCED_TABLE_NAME as "table",REFERENCED_COLUMN_NAME as "column" FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = (SELECT DATABASE()) AND TABLE_NAME = "'.$this->table.'" AND `COLUMN_NAME` = "'.$field->Field.'"');
                    $query->execute();
                    $relation = $query->fetch(\PDO::FETCH_OBJ);
                    $schema->foreignKeys[$field->Field] = $relation;

                    $inflector ??= InflectorFactory::create()->build();
                    $schema->foreignKeys[$field->Field]->resourcePath = sprintf('/%s', $inflector->pluralize($relation->table));
                }
            }
        }

        return $schema;
    }

    private function filterData(array $data): array
    {
        unset($data['id']);

        $schema = $this->getSchema();

        return array_filter($data, fn (string $property) => isset($schema->fields[$property]), \ARRAY_FILTER_USE_KEY);
    }

    private function normalize(object $data): object
    {
        $schema = $this->getSchema();
        foreach ($data as $property => &$value) {
            if ($value === null) {
                continue;
            }
            if ($schema->fields[$property]->type === 'json') {
                $value = json_decode($value);
            }
            if ($schema->fields[$property]->type === 'bool') {
                $value = (bool) $value;
            }
            if ($foreignKey = $schema->foreignKeys[$property] ?? null) {
                $value = sprintf('%s/%s', $foreignKey->resourcePath, $value);
            }
        }

        return $data;
    }

    private function denormalize(array $data): array
    {
        $schema = $this->getSchema();
        foreach ($data as $property => &$value) {
            if ($value === null) {
                continue;
            }
            if ($schema->fields[$property]->type === 'json') {
                $value = json_encode($value);
            }
            if ($foreignKey = $schema->foreignKeys[$property] ?? null) {
                [$path, $id] = explode('/', trim($value, '/'));
                $value = $id;
            }
        }

        return $data;
    }
}
