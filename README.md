# ddd-media-bundle

A media library as a reusable DDD Bounded Context for Symfony: files kept on a
Flysystem storage, described for those who cannot see them, resized on demand
by LiipImagine, browsed and deleted from a Sylius back office.

Other contexts hold a media's **id** and nothing else — no foreign key, no
file path. They render it with two Twig functions, and tell the library what
they use so that it never deletes a picture still on a page.

## Installation

```bash
composer require alexandrebulete/ddd-media-bundle
```

The Flex recipes register this bundle, LiipImagineBundle and FlysystemBundle,
and import LiipImagine's routes. The `media` table comes with the bundle's own
migration: `bin/console doctrine:migrations:migrate`, nothing to diff.

Needs the GD (or Imagick) extension for resizing, and fileinfo.

## Model

| Aggregate | Holds |
|---|---|
| `Media` | storage path, original name, MIME type, size, width and height, alternative text |

- **Images only for now**: JPEG, PNG, WebP, GIF, AVIF. The type is read from
  the file's bytes, never from its name or what the browser claims.
- **A file never changes.** A new version is a new media, so that a resized
  copy cached anywhere never shows the old picture under the new name. Only
  the alternative text is edited.
- Stored as `YYYY/MM/<id>.<ext>`: one folder per month, unique by id.

## Use cases

Commands: `UploadMedia` (the caller picks the id, to point at it in the same
breath), `DescribeMedia`, `RemoveMedia`.

Queries: `FindMediaLibrary`, `FindMedia` (back office) and `FindMediaByIds`
(`#[PublicAccess]`: a page asks for all its pictures at once).

Permissions follow the skeleton's convention: `media.upload_media`,
`media.find_media_library`…

## Showing a picture

Declare the sizes the application needs as LiipImagine filter sets; the
bundle's storage is the default data loader, so they need nothing else:

```yaml
liip_imagine:
    filter_sets:
        cover_card:
            quality: 82
            filters: { thumbnail: { size: [540, 810], mode: outbound } }
```

```twig
{% set cover = media(book.coverId) %}
{% if cover %}
    <img src="{{ media_url(cover, 'cover_card') }}" alt="{{ cover.alt }}">
{% endif %}
```

`media()` and `media_url()` accept a `Media`, a `MediaId`, or any id that
casts to string. Each unknown id costs a query: a listing first preloads all
of its ids, and the page then costs one query whatever its size.

```php
$mediaUrls->preload(array_map(fn (Book $b) => $b->coverId, $books));
```

The first request of a size computes the copy into `public/media/cache/`;
later ones get the static file. That folder is disposable.

## Deleting what is in use

Implement `MediaUsageInterface` wherever media are shown (autoconfigured,
tag `media.usage`):

```php
final readonly class BookCoverUsage implements MediaUsageInterface
{
    public function usagesOf(MediaId $id): array
    {
        // ["the cover of …", …], or [] when nothing uses it
    }
}
```

`RemoveMedia` asks every implementation and throws `MediaInUseException` —
shown as a flash message in the back office — while one answers. The resized
copies of a removed media are deleted with it.

## Upload field

`MediaUploadType` is a file field accepting what the library keeps, within the
configured size: add it to any form that takes a picture (a cover, a visual),
then dispatch `UploadMedia` with the uploaded file.

## Configuration

```yaml
media:
    max_size: 10M              # largest upload, File constraint format
    storage: ~                 # a Flysystem storage service id; null = media.storage
    admin:
        enabled: true          # Sylius screens; false for a headless setup
        grid_limits: [24, 48, 96]
```

With `storage: ~`, the bundle declares `media.storage`, a local disk under
`var/storage/media`. For an object storage, declare a Flysystem storage in the
application and name it here.

## Seams

Ports bound in `config/services.php`, to rebind in the application:

| Port | Default | Rebind for |
|---|---|---|
| `FileStorageInterface` | `FlysystemFileStorage` | another storage API |
| `FileInspectorInterface` | `NativeFileInspector` (fileinfo, getimagesize) | more file types |
| `DomainEventPublisherInterface` | `ImmediateEventPublisher` (in-process, in the transaction) | an outbox |
| `MediaRepositoryInterface` | Doctrine | another storage |

## Development

```bash
composer qa    # phpstan (max, strict) + deptrac + phpunit
```

Integration tests run on SQLite in memory, or on the database given by
`DDD_TEST_DATABASE_URL` (CI: PostgreSQL, MySQL, SQLite), with an in-memory
Flysystem.

## License

MIT
