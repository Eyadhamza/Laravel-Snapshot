<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\AsFloat;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\AsString;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\BigIncrements;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\BigInteger;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Binary;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Boolean;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Char;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Computed;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Date;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\DateTime;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\DateTimeTz;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Decimal;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Double;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Enum;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Foreign;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\ForeignId;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\ForeignIdFor;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\ForeignUlid;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\ForeignUuid;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Geometry;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\GeometryCollection;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Id;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Increments;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Integer;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\IntegerIncrements;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\IpAddress;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Json;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Jsonb;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\LineString;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\LongText;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\MacAddress;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\MediumIncrements;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\MediumInteger;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\MediumText;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Morphs;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\MultiLineString;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\MultiPoint;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\MultiPolygon;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\MultiPolygonZ;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\NullableMorphs;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\NullableNumericMorphs;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\NullableTimestamps;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\NullableUlidMorphs;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\NullableUuidMorphs;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\NumericMorphs;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Point;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Polygon;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Set;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\SmallIncrements;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\SmallInteger;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\SoftDeletes;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\SoftDeletesTz;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Text;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Time;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Timestamp;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Timestamps;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\TimestampsTz;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\TimestampTz;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\TimeTz;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\TinyIncrements;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\TinyInteger;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\TinyText;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Ulid;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\UlidMorphs;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\UnsignedBigInteger;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\UnsignedDecimal;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\UnsignedDouble;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\UnsignedFloat;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\UnsignedInteger;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\UnsignedMediumInteger;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\UnsignedSmallInteger;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\UnsignedTinyInteger;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Uuid;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\UuidMorphs;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Year;
use Illuminate\Support\Collection;

class MapToBlueprintColumn
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
    ];


    public static function map(string $type)
    {
        return self::$columns[$type] ?? $type;
    }

}
