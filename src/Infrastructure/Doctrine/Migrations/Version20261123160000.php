<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Infrastructure\Doctrine\Migrations;

use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Creates the media library.
 *
 * DBAL's built-in types only, never the bundle's own: a migration is a
 * snapshot, and must not change when a mapped type changes tomorrow. The
 * integration tests check that the result is the mapped schema on
 * PostgreSQL, MySQL and SQLite.
 */
final class Version20261123160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Media: the library of stored files.';
    }

    public function up(Schema $schema): void
    {
        $media = $schema->createTable('media');
        $media->addColumn('id', Types::GUID);
        $media->addColumn('path', Types::STRING, ['length' => 255]);
        $media->addColumn('original_name', Types::STRING, ['length' => 255]);
        $media->addColumn('mime_type', Types::STRING, ['length' => 100]);
        $media->addColumn('size', Types::INTEGER);
        $media->addColumn('width', Types::INTEGER, ['notnull' => false]);
        $media->addColumn('height', Types::INTEGER, ['notnull' => false]);
        $media->addColumn('alt', Types::STRING, ['length' => 255, 'notnull' => false]);
        $media->addColumn('created_at', Types::DATETIME_IMMUTABLE);
        $media->addColumn('updated_at', Types::DATETIME_IMMUTABLE, ['notnull' => false]);
        $media->addPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('id')->create());
        $media->addUniqueIndex(['path']);
        $media->addIndex(['created_at']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('media');
    }
}
