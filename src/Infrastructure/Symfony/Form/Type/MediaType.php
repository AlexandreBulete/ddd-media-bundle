<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Infrastructure\Symfony\Form\Type;

use AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\Resource\MediaResource;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The file on creation, the alternative text always: a media's file is never
 * replaced, a new version is a new media.
 */
final class MediaType extends AbstractType
{
    public function __construct(
        #[Autowire(param: 'media.max_size')]
        private readonly string $maxSize,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $resource = $event->getData();
            $form = $event->getForm();
            if (!$resource instanceof MediaResource || $resource->id === null) {
                $form->add('file', MediaUploadType::class, [
                    'label' => 'media.file',
                    'max_size' => $this->maxSize,
                ]);
            }
            $form->add('alt', TextType::class, [
                'label' => 'media.alt',
                'help' => $resource instanceof MediaResource && $resource->thumbnailUrl !== null
                    ? sprintf('<img src="%s" alt="" style="display:block;max-height:160px;margin:8px 0">', htmlspecialchars($resource->thumbnailUrl, \ENT_QUOTES))
                    : 'media.alt_help',
                'help_html' => $resource instanceof MediaResource && $resource->thumbnailUrl !== null,
                'required' => false,
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => MediaResource::class]);
    }
}
