<?php

namespace SlimPlatform\Infrastructure\Persistence;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;

class Config extends \ArrayObject
{
    public function __construct(
        private \PDO $pdo,
        private ?Inflector $inflector = null,
    ) {
        $this->inflector ??= InflectorFactory::create()->build();

        $resources = [];

        $query = $this->pdo->prepare('SHOW tables');
        $query->execute();
        foreach ($query->fetchAll(\PDO::FETCH_COLUMN) as $table) {
            $resources[$table] = [
                'path' => '/'.$this->inflector->pluralize($table),
                'table' => $table,
            ];
        }

        parent::__construct(['resources' => $resources]);
    }

    public function getResources(): iterable
    {
        return $this['resources'];
    }

    public function export(string $filename): void
    {
        $export = sprintf('<?php return %s;', var_export((array) $this, true));

        file_put_contents($filename, $export);
    }
}
