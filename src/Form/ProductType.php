<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function __construct(private readonly KernelInterface $kernel)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('price', null, [
                'label' => 'Prix',
            ])
            ->add('imagePath', ChoiceType::class, [
                'label' => 'Image produit',
                'required' => false,
                'placeholder' => 'Aucune image',
                'choices' => $this->buildImageChoices(),
                'help' => 'Liste automatique depuis public/images/products',
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

    /**
     * @return array<string, string>
     */
    private function buildImageChoices(): array
    {
        $imagesDirectory = $this->kernel->getProjectDir().'/public/images/products';
        $files = glob($imagesDirectory.'/*.{jpg,jpeg,png,webp}', GLOB_BRACE);

        if ($files === false || $files === []) {
            return [];
        }

        sort($files);
        $choices = [];

        foreach ($files as $file) {
            $fileName = basename($file);
            $choices[$fileName] = 'images/products/'.$fileName;
        }

        return $choices;
    }
}
