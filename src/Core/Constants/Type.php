<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Constants;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Property;

abstract class Type
{
    const FOREIGN = 'foreign';
    const ID = 'id';
    const INCREMENTS = 'increments';
    const INTEGER_INCREMENTS = 'integerIncrements';
    const TINY_INCREMENTS = 'tinyIncrements';
    const SMALL_INCREMENTS = 'smallIncrements';
    const MEDIUM_INCREMENTS = 'mediumIncrements';
    const BIG_INCREMENTS = 'bigIncrements';
    const CHAR = 'char';
    const STRING = 'string';
    const TINY_TEXT = 'tinyText';
    const TEXT = 'text';
    const MEDIUM_TEXT = 'mediumText';
    const LONG_TEXT = 'longText';
    const INTEGER = 'integer';
    const TINY_INTEGER = 'tinyInteger';
    const SMALL_INTEGER = 'smallInteger';
    const MEDIUM_INTEGER = 'mediumInteger';
    const BIGINTEGER = 'bigInteger';
    const UNSIGNED_INTEGER = 'unsignedInteger';
    const UNSIGNED_TINY_INTEGER = 'unsignedTinyInteger';
    const UNSIGNED_SMALL_INTEGER = 'unsignedSmallInteger';
    const UNSIGNED_MEDIUM_INTEGER = 'unsignedMediumInteger';
    const UNSIGNED_BIGINTEGER = 'unsignedBigInteger';
    const FOREIGN_ID = 'foreignId';
    const FOREIGN_ID_FOR = 'foreignIdFor';
    const FLOAT = 'float';
    const DOUBLE = 'double';
    const DECIMAL = 'decimal';
    const UNSIGNED_FLOAT = 'unsignedFloat';
    const UNSIGNED_DOUBLE = 'unsignedDouble';
    const UNSIGNED_DECIMAL = 'unsignedDecimal';
    const BOOLEAN = 'boolean';
    const ENUM = 'enum';
    const SET = 'set';
    const JSON = 'json';
    const JSONB = 'jsonb';
    const DATE = 'date';
    const DATETIME = 'dateTime';
    const DATE_TIME_TZ = 'dateTimeTz';
    const TIME = 'time';
    const TIME_TZ = 'timeTz';
    const TIMESTAMP = 'timestamp';
    const TIMESTAMP_TZ = 'timestampTz';
    const TIMESTAMPS = 'timestamps';
    const NULLABLE_TIMESTAMPS = 'nullableTimestamps';
    const TIMESTAMPS_TZ = 'timestampsTz';
    const SOFT_DELETES = 'softDeletes';
    const SOFT_DELETES_TZ = 'softDeletesTz';
    const YEAR = 'year';
    const BINARY = 'binary';
    const UUID = 'uuid';
    const FOREIGN_UUID = 'foreignUuid';
    const ULID = 'ulid';
    const FOREIGN_ULID = 'foreignUlid';
    const IPADDRESS = 'ipAddress';
    const MAC_ADDRESS = 'macAddress';
    const GEOMETRY = 'geometry';
    const POINT = 'point';
    const LINESTRING = 'lineString';
    const POLYGON = 'polygon';
    const GEOMETRYCOLLECTION = 'geometryCollection';
    const MULTIPOINT = 'multiPoint';
    const MULTILINESTRING = 'multiLineString';
    const MULTIPOLYGON = 'multiPolygon';
    const MULTI_POLYGONZ = 'multiPolygonZ';
    const COMPUTED = 'computed';
    const MORPHS = 'morphs';
    const NULLABLE_MORPHS = 'nullableMorphs';
    const NUMERIC_MORPHS = 'numericMorphs';
    const NULLABLE_NUMERIC_MORPHS = 'nullableNumericMorphs';
    const UUID_MORPHS = 'uuidMorphs';
    const NULLABLE_UUID_MORPHS = 'nullableUuidMorphs';
    const ULID_MORPHS = 'ulidMorphs';
    const NULLABLE_ULID_MORPHS = 'nullableUlidMorphs';
    private string $propertyType;
    private string $propertyName;

    public function __construct(
        private array|string $rules,
    ){}

    public function setPropertyType(string $propertyType): Property
    {
        $this->propertyType = $propertyType;
        return $this;
    }

    public function setPropertyName(string $propertyName): Property
    {
        $this->propertyName = $propertyName;
        return $this;
    }

    public function getRules(): array|string
    {
        return is_array($this->rules) ? $this->rules : [$this->rules];
    }

    public function getPropertyType(): string
    {
        return $this->propertyType;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }
}
