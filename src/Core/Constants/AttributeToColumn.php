<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Constants;

use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\AsFloat;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\AsString;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\BigIncrements;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\BigInteger;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Binary;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Boolean;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Char;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Computed;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Date;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\DateTime;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\DateTimeTz;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Decimal;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Double;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Enum;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Geometry;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\GeometryCollection;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Id;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Increments;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Integer;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\IntegerIncrements;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\IpAddress;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Json;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Jsonb;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\LineString;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\LongText;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\MacAddress;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\MediumIncrements;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\MediumInteger;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\MediumText;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Morphs;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\MultiLineString;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\MultiPoint;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\MultiPolygon;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\MultiPolygonZ;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\NullableMorphs;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\NullableNumericMorphs;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\NullableTimestamps;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\NullableUlidMorphs;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\NullableUuidMorphs;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\NumericMorphs;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Point;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Polygon;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Set;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\SmallIncrements;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\SmallInteger;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\SoftDeletes;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\SoftDeletesTz;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Text;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Time;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Timestamp;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Timestamps;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\TimestampsTz;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\TimestampTz;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\TimeTz;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\TinyIncrements;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\TinyInteger;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\TinyText;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Ulid;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\UlidMorphs;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\UnsignedBigInteger;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\UnsignedDecimal;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\UnsignedDouble;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\UnsignedFloat;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\UnsignedInteger;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\UnsignedMediumInteger;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\UnsignedSmallInteger;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\UnsignedTinyInteger;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Uuid;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\UuidMorphs;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Year;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\ForeignKeys\Foreign;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\ForeignKeys\ForeignId;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\ForeignKeys\ForeignIdFor;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\ForeignKeys\ForeignUlid;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\ForeignKeys\ForeignUuid;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Indexes\FullText;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Indexes\Index;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Indexes\Primary;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Indexes\RawIndex;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Indexes\SpatialIndex;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Indexes\Unique;

abstract class AttributeToColumn
{
    private static array $columns = [
        Foreign::class => 'foreign',
        Id::class => 'id',
        Increments::class => 'increments',
        IntegerIncrements::class => 'integerIncrements',
        TinyIncrements::class => 'tinyIncrements',
        SmallIncrements::class => 'smallIncrements',
        MediumIncrements::class => 'mediumIncrements',
        BigIncrements::class => 'bigIncrements',
        Char::class => 'char',
        AsString::class => 'string',
        TinyText::class => 'tinyText',
        Text::class => 'text',
        MediumText::class => 'mediumText',
        LongText::class => 'longText',
        Integer::class => 'integer',
        TinyInteger::class => 'tinyInteger',
        SmallInteger::class => 'smallInteger',
        MediumInteger::class => 'mediumInteger',
        BigInteger::class => 'bigInteger',
        UnsignedInteger::class => 'unsignedInteger',
        UnsignedTinyInteger::class => 'unsignedTinyInteger',
        UnsignedSmallInteger::class => 'unsignedSmallInteger',
        UnsignedMediumInteger::class => 'unsignedMediumInteger',
        UnsignedBigInteger::class => 'unsignedBigInteger',
        ForeignId::class => 'foreignId',
        ForeignIdFor::class => 'foreignIdFor',
        AsFloat::class => 'float',
        Double::class => 'double',
        Decimal::class => 'decimal',
        UnsignedFloat::class => 'unsignedFloat',
        UnsignedDouble::class => 'unsignedDouble',
        UnsignedDecimal::class => 'unsignedDecimal',
        Boolean::class => 'boolean',
        Enum::class => 'enum',
        Set::class => 'set',
        Json::class => 'json',
        Jsonb::class => 'jsonb',
        Date::class => 'date',
        DateTime::class => 'dateTime',
        DateTimeTz::class => 'dateTimeTz',
        Time::class => 'time',
        TimeTz::class => 'timeTz',
        Timestamp::class => 'timestamp',
        TimestampTz::class => 'timestampTz',
        Timestamps::class => 'timestamps',
        NullableTimestamps::class => 'nullableTimestamps',
        TimestampsTz::class => 'timestampsTz',
        SoftDeletes::class => 'softDeletes',
        SoftDeletesTz::class => 'softDeletesTz',
        Year::class => 'year',
        Binary::class => 'binary',
        Uuid::class => 'uuid',
        ForeignUuid::class => 'foreignUuid',
        Ulid::class => 'ulid',
        ForeignUlid::class => 'foreignUlid',
        IpAddress::class => 'ipAddress',
        MacAddress::class => 'macAddress',
        Geometry::class => 'geometry',
        Point::class => 'point',
        LineString::class => 'lineString',
        Polygon::class => 'polygon',
        GeometryCollection::class => 'geometryCollection',
        MultiPoint::class => 'multiPoint',
        MultiLineString::class => 'multiLineString',
        MultiPolygon::class => 'multiPolygon',
        MultiPolygonZ::class => 'multiPolygonZ',
        Computed::class => 'computed',
        Morphs::class => 'morphs',
        NullableMorphs::class => 'nullableMorphs',
        NumericMorphs::class => 'numericMorphs',
        NullableNumericMorphs::class => 'nullableNumericMorphs',
        UuidMorphs::class => 'uuidMorphs',
        NullableUuidMorphs::class => 'nullableUuidMorphs',
        UlidMorphs::class => 'ulidMorphs',
        NullableUlidMorphs::class => 'nullableUlidMorphs',
        Unique::class => 'unique',
        Index::class => 'index',
        FullText::class => 'fullText',
        Primary::class => 'primary',
        RawIndex::class => 'rawIndex',
        SpatialIndex::class => 'spatialIndex',

    ];


    public static function map(string $type)
    {
        return self::$columns[$type] ?? $type;
    }

}
