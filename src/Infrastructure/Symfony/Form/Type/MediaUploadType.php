<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Infrastructure\Symfony\Form\Type;

use AlexandreBulete\DddMediaBundle\Domain\ValueObject\FileFacts;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

/**
 * A file field accepting what the library keeps, within the size limit.
 * For any form that takes a picture (a cover, a visual): the caller then
 * dispatches UploadMedia with the uploaded file.
 */
final class MediaUploadType extends AbstractType
{
    public function __construct(
        #[Autowire(param: 'media.max_size')]
        private readonly string $maxSize,
    ) {}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'max_size' => $this->maxSize,
            'required' => false,
        ]);
        $resolver->setAllowedTypes('max_size', 'string');
        $resolver->setDefault('constraints', static function (Options $options): array {
            $maxSize = $options['max_size'];
            \assert(is_string($maxSize));

            return [new File(maxSize: $maxSize, mimeTypes: array_keys(FileFacts::ACCEPTED_TYPES), mimeTypesMessage: 'media.file.type')];
        });
        $resolver->setDefault('attr', ['accept' => implode(',', array_keys(FileFacts::ACCEPTED_TYPES))]);
    }

    public function getParent(): string
    {
        return FileType::class;
    }
}
