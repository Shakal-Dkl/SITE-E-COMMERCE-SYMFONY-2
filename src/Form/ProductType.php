<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

// Formulaire produit utilisé dans le back-office (création + édition).
class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('price', null, [
                'label' => 'Prix',
            ])
            // Ce champ ouvre l'explorateur de fichiers pour téléverser une image locale.
            ->add('uploadedImage', FileType::class, [
                'label' => 'Télécharger une image',
                'mapped' => false,
                'required' => false,
                'help' => 'Formats conseillés : jpg, jpeg, png, webp',
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        mimeTypes: [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        maxSizeMessage: 'Le fichier est trop volumineux (max 2 Mo).',
                        mimeTypesMessage: 'Format invalide. Utilise uniquement JPG, PNG ou WEBP.',
                    ),
                ],
            ])
            ->add('stockXs')
            ->add('stockS')
            ->add('stockM')
            ->add('stockL')
            ->add('stockXl')
            ->add('featured', CheckboxType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
